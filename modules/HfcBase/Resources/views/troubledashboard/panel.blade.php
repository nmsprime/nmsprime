<div id="troubleDash">
    <vue-snotify></vue-snotify>
    <div id="troubleDashMute" class="d-flex justify-content-end m-b-15" style="display:none;">
        <div v-if="!showMuted" v-on:click="showMuted = !showMuted" class="m-r-10" title="show Muted"><i class="fa fa-2x fa-eye-slash"></i></div>
        <div v-if="showMuted" v-on:click="showMuted = !showMuted" class="m-r-10" title="hide Muted"><i class="fa fa-2x fa-eye"></i></div>
    </div>

    <div class="height-lg" style="overflow-y:scroll;overflow-x:hidden;">
        <table class="table m-b-0" style="width:100%;">
            <thead>
                <tr>
                    <th class="d-table-cell position-sticky fixed-top"></th>
                    <th class="d-none d-lg-table-cell position-sticky fixed-top" width="100px">Severity</th>
                    <th class="d-table-cell position-sticky fixed-top">Type</th>
                    <th class="d-table-cell position-sticky fixed-top">Size</th>
                    <th class="d-table-cell position-sticky fixed-top">Host</th>
                    <th class="d-none d-wide-table-cell position-sticky fixed-top">Detected</th>
                    <th class="d-table-cell position-sticky fixed-top">Status</th>
                    <th class="d-none d-sm-table-cell position-sticky fixed-top text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="troubleDashTable" class="" style="overflow:scroll;display:none;">
            @foreach ($impairedData['impairedData'] as $id => $netelement)
                <tr v-show="((!!acknowledged[{{ $netelement->icingaHostStatus->host_object_id }}] || '{{ $netelement->hasMutedServices }}') && showMuted) || (!!!acknowledged[{{ $netelement->icingaHostStatus->host_object_id }}] && !showMuted && '{{ $netelement->last_hard_state > 0 }}')">
                    <td style="cursor: pointer" data-toggle="collapse" data-target=".{{ $netelement->icingaHostStatus->host_object_id }}-collapsedservice"><i class="fa fa-plus"></i></td>
                    <td class='{{ $colors[$netelement->last_hard_state] }} f-s-13 d-none d-lg-table-cell' style="cursor: pointer;" data-toggle="collapse" data-target=".{{ $netelement->icingaObject->object_id  }}-collapsedservice">
                        {{ $netelement->last_hard_state > 1 ? 'CRITICAL' : 'WARNING' }}
                    </td>
                    <td style="cursor: pointer;" data-toggle="collapse" data-target=".{{ $netelement->icingaObject->object_id  }}-collapsedservice">{{ $netelement->criticalModems > $netelement->offlineModems ? 'Proactive' : 'Outage' }}</td>
                    <td style="cursor: pointer;" data-toggle="collapse" data-target=".{{ $netelement->icingaObject->object_id  }}-collapsedservice">
                        @if (! $netelement->isProvisioningSystem && ($netelement->offlineModems + $netelement->criticalModems) > 0)
                            {{ $netelement->offlineModems + $netelement->criticalModems }}/{{ $netelement->allModems }}
                            @endif
                    </td>
                    <td class='f-s-13 breakAll' data-toggle="collapse" data-target=".{{ $netelement->icingaObject->object_id  }}-collapsedservice">
                        <a href="{{ route('NetElement.controlling_edit', [$netelement->id, 0, 0]) }}" target="_blank" class="">
                            <i class="fa fa-wrench"></i>
                            {{ $netelement->name }}
                        </a>
                    </td>
                    <td class='f-s-13 d-none d-wide-table-cell' style="cursor: pointer;" data-toggle="collapse" data-target=".{{ $netelement->icingaObject->object_id  }}-collapsedservice">{{ $netelement->icingaHostStatus->last_hard_state_change->diffForHumans() }}</td>
                    <td class='f-s-13 breakAll' data-toggle="collapse" data-target=".{{ $netelement->icingaObject->object_id  }}-collapsedservice" v-text="!!acknowledged[{{ $netelement->icingaHostStatus->host_object_id }}] ? 'Muted' : 'Open'"></td>
                    <td class="d-none d-sm-table-cell" max-width="180px" >
                        <div class="d-flex align-items-center justify-content-end">
                            <a href="{{ $netelement->toMap() }}" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-map"></i></a>
                            <a href="{{ $netelement->toTicket() }}" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-ticket"></i></a>
                            <form object-id="{{ $netelement->icingaHostStatus->host_object_id }}" method="POST" v-on:submit.prevent="mute"
                                action="{{ route('TroubleDashboard.mute', ['Host', $netelement->icingaHostStatus->host_object_id, $netelement->icingaHostStatus->problem_has_been_acknowledged ? 0 : 1]) }}">
                                <button type="submit" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg" :class="loading[{{ $netelement->icingaObject->object_id }}] ? 'fa-circle-o-notch fa-spin' : (acknowledged[{{ $netelement->icingaObject->object_id }}] ? 'fa-bell-o' : 'fa-bell-slash-o')"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @foreach ($netelement->icingaServices as $service)
                @if ($service->last_hard_state > 0)
                    <tr  v-show="(((!!acknowledged[{{ $service->service_object_id }}])|| '{{ $netelement->icingaHostStatus->problem_has_been_acknowledged }}') && showMuted) || (!!!acknowledged[{{ $service->service_object_id }}] && !showMuted)" class="collapse {{ $netelement->icingaHostStatus->host_object_id }}-collapsedservice">
                        <td class="d-none d-sm-table-cell" colspan="2"><i class="fa fa-circle text-{{ $service->additionalData->first()['cls'] ?? $colors[$service->last_hard_state] }} p-r-10"></i>{{ $service->icingaObject->name2 }}</td>
                        <td class="d-lg-none" colspan="2" class="p-20">{{ $service->additionalData->first()['text'] ?? $service->check_command }}</td>
                        <td class="d-none d-lg-table-cell d-wide-none p-10" colspan="3">
                            @if($service->additionalData->first() && $service->additionalData->first()['per'] !== null)
                            <div class=" d-flex-md align-items-center progress progress-striped m-b-0" style="position:relative;">
                                <div class="progress-bar progress-bar-{{ $service->additionalData->first()['cls'] ?? $colors[$service->last_hard_state] }}" style="width: {{ $service->additionalData->first()['per'] }}%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                <div class='text-inverse' style="position:absolute;width:auto;left:40px;">{{$service->additionalData->first()['text']}}</div>
                                            </div>
                                        </div>
                                @elseif($service->additionalData->first())
                                    {{ $service->additionalData->first()['text'] }}: {{ $service->additionalData->first()['val'] }}
                                @else
                                    {{ $service->output }}
                                @endif
                            </td>
                            <td class="d-none d-wide-table-cell p-10" colspan="4">
                                @if($service->additionalData->first() && $service->additionalData->first()['per'] !== null)
                                        <div class=" d-flex-md align-items-center progress progress-striped m-b-0" style="position:relative;">
                                            <div class="progress-bar progress-bar-{{ $service->additionalData->first()['cls'] ?? $colors[$service->last_hard_state] }}" style="width: {{ $service->additionalData->first()['per'] }}%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                <div class='text-inverse' style="position:absolute;width:auto;left:40px;">{{$service->additionalData->first()['text']}}</div>
                                            </div>
                                        </div>
                                @elseif($service->additionalData->first())
                                    {{ $service->additionalData->first()['text'] }}: {{ $service->additionalData->first()['val'] }}
                                @else
                                    {{ $service->output }}
                                @endif
                            </td>
                            <td class="d-none d-sm-table-cell">
                                @if (Str::StartsWith($service->icingaObject->name2, 'clusters_online'))
                                    {{ $netelement->modems_count - $netelement->modems_online_count}}
                                @endif
                                @if (Str::StartsWith($service->icingaObject->name2, 'clusters_power'))
                                    {{ $netelement->modems_critical_count}}
                                @endif
                            </td>
                            <td class="d-none d-sm-table-cell" align="right">
                                <div class="d-flex align-items-center justify-content-end">
                                    <!-- <a href="#" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-hdd-o"></i></a> -->
                                    <a href="{{ $service->toSubTicket($netelement) }}" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-ticket"></i></a>
                                    <a href="{{ $service->toIcingaWeb() }}" target="_blank" class="btn btn-light p-5 m-l-10">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" version="1.1" >
                                            @if ($loop->first)
                                                <symbol id="icinga2symbol" viewBox="-5 -5 105 105">
                                                    <path d="m 40.704846,40.305609 0,0 12.22301,-25.1583 m -20.21584,28.9132 0,0 -20.59136,-16.8982 m 26.61011,23.7512 0,0 14.00908,23.4685 m -14.95037,-24.5016 0,0 50.21059,-12.3916 m -50.21059,12.3916 0,0 -24.25801,34.7343" style="fill:none;stroke:#000000;stroke-width:1.2216469;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" id="path3906"/>
                                                    <path d="m 26.601396,35.704509 0,0 c 7.05279,-5.7261 17.39572,-4.693 23.13121,2.3477 5.73549,7.0407 4.70145,17.3659 -2.35135,23.0933 -7.05154,5.6323 -17.39447,4.5991 -23.12996,-2.4416 -5.73549,-6.9468 -4.60744,-17.2721 2.3501,-22.9994 z m 23.13121,-33.2309002 0,0 c 3.6674,-2.91059997 9.02688,-2.34779997 12.035,1.2195 3.00938,3.661 2.44535,9.0119002 -1.22205,12.0163002 -3.6674,3.0044 -9.02688,2.4403 -12.035,-1.2208 -3.00938,-3.661 -2.44535,-9.0119002 1.22205,-12.0150002 z m 30.37077,34.6393002 0,0 c -0.28202,-3.6611 2.5381,-6.8531 6.2055,-7.1345 3.76141,-0.2815 6.95754,2.4403 7.23955,6.1026 0.28201,3.7548 -2.5381,6.9456 -6.2055,7.2283 -3.66741,0.2814 -6.95754,-2.4416 -7.23955,-6.1964 z m -72.4951504,-7.416 0,0 c -1.1283,-2.3464 -0.18801,-5.3508 2.25659,-6.4778 2.4447304,-1.2195 5.3596004,-0.1876 6.5816504,2.2539 1.22205,2.3465 0.18801,5.3509 -2.25609,6.4766 -2.44498,1.2208 -5.3598504,0.1876 -6.5821504,-2.2527 l 0,0 z m 41.7483704,44.9658 0,0 c 0.188,-1.8774 1.88007,-3.2858 3.76015,-3.0969 1.88133,0.2814 3.29139,1.9712 3.00938,3.8487 -0.18801,1.8774 -1.88008,3.192 -3.76141,3.0031 -1.88008,-0.1876 -3.29014,-1.8774 -3.00812,-3.7549 l 0,0 z m -48.7063704,11.6411 0,0 c -0.47013,-6.1026 4.04313,-11.3596 10.1548804,-11.8287 6.11138,-0.469 11.37685,4.1314 11.84687,10.1389 0.47127,6.1013 -4.04216,11.4527 -10.1543,11.922 -6.1117504,0.3755 -11.4713504,-4.1309 -11.8474504,-10.2322 z" style="fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="path4016"/>
                                                </symbol>
                                            @endif
                                            <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icinga2symbol"></use>
                                        </svg>
                                    </a>
                                    <form object-id="{{ $service->service_object_id }}" method="POST" v-on:submit.prevent="mute"
                                        action="{{ route('TroubleDashboard.mute', ['Service', $service->service_object_id, $service->problem_has_been_acknowledged ? 0 : 1]) }}">
                                        <button type="submit" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg" :class="loading[{{ $service->service_object_id }}] ? 'fa-circle-o-notch fa-spin' : (acknowledged[{{ $service->service_object_id }}] ? 'fa-bell-o' : 'fa-bell-slash-o')"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center m-t-20">
            <div id="loader"></div>
        </div>
    </div>
</div>
