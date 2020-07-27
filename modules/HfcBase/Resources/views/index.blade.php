@extends ('Layout.default')

@section ('date')
    <h4>{{ \App\Http\Controllers\BaseViewController::translate_view('Date', 'Dashboard') }}</h4>
    <p>{{ date('d.m.Y') }}</p>
@stop

@section('content')

    <div class="col-md-12">

        <h1 class="page-header">{{ $title }}</h1>

        <div class="row">
            @DivOpen(8)
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
            <div class="d-flex">
                <div>
                    @include('HfcBase::widgets.hfc')
                </div>
                <div class="col-md-8">
                    @include('Generic.widgets.moduleDocu', [ 'urls' => [
                        'documentation' => 'https://devel.roetzer-engineering.com/confluence/display/NMS/IT+Maintenance',
                        'youtube' => 'https://www.youtube.com/playlist?list=PL07ZNkpZW6fyYWJ8xLHHhVLxoGQc72t2J',
                        'forum' => 'https://devel.roetzer-engineering.com/confluence/pages/viewpage.action?pageId=22773888',
                        ]])
                </div>
            </div>
            @DivClose()
            <div class="col-md-4">
                <div>
                    @section ('impaired_summary')
                        @include('HfcBase::troubledashboard.summary')
                    @stop
                    @include ('bootstrap.panel', [
                        'content' => "impaired_summary",
                        'view_header' => 'System Summary',
                        'i' => '3'
                    ])
                </div>
                <div>
                    @section ('dashboard_logs')
                        @include('dashboard::timeline.logs')
                    @stop
                    @include ('bootstrap.panel', [
                        'content' => "dashboard_logs",
                        'view_header' => 'All updates',
                        'height' => '200px',
                        'i' => '2'
                    ])
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
@include('HfcBase::troubledashboard.tablejs')
@include('HfcBase::troubledashboard.summaryjs')
@stop
