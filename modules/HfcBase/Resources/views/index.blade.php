@extends ('Layout.default')

@section ('date')
    <h4>{{ \App\Http\Controllers\BaseViewController::translate_view('Date', 'Dashboard') }}</h4>
    <p>{{ date('d.m.Y') }}</p>
@stop

@section('content')

    <div class="col-md-12">

        <h1 class="page-header">{{ $title }}</h1>

        {{--Quickstart--}}

        <div class="row">
            @DivOpen(8)
                @include('Generic.quickstart')
            @DivClose()
            @DivOpen(4)
                @include('HfcBase::widgets.hfc')
            @DivClose()

            @if(! empty($impairedData))
                @section ('impaired_services')
                    @include('HfcBase::troubledashboard.panel')
                @stop
                @include ('bootstrap.panel', [
                    'content' => "impaired_services",
                    'view_header' => 'Trouble Dashboard',
                    'height' => 'auto',
                    'i' => '2'
                ])
            @endif

            @DivOpen(5)
                @include('Generic.widgets.moduleDocu', [ 'urls' => [
                    'documentation' => 'https://devel.roetzer-engineering.com/confluence/display/NMS/IT+Maintenance',
                    'youtube' => 'https://www.youtube.com/playlist?list=PL07ZNkpZW6fyYWJ8xLHHhVLxoGQc72t2J',
                    'forum' => 'https://devel.roetzer-engineering.com/confluence/pages/viewpage.action?pageId=22773888',
                    ]])
            @DivClose()
        </div>
    </div>
@stop

@section('javascript')
@include('HfcBase::troubledashboard.javascript')
@stop
