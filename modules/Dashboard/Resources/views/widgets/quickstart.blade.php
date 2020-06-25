<div class="widget widget-stats bg-grey">
    <div class="stats-icon"><i class="fa fa-link"></i></div>
    {{-- info/data --}}
    <div class="stats-info text-center">
        @include('bootstrap.quickstartbutton', [
            'route' => 'Contract.index',
            'icon' => 'address-book-o',
            'title' => trans('view.dashboard.contractIndexPage'),
        ])
        @include('bootstrap.quickstartbutton', [
            'route' => 'Modem.index',
            'icon' => 'hdd-o',
            'title' => trans('view.dashboard.modemIndexPage'),
        ])
        @if(Module::collections()->has('HfcBase'))
            @include('bootstrap.quickstartbutton', [
                'route' => 'NetElement.index',
                'icon' => 'object-ungroup',
                'title' => trans('view.dashboard.netelementIndexPage'),
            ])
        @endif
        @if(Module::collections()->has('Ticketsystem'))
            @include('bootstrap.quickstartbutton', [
                'route' => 'Ticket.index',
                'icon' => 'ticket',
                'title' => trans('view.dashboard.ticketIndexPage'),
            ])
        @endif

    </div>
    {{-- reference link --}}
    <div class="stats-link noHover"><a href="#">{{ trans('view.Dashboard_Quickstart') }}</a></div>
</div>
