@extends ('Layout.default')

@section ('date')
    <h4>{{ \App\Http\Controllers\BaseViewController::translate_view('Date', 'Dashboard') }}</h4>
    <p>{{ date('d.m.Y') }}</p>
@stop

@section('content')

    <div class="col-md-12">

        <h1 class="page-header">{{ $title }}</h1>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div>
                    @section ('impaired_services')
                    @include('HfcBase::troubledashboard.panel')
                    @stop
                    @include ('bootstrap.panel', [
                        'content' => "impaired_services",
                        'view_header' => 'Trouble Dashboard',
                        'height' => 'auto',
                        'i' => '4'
                        ])
                </div>
                <div class="row">
                    <div class="col">
                        @section ('dashboard_logs')
                            @include('dashboard::timeline.logs')
                        @stop
                        @include ('bootstrap.panel', [
                            'content' => "dashboard_logs",
                            'view_header' => 'All updates',
                            'height' => 'auto',
                            'i' => '2'
                        ])
                    </div>
                    @if (isset($tickets) && $tickets['own'])
                        <div class="col-12 col-xl-6">
                            @section ('ticket_table')
                                @include('ticketsystem::panels.ticket_table')
                            @stop
                            @include ('bootstrap.panel', [
                                'content' => "ticket_table",
                                'view_header' => "Your new Tickets",
                                'height' => 'auto',
                                'i' => '1',
                            ])
                        </div>
                    @endif
                </div>
            </div>
            <div class="d-flex flex-column-reverse col-12 col-lg-4 flex-lg-column">
                <div>
                    @section ('allTickets')
                        <h4>{{ App\Http\Controllers\BaseViewController::translate_view('TotalTickets', 'Dashboard') }}</h4>
                        <p style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
                            {{ $tickets['total'] ?? App\Http\Controllers\BaseViewController::translate_view('NoTickets', 'Dashboard') }}
                        </p>
                    @stop
                    @include ('bootstrap.widget', [
                        'content' => 'allTickets',
                        'widget_icon' => 'ticket',
                        'widget_bg_color' => 'orange',
                        'link_target' => route('Ticket.index', ['show_filter' => 'newTickets']),
                    ])
                </div>
                @if (isset($tickets) && $tickets['own'])
                    <div>
                        @section ('tickets')
                            <h4>{{ App\Http\Controllers\BaseViewController::translate_view('Tickets', 'Dashboard') }}</h4>
                            <p style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
                                {{ $tickets['own'] ?? App\Http\Controllers\BaseViewController::translate_view('NoTickets', 'Dashboard') }}
                            </p>
                        @stop
                        @include ('bootstrap.widget', [
                            'content' => 'tickets',
                            'widget_icon' => 'ticket',
                            'widget_bg_color' => 'orange',
                            'link_target' => '#anchor-tickets',
                        ])
                    </div>
                @endif
                <div>
                    @section ('impaired_summary')
                        @include('HfcBase::troubledashboard.summary', ['aside' => true])
                    @stop
                    @include ('bootstrap.panel', [
                        'content' => "impaired_summary",
                        'view_header' => 'System Summary',
                        'i' => '3'
                    ])
                </div>
                <div>
                    @include('HfcBase::widgets.hfc')
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
@include('HfcBase::troubledashboard.tablejs')
@include('HfcBase::troubledashboard.summaryjs')
@stop
