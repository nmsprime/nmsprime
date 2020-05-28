<?php

namespace Modules\HfcBase\Http\Controllers;

use Modules\HfcReq\Entities\NetElement;
use Modules\HfcBase\Entities\IcingaObject;
use Modules\HfcBase\Entities\IcingaHostStatus;
use Modules\HfcBase\Entities\IcingaServiceStatus;

class TroubleDashboardController
{
    public static function impairedData()
    {
        if (! IcingaObject::db_exists()) {
            return collect(['netelements' => [], 'services' => []]);
        }

        $hosts = IcingaHostStatus::forTroubleDashboard()->get();
        $services = IcingaServiceStatus::forTroubleDashboard()->get();
        $netelements = NetElement::withActiveModems()->get()->keyBy('id');

        $impairedData = $hosts->concat($services)
            ->sortByDesc(function ($element) use ($netelements) {
                return [
                    $element->last_hard_state,
                    $element->affectedModemsCount($netelements),
                ];
            })
            ->map(function ($impaired) use ($netelements) {
                if (isset($impaired->additionalData) && ! is_array($impaired->additionalData)) {
                    $impaired->additionalData = $impaired->additionalData
                        ->sortByDesc(function ($element) use ($netelements) {
                            return [
                                // $element['state'],
                                ((isset($netelements[$element['id']])) ? $netelements[$element['id']]->modems_count : 0),
                            ];
                        });
                }

                return $impaired;
            });

        return collect(compact('hosts', 'impairedData', 'netelements', 'services'));
    }

    public function muteProblem($type, $id, $mute)
    {
        if ($type === 'host') {
            $model = IcingaHostStatus::findorFail($id);
        } else {
            $model = IcingaServiceStatus::findorFail($id);
        }

        $model->problem_has_been_acknowledged = $mute;
        $model->save();

        return redirect()->back();
    }
}
