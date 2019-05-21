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
            <div class="col-md-12">
                @include('Generic.quickstart')
            </div>
        </div>
        <div class="row">
            @if($services)
            @section ('impaired_services')
                @include('HfcBase::panels.impaired_services')
            @stop
            @include ('bootstrap.panel', array ('content' => "impaired_services", 'view_header' => 'Impaired Services', 'md' => 7, 'height' => 'auto', 'i' => '2'))
            @endif

            <div class="col-md-5">
                <div class="row">
                    @DivOpen(6)
                    @include('HfcBase::widgets.hfc')
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
                    <div class="col-md-12">
                        @if($netelements)
                        @section ('impaired_netelements')
                            @include('HfcBase::panels.impaired_netelements')
                        @stop
                        @include ('bootstrap.panel', array ('content' => "impaired_netelements", 'view_header' => 'Impaired Netelements', 'md' => 12, 'height' => 'auto', 'i' => '1'))
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        @include('HfcBase::widgets.documentation')
                    </div>
                </div>
            </div>
        </div>
        {{--TODO: implement each module its own news--}}
        {{--<div class="row">--}}
            {{--@if (isset($news) && $news)--}}
            {{--@section ('news')--}}
                {{--@include('HfcBase::panels.news')--}}
            {{--@stop--}}
            {{--@include ('bootstrap.panel', array ('content' => "news", 'view_header' => 'News', 'md' => 12, 'height' => '350px', 'i' => '5'))--}}
            {{--@endif--}}
        {{--</div>--}}

    </div>

@stop
