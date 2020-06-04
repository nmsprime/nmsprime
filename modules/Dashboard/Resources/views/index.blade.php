@extends ('Layout.default')

@section ('contracts_total')
<h4>{{ App\Http\Controllers\BaseViewController::translate_view('Contracts', 'Dashboard') }} {{ date('m/Y') }}</h4>
<p style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
    @if (isset($contracts_data['total']) && $contracts_data['total'])
    {{ $contracts_data['total'] }}
@else
    {{ App\Http\Controllers\BaseViewController::translate_view('NoContracts', 'Dashboard') }}
@endif
</p>
@stop

@section ('income')
<h4>{{ App\Http\Controllers\BaseViewController::translate_view('Net Income', 'Dashboard') }} {{ date('m/Y') }}</h4>
<p style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
    @if (isset($income_data['total']) && $income_data['total'])
        {{ number_format($income_data['total'], 0, ',', '.') }}
    @else
        {{ number_format(0, 0, ',', '.') }}
    @endif
</p>
@stop

@section ('tickets')
    <h4>{{ App\Http\Controllers\BaseViewController::translate_view('Tickets', 'Dashboard') }}</h4>
    <p style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
        {{ $tickets['total'] ?: App\Http\Controllers\BaseViewController::translate_view('NoTickets', 'Dashboard') }}
    </p>
@stop

@section ('date')
    <h4>{{ App\Http\Controllers\BaseViewController::translate_view('Date', 'Dashboard') }}</h4>
    <p>{{ date('d.m.Y') }}</p>
@stop

@section ('contracts_total')
<h4>{{ App\Http\Controllers\BaseViewController::translate_view('Contracts', 'Dashboard') }} {{ date('m/Y') }}</h4>
<p style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
    @if (isset($contracts_data['total']) && $contracts_data['total'])
        {{ $contracts_data['total'] }}
    @else
        {{ App\Http\Controllers\BaseViewController::translate_view('NoContracts', 'Dashboard') }}
    @endif
</p>
@stop

@section('content')
<h1 class="page-header h1">{{ $title }}</h1>
<div class="row">
    <div class="col-xs-12 col-sm-6 col-xl-3">
        @include ('bootstrap.widget', [
        'content' => 'contracts_total',
            'widget_icon' => 'users',
            'widget_bg_color' => 'green',
            'link_target' => '#anchor-contracts',
        ])
    </div>
    <div class="col-xs-12 col-sm-6 col-xl-3">
        @include ('bootstrap.widget', [
            'content' => 'income',
            'widget_icon' => 'euro',
            'widget_bg_color' => 'blue',
            'link_target' => '#anchor-income',
        ])
    </div>
    <div class="col-xs-12 col-sm-6 col-xl-4">
        @include ('bootstrap.widget', [
            'content' => 'tickets',
            'widget_icon' => 'ticket',
            'widget_bg_color' => 'orange',
            'link_target' => '#anchor-tickets',
        ])
    </div>
    <div class="col-xs-12 col-sm-6 col-xl-2">
        @include ('bootstrap.widget',[
            'content' => 'date',
            'widget_icon' => 'calendar',
            'widget_bg_color' => 'purple',
            'linkText' => 'Heute'
        ])
    </div>
</div>
<div class="row">
    <div class="col-xl-8 no-gutters ui-sortable">
        @if($impairedData)
            @section ('trouble_dashboard')
                @include('HfcBase::troubledashboard.panel')
            @stop
            @include ('bootstrap.panel', [
                'content' => "trouble_dashboard",
                'view_header' => 'Trouble Dashboard',
                'height' => 'auto',
                'i' => '5'
            ])
        @endif
    </div>
    <div class="col-xl-4 no-gutters ui-sortable">
        @include('dashboard::widgets.quickstart')
        @if ($tickets && $tickets['total'])
            @section ('ticket_table')
                @include('ticketsystem::panels.ticket_table')
            @stop
            @include ('bootstrap.panel', [
                'content' => "ticket_table",
                'view_header' => trans('messages.dashboard_ticket'),
                'height' => 'auto',
                'i' => '1',
            ])
        @endif
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
</div>
<div class="row">
@if (isset($news) && $news)
    @section ('news')
        @include('dashboard::panels.news')
    @stop
    @include ('bootstrap.panel', [
        'content' => "news",
        'view_header' => 'News',
        'height' => '350px',
        'md' => 12,
        'i' => '5'
    ])
@endif
</div>
@stop

@section('javascript')
@include('HfcBase::troubledashboard.javascript')
@stop
