@extends ('Layout.default')

@section ('tickets')
    <h4>{{ \App\Http\Controllers\BaseViewController::translate_view('Tickets', 'Dashboard') }}</h4>
    <p>
        @if ($tickets['total'])
            {{ $tickets['total'] }}
        @else
            {{ \App\Http\Controllers\BaseViewController::translate_view('NoTickets', 'Dashboard') }}
        @endif
    </p>
@stop
@section ('date')
    <h4>{{ \App\Http\Controllers\BaseViewController::translate_view('Date', 'Dashboard') }}</h4>
    <p>{{ date('d.m.Y') }}</p>
@stop

@section('content')
    <div class="col-md-12">

        <h1 class="page-header">{{ $title }}</h1>
        <div class="row">
            @section ('dashboard_logs')
                @include('dashboard::timeline.logs')
            @stop
            @include ('bootstrap.panel', array ('content' => "dashboard_logs", 'view_header' => 'All updates', 'md' => 6, 'height' => 'auto', 'i' => '2'))
            <div class="col-md-6">
                <div class="row">
                    @DivOpen(6)
                    @include ('bootstrap.widget',
                        array (
                            'content' => 'tickets',
                            'widget_icon' => 'ticket',
                            'widget_bg_color' => 'orange',
                            'link_target' => '#anchor-tickets',
                        )
                    )
                    @DivClose()
                    @DivOpen(6)
                    @include ('bootstrap.widget',
                        array (
                            'content' => 'date',
                            'widget_icon' => 'calendar',
                            'widget_bg_color' => 'purple',
                        )
                    )
                    @DivClose()
                </div>
                <div class="row">
                    @if ($tickets && $tickets['total'])
                    @section ('ticket_table')
                        @include('ticketsystem::panels.ticket_table')
                    @stop
                    @include ('bootstrap.panel', array ('content' => "ticket_table", 'view_header' => trans('messages.dashbrd_ticket'), 'md' => 12, 'height' => 'auto', 'i' => '1'))
                    @endif
                </div>
                <div class="row">
                    @if($services)
                    @section ('impaired_services')
                        @include('HfcBase::panels.impaired_services')
                    @stop
                    @include ('bootstrap.panel', array ('content' => "impaired_services", 'view_header' => 'Impaired Services', 'md' => 12, 'height' => 'auto', 'i' => '3'))
                    @endif
                </div>
                <div class="row">
                    @if($netelements)
                    @section ('impaired_netelements')
                        @include('HfcBase::panels.impaired_netelements')
                    @stop
                    @include ('bootstrap.panel', array ('content' => "impaired_netelements", 'view_header' => 'Impaired Netelements', 'md' => 12, 'height' => 'auto', 'i' => '4'))
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
