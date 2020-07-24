<?php

namespace Modules\HfcBase\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\ProvBase\Entities\Modem;
use Modules\HfcBase\Entities\HfcBase;
use Modules\HfcReq\Entities\NetElement;
use Modules\HfcBase\Entities\IcingaObject;
use Modules\HfcBase\Entities\IcingaHostStatus;
use Modules\HfcBase\Entities\IcingaServiceStatus;

class TroubleDashboardController
{
    /**
     * Collect all necessary information for the trouble dashboard.
     *
     * @return \Illuminate\Support\Collection
     */
    public function summary()
    {
        return \Cache::remember('TD:summary', 1, function () {
            if (! IcingaObject::db_exists()) {
                return collect(['hostCounts' => [], 'serviceCounts' => []]);
            }

            $hostCounts = IcingaHostStatus::countsForTroubleDashboard()->first();
            $serviceCounts = IcingaServiceStatus::countsForTroubleDashboard()->first();

            return collect(compact('hostCounts', 'serviceCounts'));
        });
    }

    public function data()
    {
        $node = $this->getProvisioningSystemData();
        $netelements = NetElement::withActiveModems('id', '>', '1')
            ->with(['icingaObject.hostStatus', 'icingaServices.icingaObject', 'geoPosModems'])
            ->without('netelementtype')
            ->get(['cluster', 'descr', 'id', 'name', 'parent_id', 'pos'])
            ->keyBy('id');

        $affectedModemsCount = self::calculateAllModemCounts($netelements);
        $netelements[$node->name] = $node;

        $impairedData = $netelements->mapWithKeys(function ($netelement) use ($affectedModemsCount) {
            if (! $netelement->icingaObject) {
                return [];
            }

            if (isset($affectedModemsCount[$netelement->id])) {
                $netelement->singleFail = false;
                $netelement->allModems = $affectedModemsCount[$netelement->id]['all'];
                $netelement->offlineModems = $affectedModemsCount[$netelement->id]['all'] - $affectedModemsCount[$netelement->id]['online'];
                $netelement->criticalModems = $affectedModemsCount[$netelement->id]['critical'];
                $netelement->offlineModems =
                    ($netelement->allModems > $netelement->modems_count) &&
                    ($netelement->offlineModems >= ((config('hfccustomer.threshhold.avg.percentage.multipleClusters') / 100) * $netelement->allModems)) ?
                    $netelement->offlineModems :
                    (($netelement->allModems <= $netelement->modems_count &&
                    ($netelement->modems_count - $netelement->modems_online_count) > (1 - (config('hfccustomer.threshhold.avg.percentage.warning') / 100)) * $netelement->modems_count) ?
                    $netelement->modems_count - $netelement->modems_online_count :
                    0);
            }

            switch (true) {
                case ($netelement->offlineModems >= 100):
                    $netelement->severity = 3;
                    break;
                case ($netelement->offlineModems >= 25):
                    $netelement->severity = 2;
                    break;
                case ($netelement->offlineModems >= 5):
                    $netelement->severity = 1;
                    break;
                default:
                    $netelement->severity = 0;
                    break;
            }


            $netelement->icingaServices = $netelement->icingaServices
                ->map(function ($service) use ($netelement) {
                    if ($service->problem_has_been_acknowledged) {
                        $netelement->hasMutedServices = true;
                    }

                    if ($service->last_hard_state > 0) {
                        $netelement->partiallyImpaired = true;
                    }

                    $service->ticketLink = $service->toSubTicket($netelement);
                    $service->icingaLink = $service->toIcingaWeb();
                    $service->acknowledgeLink = route('TroubleDashboard.mute', ['Service', $service->service_object_id, $service->problem_has_been_acknowledged ? 0 : 1]);

                    return $service;
                })
                ->sortByDesc(function ($service) {
                    return $service->last_hard_state;
                });

            $netelement->last_hard_state_change = $netelement->icingaServices
                ->pluck('last_hard_state_change')
                ->push($netelement->icingaHostStatus->last_hard_state_change)
                ->max();
            $netelement->last_hard_state_change = Carbon::parse($netelement->last_hard_state_change)->diffForHumans();

            $netelement->controllingLink = !$netelement->isProvisioningSystem ? route('NetElement.controlling_edit', [ $netelement->id , 0, 0]) : '';
            $netelement->mapLink = $netelement->toMap();
            $netelement->ticketLink = $netelement->toTicket();
            $netelement->acknowledgeLink = route('TroubleDashboard.mute', [($netelement->isProvisioningSystem ? 'Node' : 'Host'), $netelement->icingaHostStatus->host_object_id, $netelement->icingaHostStatus->problem_has_been_acknowledged ? 0 : 1]);

            return [$netelement->id => $netelement];
        })->sortByDesc(function ($netelement) {
            return [
                $netelement->offlineModems,
                $netelement->severity,
                $netelement->isProvisioningSystem,
                $netelement->allModems,
            ];
        });

        return $impairedData->values()->toJson();
    }

