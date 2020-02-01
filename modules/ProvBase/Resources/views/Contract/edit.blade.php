@extends ('Layout.split-nopanel')

@section('content_top')

    {!! $headline !!}

@stop
@php(dd($model_name, $view_var, $view_header, $form_path, $form_fields, $headline, $tabs, $relations, $action, $additional_data))

@section('content')
    <div id="vapp" style="height: 100%; width: 100%;">
        <test v-bind:current-tab="'Edit'" :tabs="{{json_encode($tabs)}}" :relations="{{json_encode($relations)}}" v-bind:md="12"></test>
    </div>
    {{-- Alert --}}
    @if (Session::has('alert'))
        @foreach (Session::get('alert') as $notif => $message)
            @include('bootstrap.alert', array('message' => $message, 'color' => $notif))
            <?php Session::forget("alert.$notif"); ?>
        @endforeach
    @endif

@stop
@section ('javascript_extra')
    @if (Module::collections()->has('PropertyManagement'))
        @include('provbase::Contract.hideAddress')
    @endif
@stop
