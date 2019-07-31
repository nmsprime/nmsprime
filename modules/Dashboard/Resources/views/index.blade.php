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
    @DivOpen(12)
    <h1 class="page-header">{{ $title }}</h1>
    @DivClose()

    @DivOpen(6)
        <div class="row">
        @section ('dashboard_logs')
            @include('dashboard::timeline.logs')
        @stop
        @include ('bootstrap.panel', ['content' => "dashboard_logs", 'view_header' => 'All updates', 'md' => 12, 'height' => 'auto', 'i' => '2'])
        </div>
        <div class="row">
            @if ($tickets && $tickets['total'])
                @section ('ticket_table')
                    @include('ticketsystem::panels.ticket_table')
                @stop
                @include ('bootstrap.panel', [
                    'content' => "ticket_table",
                    'view_header' => trans('messages.dashbrd_ticket'),
                    'md' => 12, 'height' => 'auto', 'i' => '1',
                ])
            @endif
        </div>
    @DivClose()
    @DivOpen(6)
        <div class="row">
            @DivOpen(6)
                @include ('bootstrap.widget', [
                    'content' => 'tickets',
                    'widget_icon' => 'ticket',
                    'widget_bg_color' => 'orange',
                    'link_target' => '#anchor-tickets',
                ])
            @DivClose()
            @DivOpen(6)
                @include ('bootstrap.widget', [
                    'content' => 'date',
                    'widget_icon' => 'calendar',
                    'widget_bg_color' => 'purple',
                ])
            @DivClose()
        </div>
        <div class="row">
            @DivOpen(12)
                @include('dashboard::widgets.quickstart')
            @DivClose()
        </div>
        <div class="row">
            @if($services)
                @section ('impaired_services')
                    @include('HfcBase::panels.impaired_services')
                @stop
                @include ('bootstrap.panel', [
                    'content' => "impaired_services",
                    'view_header' => 'Impaired Services',
                    'md' => 12, 'height' => 'auto', 'i' => '3',
                ])
            @endif
        </div>
        <div class="row">
            @if($netelements)
                @section ('impaired_netelements')
                    @include('HfcBase::panels.impaired_netelements')
                @stop
                @include ('bootstrap.panel', [
                    'content' => "impaired_netelements",
                    'view_header' => 'Impaired Netelements',
                    'md' => 12, 'height' => 'auto', 'i' => '4',
                ])
            @endif
        </div>
    @DivClose()

    @if (isset($news) && $news)
        @section ('news')
            @include('dashboard::panels.news')
        @stop
        @include ('bootstrap.panel', ['content' => "news", 'view_header' => 'News', 'md' => 12, 'height' => '350px', 'i' => '5'])
    @endif
@stop