    /**
     * Get the cummulated number of registered, online and critical Modems for
     * each NetElement
     *
     * @param \Illuminate\Database\Eloquent\Collection $netelements
     * @return array
     */
    public static function calculateAllModemCounts(\Illuminate\Database\Eloquent\Collection $netelements)
    {
        $netelementIds = Modem::select('netelement_id')->distinct()->pluck('netelement_id')->filter();
        $lookup = [
            'modems_count' => 'all',
            'modems_online_count' => 'online',
            'modems_critical_count' =>  'critical',
        ];
        $modemsPerNetelement = [];

        foreach ($netelementIds as $id) {
            $currentId = $id;
            $branchModemCount = [];
            $branchModemCount['all'] = 0;
            $branchModemCount['online'] = 0;
            $branchModemCount['critical'] = 0;
            $parentId = $netelements[$currentId]->parent_id;

            while ($parentId > 1) {
                foreach ($lookup as $property => $type) {
                    $branchModemCount[$type] = $branchModemCount[$type] +
                        (! isset($modemsPerNetelement[$currentId][$type]) ? $netelements[$currentId]->$property : 0);

                    $modemsPerNetelement[$currentId][$type] = ($modemsPerNetelement[$currentId][$type] ?? 0) + $branchModemCount[$type];
                }

                $currentId = $parentId;
                $parentId = $netelements[$currentId]->parent_id;
            }
        }

        return $modemsPerNetelement;
    }

    /**
     * Call to Icinga2 API and acknowledge or remove acknowledgement for a Problem.
     *
     * @param string $type
     * @param int $id
     * @param bool $mute
     * @return \Illuminate\Http\Response
     */
    public function muteProblem($type, $id, $mute)
    {
        $filter = $this->composeFilter($type, $id = IcingaObject::find($id));
        $icingaApi = new \Modules\HfcBase\Helpers\IcingaApi($type, $filter);
        $results = $mute ? $icingaApi->acknowledgeProblem() : $icingaApi->removeAcknowledgement();

        if (! $results) {
            \Log::alert('An API request was sent to ICINGA2, but it is probably not runnig.');

            return response(json_encode(['error' => 'ICINGA2 is not running.']), 400);
        }

        $results['id'] = $id->object_id;

        return response(json_encode($results), $results['error'] ?? 200);
    }

    /**
     * Create the filter for Icinga2 API
     *
     * @param string $type
     * @param IcingaObject $icingaObject
     * @return string|\Illuminate\Http\Response
     */
    protected function composeFilter($type, $icingaObject)
    {
        if ($type === 'Service') {
            return "host.name == \"{$icingaObject->name1}\" && service.name == \"{$icingaObject->name2}\"";
        }

        if ($type === 'Host') {
            return "host.name == \"{$icingaObject->netelement->id_name}\"";
        }

        return response(['results' => [
            'error' => 400,
            'status' => 'Bad Request. Your Type Parameter is not matching our database.',
        ],
        ], 400);
    }

    protected function getProvisioningSystemData() {
        $nodeObject = IcingaObject::where('is_active', 1)->where('name2', 'icinga')->first();
        $nodeObject = IcingaObject::where('objecttype_id', 1)->where('name1', $nodeObject->name1)->first();

        $node = new NetElement();
        $node->id = 0;
        $node->name = $nodeObject->name1;
        $node->isProvisioningSystem = true;
        $node->setRelation('icingaObject', $nodeObject);
        $node->setRelation('hostStatus', $nodeObject->hostStatus);
        $node->setRelation('icingaServices', $nodeObject->services()->with('icingaObject')->get());

        return $node;
    }
}
