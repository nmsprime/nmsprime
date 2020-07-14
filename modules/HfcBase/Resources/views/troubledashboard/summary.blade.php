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
            <div class="d-flex m-b-5 align-items-baseline">
                <i class="fa fa-circle text-success m-r-5"></i>
                {{ $impairedData['hostCounts']->ok }} Netelements are online
            </div>
            <div class="d-flex m-b-5 align-items-baseline">
                <i class="fa fa-circle text-danger m-r-5"></i>
                <a href="#javascript;" data-toggle="collapse" data-target="#hosts-critical">
                    {{ $impairedData['hostCounts']->critical }} Netelements are in critical state
                </a>
            </div>
        @endsection
        @include ('HfcBase::troubledashboard.summarycard', [
            'title' => 'Netelements',
            'content' => "netelement-chart",
            'canvas' => 'netelement',
        ])

        @section('service-chart')
            <div class="d-flex m-b-5 align-items-baseline">
                <i class="fa fa-circle text-success m-r-5"></i>
                {{ $impairedData['serviceCounts']->ok }} Services online
            </div>
            <div class="d-flex m-b-5 align-items-baseline">
                <i class="fa fa-circle text-warning m-r-5"></i>
                @if($impairedData['serviceCounts']->warning > 0)
                    <a href="#javascript;" data-toggle="collapse" data-target="#services-warning">
                        {{ $impairedData['serviceCounts']->warning }} Services are in warning state
                    </a>
                @else
                    {{ $impairedData['serviceCounts']->warning }} Services are in warning state
                @endif
            </div>
            <div class="d-flex m-b-5 align-items-baseline">
                <i class="fa fa-circle text-danger m-r-5"></i>
                @if($impairedData['serviceCounts']->critical > 0)
                    <a href="#javascript;" data-toggle="collapse" data-target="#services-critical">
                        {{ $impairedData['serviceCounts']->critical }} Services are in critical state
                    </a>
                @else
                    {{ $impairedData['serviceCounts']->critical }} Services are in critical state
                @endif
            </div>
            <div class="d-flex m-b-5 align-items-baseline">
                <i class="fa fa-circle text-gray m-r-5"></i>
                @if($impairedData['serviceCounts']->critical > 0)
                    <a href="#javascript;" data-toggle="collapse" data-target="#services-critical">
                        {{ $impairedData['serviceCounts']->unknown }} Services are in a unknown state
                    </a>
                @else
                    {{ $impairedData['serviceCounts']->unknown }} Services are in a unknown state
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
{{--
    <div id="hosts-critical" class="collapse">
        <div class="d-flex justify-content-around justify-content-sm-between flex-wrap p-5 border m-b-25">
            @foreach($hostsCritical as $service)
                <div class="d-flex align-items-center p-5 m-5" style="width:280px;">
                    <i class="fa fa-circle text-danger m-r-5"></i>
                    <a class="p-5" href="{{ route('NetElement.controlling_edit', [optional($service->icingaObject->netelement)->id, 0, 0]) }}" target="_blank" rel="noopener noreferrer">
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
--}}
