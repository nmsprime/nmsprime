<?php $bclasses = [
  'created' => 'fa-plus-square-o',
  'deleted' => 'fa-minus-square-o',
  'updated' => 'fa-pencil-square-o',
  'updated N:M' => 'fa-pencil-square-o'
];
?>
<ul class="timeline">
    <?php
        \Carbon\Carbon::setLocale(\App::getLocale());
    ?>
    @foreach($logs as $log)
        <li>
            <!-- begin timeline-time -->
            <div class="timeline-time">
                <span class="date">{{langDateFormat($log->updated_at->timestamp)}}</span>
                <span class="time">{{$log->updated_at->format('H:i:s')}}</span>
            </div>
            <!-- end timeline-time -->
            <!-- begin timeline-icon -->
            <div class="timeline-icon">
                <a href="javascript:;">&nbsp;</a>
            </div>
            <!-- end timeline-icon -->
            <!-- begin timeline-body -->
            <div class="timeline-body">
                <div class="timeline-header">
                    <span class="userimage"><i class="fa fa-user-circle-o fa-lg"></i></span>
                    <span class="username">{{$log->username}}</span>
                    <span class="pull-right text-muted">{{$log->updated_at->diffForHumans()}}</span>
                </div>
                <div class="timeline-content">
                    <h4>
                        <i class="fa {{$bclasses[$log->method]}}"></i>
                        {{$log->username}} {{ trans("messages.dashboard.log.$log->method") }} <a href="admin/{{$log->model}}/{{$log->model_id}}">{{ \App\Http\Controllers\BaseViewController::translate_view($log->model, 'Header')}}</a>
                    </h4>
                    @foreach(explode(',', $log->text) as $change)
                        <p class="ml-lg-5">
                            {!! $change !!}
                        </p>
                    @endforeach
                </div>
            </div>
            <!-- end timeline-body -->
        </li>
    @endforeach
</ul>
