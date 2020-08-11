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

@section ('content')

    <div class="col-md-12">

        <h1 class="page-header">{{ $title }}</h1>

        {{--Quickstart--}}

        <div class="row">
            <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
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
                <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
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
            <div class="col-md-10 col-lg-8 col-xl-6">
                @include ('Generic.widgets.moduleDocu', [ 'urls' => [
                        'documentation' => 'https://devel.roetzer-engineering.com/confluence/display/NMS/NMS+PRIME',
                        'youtube' => 'https://www.youtube.com/channel/UCpFaWPpJLQQQLpTVeZnq_qA',
                        'forum' => 'https://devel.roetzer-engineering.com/confluence/display/nmsprimeforum/Welcome',
                    ]])
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
                @include ('bootstrap.widget', [
                        'content' => 'date',
                        'widget_icon' => 'calendar',
                        'widget_bg_color' => 'purple',
                    ]
                )
            </div>
        </div>

        <div class="row">
            @if (isset($tickets) && $tickets['own'])
                <div class="col-12 col-xl-6">
                    @section ('ticket_table')
                        @include ('ticketsystem::panels.ticket_table')
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

@stop
