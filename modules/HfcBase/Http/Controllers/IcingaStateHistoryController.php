<?php

namespace Modules\HfcBase\Http\Controllers;

use Carbon\Carbon;
use Modules\HfcReq\Entities\NetElement;

class IcingaStateHistoryController
{
    public function table($id)
    {
        $now = now()->startOfDay();
        $netelement = NetElement::where('id', $id)
            ->select(['id', 'name', 'id_name'])
            ->without('netelementtype')
            ->with([
                //'icingaObject:object_id,name1,is_active',
                //'icingaObject.recentStateHistory:statehistory_id,object_id,state_time,state_time_usec,state,state_type,output,long_output,last_hard_state,current_check_attempt,max_check_attempts',
                'icingaServices:servicestatus_id,name2,service_object_id,last_hard_state,last_hard_state_change,check_command',
                'icingaServices.recentStateHistory:statehistory_id,object_id,state_time,state_time_usec,state,state_type,output,long_output,last_hard_state,current_check_attempt,max_check_attempts',
            ])
            ->first();
        /*
        $netelement->icingaObject->recentStateHistory
            ->map(function ($state) {
                $state->service = 'host';
                return $state;
            });
        */

        $history = collect();

        foreach ($netelement->icingaServices as $service) {
            if (! \Str::startsWith($service->name2, 'clusters')) {
                continue;
            }

            $lastState = 0;
            $finalState = [];
            $moni = now()->subMonth(2)->startOfDay();

            $daysWithFailures = $service->recentStateHistory->groupBy(function ($state) {
                return Carbon::parse($state->state_time)->format('m-d');
            });

            $history = $history->concat($service->recentStateHistory
                ->map(function ($state) use ($service, &$finalState, &$lastState, $moni, $daysWithFailures) {
                    while ($state->state_time->diffInDays($moni, false) <= 0) {
                        $highestState = $lastState;
                        if (isset($daysWithFailures[$moni->format('m-d')])) {
                            foreach ($daysWithFailures[$moni->format('m-d')]->pluck('state')->toArray() as $dayState) {
                                if ($dayState > $highestState) {
                                    $highestState = $dayState;
                                }
                            }

                            $lastState = $daysWithFailures[$moni->format('m-d')]->last()->state;
                        }

                        $finalState[$moni->format('m-d')] = $highestState; // only show worst state of the Day
                        $moni->addDay();
                    }

                    $state->service = $service->name2;
                    $state->state_time = $state->state_time->toDateTimeString();

                    return $state;
                })
            );

            while ($now->gt($moni)) {
                $finalState[$moni->addDay()->format('m-d')] = $lastState;
            }

            $service->stateArray = $finalState;
        }

        return collect([
            'table' => $history,
            'slider_power' => $netelement->icingaServices->where('name2', 'clusters_power')->first()->stateArray,
            'slider_online' => $netelement->icingaServices->where('name2', 'clusters_online')->first()->stateArray,
        ]);
    }
}
