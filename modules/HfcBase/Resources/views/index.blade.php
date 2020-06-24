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
            @DivOpen(7)
                @include('Generic.quickstart')
            @DivClose()
            @DivOpen(2)
            @DivClose()
            @DivOpen(3)
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
            @DivOpen(3)
                @include('HfcBase::widgets.hfc')
            @DivClose()

            @DivOpen(5)
                @include('Generic.widgets.moduleDocu', [ 'urls' => [
                    'documentation' => 'https://devel.roetzer-engineering.com/confluence/display/NMS/IT+Maintenance',
                    'youtube' => 'https://www.youtube.com/playlist?list=PL07ZNkpZW6fyYWJ8xLHHhVLxoGQc72t2J',
                    'forum' => 'https://devel.roetzer-engineering.com/confluence/pages/viewpage.action?pageId=22773888',
                    ]])
            @DivClose()
        </div>

        <div class="row">
            @if($services)
                @section ('impaired_services')
                    @include('HfcBase::panels.impaired_services')
                @stop
                @include ('bootstrap.panel', array ('content' => "impaired_services", 'view_header' => 'Impaired Services', 'md' => 6, 'height' => 'auto', 'i' => '2'))
            @endif

            @if($netelements)
                @section ('impaired_netelements')
                    @include('HfcBase::panels.impaired_netelements')
                @stop
                @include ('bootstrap.panel', array ('content' => "impaired_netelements", 'view_header' => 'Impaired Netelements', 'md' => 6, 'height' => 'auto', 'i' => '1'))
            @endif
        </div>
    </div>
@stop
