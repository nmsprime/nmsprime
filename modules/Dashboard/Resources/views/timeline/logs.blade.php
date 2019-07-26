<?php $bclasses = [
    'created' => 'fa-plus-square-o',
    'deleted' => 'fa-minus-square-o',
    'restored' => 'fa-plus-square-o',
    'updated' => 'fa-pencil-square-o',
    'updated N:M' => 'fa-pencil-square-o',
];
?>
{{--<ul class="timeline">--}}
    {{--@php--}}
        {{--\Carbon\Carbon::setLocale(\App::getLocale());--}}
        {{--$last_log_user_id = 0;--}}
    {{--@endphp--}}
    {{--@foreach($logs as $log)--}}
        {{--@if($last_log_user_id != $log->user_id)--}}
            {{--<li>--}}
                {{--<!-- begin timeline-time -->--}}
                {{--<div class="timeline-time">--}}
                    {{--<span class="date">{{langDateFormat($log->updated_at->timestamp)}}</span>--}}
                    {{--<span class="time">{{$log->updated_at->format('H:i:s')}}</span>--}}
                {{--</div>--}}
                {{--<!-- end timeline-time -->--}}
                {{--<!-- begin timeline-icon -->--}}
                {{--<div class="timeline-icon">--}}
                    {{--<a href="javascript:;">&nbsp;</a>--}}
                {{--</div>--}}
                {{--<!-- end timeline-icon -->--}}
                {{--<!-- begin timeline-body -->--}}
                {{--<div class="timeline-body">--}}
                    {{--<div class="timeline-header">--}}
                        {{--<span class="userimage"><i class="fa fa-user-circle-o fa-lg"></i></span>--}}
                        {{--<span class="username">{{$log->username}}</span>--}}
                        {{--<span class="pull-right text-muted">{{$log->updated_at->diffForHumans()}}</span>--}}
                    {{--</div>--}}
                    {{--<div class="timeline-content">--}}
                        {{--<h4>--}}
                            {{--<i class="fa {{$bclasses[$log->method]}}"></i>{{ trans("messages.dashboard.log.$log->method") }}--}}
                            {{--<a--}}
                                    {{--href="admin/{{$log->model}}/{{$log->model_id}}">{{ \App\Http\Controllers\BaseViewController::translate_view($log->model, 'Header')}}</a>--}}
                        {{--</h4>--}}
                        {{--@foreach(explode(',', $log->text) as $change)--}}
                            {{--<p class="ml-lg-5">--}}
                                {{--{!! $change !!}--}}
                            {{--</p>--}}
                        {{--@endforeach--}}
                        {{--@else--}}
                            {{--<h4>--}}
                                {{--<i class="fa {{$bclasses[$log->method]}}"></i>{{ trans("messages.dashboard.log.$log->method") }}--}}
                                {{--<a--}}
                                        {{--href="admin/{{$log->model}}/{{$log->model_id}}">{{ \App\Http\Controllers\BaseViewController::translate_view($log->model, 'Header')}}</a>--}}
                            {{--</h4>--}}
                            {{--@foreach(explode(',', $log->text) as $change)--}}
                                {{--<p class="ml-lg-5">--}}
                                    {{--{!! $change !!}--}}
                                {{--</p>--}}
                            {{--@endforeach--}}
                        {{--@endif--}}
                        {{--@php--}}
                            {{--$last_log_user_id = $log->user_id--}}
                        {{--@endphp--}}

                        {{--@if($last_log_user_id != $log->user_id)--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</li>--}}
        {{--@endif--}}
    {{--<!-- end timeline-body -->--}}
    {{--@endforeach--}}
{{--</ul>--}}
<div data-scrollbar="true" style="    height: calc(100vh - 160px)">
<div class="container py-2 px-0">

    @php
        \Carbon\Carbon::setLocale(\App::getLocale());
        $last_log_user_id = 0;
    @endphp
    @foreach($logs as $key => $log)
        @if($last_log_user_id != $log->user_id)
            <div class="row">
                {{--<div class="col-auto text-center flex-column d-none d-sm-flex">--}}
                    {{--<div class="row h-50">--}}
                        {{--@if(!$key)--}}
                            {{--<div class="col ">&nbsp;</div>--}}
                        {{--@else--}}
                            {{--<div class="col border-right">&nbsp;</div>--}}
                        {{--@endif--}}
                        {{--<div class="col">&nbsp;</div>--}}
                    {{--</div>--}}
                    {{--<h5 class="m-2">--}}
                        {{--<span class="badge badge-pill bg-success">&nbsp;</span>--}}
                    {{--</h5>--}}
                    {{--<div class="row h-50">--}}
                        {{--<div class="col border-right">&nbsp;</div>--}}
                        {{--<div class="col">&nbsp;</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
                <div class="col py-2">
                    <div class="card border-success shadow">
                        <div class="card-body p-10">
                            <div class="float-right text-success">{{langDateFormat($log->updated_at->timestamp)}}</div>
                            <h4 class="card-title text-success"><i
                                        class="fa fa-user-circle-o fa-lg"></i></span> {{$log->username}}</h4>
                            <div class="px-4">
                            <p class="card-text m-b-0">
                                <i class="fa {{$bclasses[$log->method]}}"></i> {{ trans("messages.dashboard.log.$log->method") }}
                                <a href="admin/{{$log->model}}/{{$log->model_id}}"> {{ \App\Http\Controllers\BaseViewController::translate_view($log->model, 'Header')}}</a>
                                <span class="date">{{langDateFormat($log->updated_at->timestamp)}}</span>
                                <span class="pull-right text-muted">{{$log->updated_at->diffForHumans()}}</span>
                                @php
                                    $changes = preg_split('@,@', $log->text, NULL, PREG_SPLIT_NO_EMPTY);
                                @endphp
                                @if(count($changes))
                                    <button class="btn btn-xs btn-outline-secondary" type="button"
                                            data-target="#details_{{$log->id}}"
                                            data-toggle="collapse">Show Changes {{count($changes)}}
                                    </button>
                            </p>
                                <div class="collapse p-3 ml-4 border rounded m-b-10" id="details_{{$log->id}}">
                                    <div class="p-2 text-monospace">
                                        @foreach($changes as $change)
                                            <div>{!! $change !!}</div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @php
                                if($last_log_user_id != $log->user_id){
                                    $last_log_user_id = $log->user_id;
                            }
                            @endphp
                            @else
                                <p class="card-text m-b-0">
                                    <i class="fa {{$bclasses[$log->method]}}"></i> {{ trans("messages.dashboard.log.$log->method") }}
                                    <a href="admin/{{$log->model}}/{{$log->model_id}}"> {{ \App\Http\Controllers\BaseViewController::translate_view($log->model, 'Header')}}</a>
                                    @php
                                        $changes = preg_split('@,@', $log->text, NULL, PREG_SPLIT_NO_EMPTY);
                                    @endphp
                                    @if(count($changes))
                                        <button class="btn btn-xs btn-outline-secondary" type="button"
                                                data-target="#details_{{$log->id}}"
                                                data-toggle="collapse">Show Changes {{count($changes)}}
                                        </button>
                                </p>
                                    <div class="collapse p-3 ml-4 border rounded m-b-10" id="details_{{$log->id}}">
                                        <div class="p-2 text-monospace">
                                            @foreach($changes as $change)
                                                <div>{!! $change !!}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @php
                                if($last_log_user_id != $log->user_id){
                                    $last_log_user_id = $log->user_id;
                            }
                            @endphp

                            @if( count($logs) == $key + 1 || $log->user_id != $logs[$key + 1]->user_id)
                        </div>
                    </div>
                </div>
            </div>
            </div>
                @endif
                @endforeach
            </div>
</div>
