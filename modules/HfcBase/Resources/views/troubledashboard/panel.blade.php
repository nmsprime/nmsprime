<div id="troubleDash">
    <div>
        <h2 class="m-b-25">Summary</h2>
        <div class="d-flex flex-column flex-lg-row justify-content-center m-b-25 p-r-15 p-l-15">
            @section('modem-chart')
                <div class="d-flex m-b-5 align-items-baseline">
                    <i class="fa fa-circle text-success m-r-5"></i>
                    {{ $modem_statistics->online - $modem_statistics->warning - $modem_statistics->critical }} Modems with good signal
                </div>
                <div class="d-flex m-b-5 align-items-baseline">
                    <i class="fa fa-circle text-warning m-r-5"></i>
                    {{ $modem_statistics->warning }} Modems have warning state
                </div>
                <div class="d-flex m-b-5 align-items-baseline">
                    <i class="fa fa-circle text-danger m-r-5"></i>
                    {{ $modem_statistics->critical }} Modems have critical state
                </div>
                <div class="d-flex m-b-5 align-items-baseline">
                    <i class="fa fa-circle text-gray m-r-5"></i>
                    {{ $modem_statistics->all -$modem_statistics->online }} Modems offline
                </div>
            @endsection
            @include ('HfcBase::troubledashboard.summarycard', [
                'title' => 'Modems',
                'content' => "modem-chart",
                'canvas' => 'modem',
            ])

            @section('netelement-chart')
                @php
                $hostsCritical = $impairedData['hosts']->filter(function ($host) {
                    return $host->last_hard_state == 2;
                });
                $hostsCriticalCount = $hostsCritical->count();
                $hostsOk = $impairedData['hosts']->count() - $hostsCriticalCount;
                @endphp
                <div class="d-flex m-b-5 align-items-baseline">
                    <i class="fa fa-circle text-success m-r-5"></i>
                    {{ $hostsOk }} Netelements are online
                </div>
                <div class="d-flex m-b-5 align-items-baseline">
                    <i class="fa fa-circle text-danger m-r-5"></i>
                    <a href="#javascript;" data-toggle="collapse" data-target="#hosts-critical">
                        {{ $hostsCriticalCount }} Netelements are in critical state
                    </a>
                </div>
            @endsection
            @include ('HfcBase::troubledashboard.summarycard', [
                'title' => 'Netelements',
                'content' => "netelement-chart",
                'canvas' => 'netelement',
            ])

            @section('service-chart')
                @php
                $servicesCritical = $impairedData['services']->filter(function ($service) {
                        return $service->last_hard_state == 2;
                    });
                $servicesCriticalCount = $servicesCritical->count();
                $servicesWarning = $impairedData['services']->filter(function ($service) {
                        return $service->last_hard_state == 1;
                    });
                $servicesWarningCount = $servicesWarning->count();
                $servicesOk = $impairedData['services']->count() - $servicesCriticalCount - $servicesWarningCount;
                @endphp
                <div class="d-flex m-b-5 align-items-baseline">
                    <i class="fa fa-circle text-success m-r-5"></i>
                    {{ $servicesOk }} Services online
                </div>
                <div class="d-flex m-b-5 align-items-baseline">
                    <i class="fa fa-circle text-warning m-r-5"></i>
                    @if($servicesWarningCount > 0)
                        <a href="#javascript;" data-toggle="collapse" data-target="#services-critical">
                            {{ $servicesWarningCount }} Services are in critical state
                        </a>
                    @else
                        {{ $servicesWarningCount }} Services are in critical state
                    @endif
                </div>
                <div class="d-flex m-b-5 align-items-baseline">
                    <i class="fa fa-circle text-danger m-r-5"></i>
                    @if($servicesCriticalCount > 0)
                        <a href="#javascript;" data-toggle="collapse" data-target="#services-critical">
                            {{ $servicesCriticalCount }} Services are in critical state
                        </a>
                    @else
                        {{ $servicesCriticalCount }} Services are in critical state
                    @endif
                </div>
            @endsection
            @include ('HfcBase::troubledashboard.summarycard', [
                'title' => 'Services',
                'content' => "service-chart",
                'canvas' => 'service',
            ])
        </div>
    </div>
    <div id="hosts-critical" class="collapse">
        <div class="d-flex justify-content-around justify-content-sm-between flex-wrap p-5 border m-b-25">
            @foreach($hostsCritical as $service)
                <div class="d-flex align-items-center p-5 m-5" style="width:280px;">
                    <i class="fa fa-circle text-danger m-r-5"></i>
                    <a class="p-5" href="{{ route('NetElement.controlling_edit', [$service->icingaObject->netelement->id, 0, 0]) }}" target="_blank" rel="noopener noreferrer">
                        {{ $service->icingaObject->name1 }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
    <div id="services-warning" class="collapse">
        <div class="d-flex justify-content-around justify-content-sm-between flex-wrap p-5 border m-b-25">
            @foreach($servicesWarning as $service)
                <div class="d-flex align-items-center p-5 m-5" style="width:140px;">
                    <i class="fa fa-circle text-warning m-r-5"></i>
                    <div>{{ $service->icingaObject->name2 }}</div>
                </div>
            @endforeach
        </div>
    </div>
    <div id="services-critical" class="collapse">
        <div class="d-flex justify-content-around justify-content-sm-between flex-wrap p-5 border m-b-25">
            @foreach($servicesCritical as $service)
                <div class="d-flex align-items-center p-5 m-5" style="width:140px;">
                    <i class="fa fa-circle text-danger m-r-5"></i>
                    <div>{{ $service->icingaObject->name2 }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="d-flex justify-content-end m-b-15">
        <div v-if="!showMuted" v-on:click="showMuted = !showMuted" class="m-r-10" title="show Muted"><i class="fa fa-2x fa-eye-slash"></i></div>
        <div v-if="showMuted" v-on:click="showMuted = !showMuted" class="m-r-10" title="hide Muted"><i class="fa fa-2x fa-eye"></i></div>
    </div>
    <div class="height-lg" style="overflow-y:scroll;overflow-x:hidden">
        <table class="table m-b-0" style="width:100%;">
            <thead>
                <tr>
                    <th class="d-table-cell position-sticky fixed-top"></th>
                    <th class="d-table-cell position-sticky fixed-top">Host</th>
                    <th class="d-none d-lg-table-cell position-sticky fixed-top">Service</th>
                    <th class="d-none d-lg-table-cell position-sticky fixed-top">Status</th>
                    <th class="d-none d-wide-table-cell position-sticky fixed-top">Since</th>
                    <th class="d-none d-wide-table-cell position-sticky fixed-top">Last OK</th>
                    <th class="d-table-cell position-sticky fixed-top">#aM</th>
                    <th class="d-none d-sm-table-cell position-sticky fixed-top text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="" style="overflow:scroll;">
            @foreach ($impairedData['impairedData'] as $i => $impaired)
                @if ($impaired->last_hard_state > 0)
                    <tr v-show="(!!acknowledged[{{ $impaired->icingaObject->object_id }}] && showMuted) || (!!!acknowledged[{{ $impaired->icingaObject->object_id }}] && !showMuted)" class="{{ $colors[$impaired->last_hard_state] }}">
                        <td style="{{ $hasAdditionalData = $impaired->hasAdditionalData() ? 'cursor: pointer;' : '' }}" data-toggle="collapse" data-target=".{{$i}}collapsedservice">
                            <i class="fa fa-{{ $hasAdditionalData ? 'plus' : 'info' }}"></i>
                        </td>
                        <td class='f-s-13 breakAll' style="{{ $hasAdditionalData ? 'cursor: pointer;' : '' }}" data-toggle="collapse" data-target=".{{$i}}collapsedservice">{{ $impaired->netelement ? $impaired->netelement->name : $impaired->icingaObject->name1 }}</td>
                        <td class='f-s-13 d-none d-lg-table-cell breakAll' style="{{ $hasAdditionalData ? 'cursor: pointer;' : '' }}" data-toggle="collapse" data-target=".{{$i}}collapsedservice">{{ $impaired->icingaObject->name2 ?? 'Netelement'}}</td>
                        <td class='f-s-13 d-none d-lg-table-cell' style="{{ $hasAdditionalData ? 'cursor: pointer;' : '' }}" data-toggle="collapse" data-target=".{{$i}}collapsedservice">{{ preg_replace('/[<>]/m', '', $impaired->output) }}</td>
                        <td class='f-s-13 d-none d-wide-table-cell' style="{{ $hasAdditionalData ? 'cursor: pointer;' : '' }}" data-toggle="collapse" data-target=".{{$i}}collapsedservice">{{ $impaired->last_hard_state_change }}</td>
                        <td class='f-s-13 d-none d-wide-table-cell' style="{{ $hasAdditionalData ? 'cursor: pointer;' : '' }}" data-toggle="collapse" data-target=".{{$i}}collapsedservice">{{ $impaired->last_time_ok }}</td>
                        <td style="{{ $hasAdditionalData ? 'cursor: pointer;' : '' }}" data-toggle="collapse" data-target=".{{$i}}collapsedservice">{{ $impaired->affectedModems }}</td>
                        <td class="d-none d-sm-table-cell" max-width="180px" >
                            <div class="d-flex align-items-center justify-content-end">
                                <a href="{{ $impaired->toIcingaWeb() }}" target="_blank" class="btn btn-light p-5 m-l-10">
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
                                @if ($impaired instanceof Modules\HfcBase\Entities\IcingaHostStatus)
                                    <a href="{{ route('NetElement.controlling_edit', [$impaired->icingaObject->netelement->id, 0, 0]) }}" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-wrench"></i></a>
                                @endif
                                <form object-id="{{ $impaired->icingaObject->object_id }}" method="POST" v-on:submit.prevent="mute"
                                @if ($impaired instanceof Modules\HfcBase\Entities\IcingaServiceStatus)
                                    action="{{ route('TroubleDashboard.mute', ['Service', $impaired->service_object_id, $impaired->problem_has_been_acknowledged ? 0 : 1]) }}">
                                @else
                                    action="{{ route('TroubleDashboard.mute', ['Host', $impaired->host_object_id, $impaired->problem_has_been_acknowledged ? 0 : 1]) }}">
                                @endif
                                    {{ csrf_field() }}
                                    <button type="submit" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg" :class="loading[{{ $impaired->icingaObject->object_id }}] ? 'fa-circle-o-notch fa-spin' : (acknowledged[{{ $impaired->icingaObject->object_id }}] ? 'fa-bell-o' : 'fa-bell-slash-o')"></i></button>
                                </form>
                                <a href="{{ $impaired->toMap() }}" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-map"></i></a>
                                <a href="{{ $impaired->toTicket() }}" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-ticket"></i></a>
                            </div>
                        </td>
                    </tr>
                    @foreach ($impaired->additionalData as $perf)
                        <tr class="collapse {{$i}}collapsedservice {{$perf['cls']}}">
                            <td class="d-lg-none" colspan="2" class="p-20">{{$perf['text']}}</td>
                            <td class="d-none d-lg-table-cell d-wide-none" colspan="4" class="p-20">
                                @if($perf['per'] !== null)
                                        <div class=" d-flex-md align-items-center progress progress-striped m-b-0">
                                            <div class="progress-bar progress-bar-{{ $perf['cls'] ?? $colors[$impaired->last_hard_state] }}" style="width: {{$perf['per']}}%">
                                                <span class='text-inverse' style="width:auto;left:40px;">{{$perf['text']}}</span>
                                            </div>
                                        </div>
                                @else
                                    {{ $perf['text'] }}: {{ $perf['val'] }}
                                @endif
                            </td>
                            <td class="d-none d-wide-table-cell" colspan="6" class="p-20">
                                @if($perf['per'] !== null)
                                        <div class=" d-flex-md align-items-center progress progress-striped m-b-0">
                                            <div class="progress-bar progress-bar-{{ $perf['cls'] ?? $colors[$impaired->last_hard_state] }}" style="width: {{$perf['per']}}%">
                                                <span class='text-inverse' style="width:auto;left:40px;">{{$perf['text']}}</span>
                                            </div>
                                        </div>
                                @else
                                    {{ $perf['text'] }}: {{ $perf['val'] }}
                                @endif
                            </td>
                            <td>
                                {{ ($perf['id'] && $netelements[$perf['id']]) ? $netelements[$perf['id']]->modems_count : 0 }}
                            </td>
                            <td class="d-none d-sm-table-cell">
                                <div class="d-flex align-items-center">
                                    @if (isset($netelements[$perf['id']]))
                                    <a href="{{ route('NetElement.controlling_edit', [$netelements[$perf['id']]->id, 0, 0]) }}" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-wrench"></i></a>
                                    <a href="{{ $netelements[$perf['id']]->prov_device_id ? route('ProvMon.index', [$netelements[$perf['id']]->prov_device_id]): route('ProvMon.diagram_edit', [$netelements[$perf['id']]->cluster]) }}" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-area-chart"></i></a>
                                    <!-- <a href="#" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-hdd-o"></i></a> -->
                                    <a href="{{ $netelements[$perf['id']]->cluster ? route('TreeTopo.show', ['cluster', $netelements[$perf['id']]->cluster]) : route('TreeTopo.show', ['id', $netelements[$perf['id']]->id]) }}" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-map"></i></a>
                                    @endif
                                    <a href="{{ $impaired->toSubTicket($netelements, $perf) }}" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-ticket"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
            </tbody>
        </table>
    </div>
</div>
