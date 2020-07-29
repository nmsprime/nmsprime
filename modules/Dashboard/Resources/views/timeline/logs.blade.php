<?php $bclasses = [
    'created' => 'fa-plus-square-o',
    'deleted' => 'fa-minus-square-o',
    'restored' => 'fa-plus-square-o',
    'updated' => 'fa-pencil-square-o',
    'updated N:M' => 'fa-pencil-square-o',
];

	$previewLength = 70;
    $last_log_user_id = 0;
?>

<div data-scrollbar="true" style="height: calc(100vh - 160px)">
<div class="container py-2 px-0">
    @foreach ($logs as $key => $log)
        @php
            $changes = preg_split('@,@', $log->text, NULL, PREG_SPLIT_NO_EMPTY);

            if ($log->model === 'Comment' && $log->method === 'created') {
                $changes[0] = $log->comment;
            }
        @endphp

        <!-- next user -->
        @if ($last_log_user_id != $log->user_id)
            <div class="row">
                <div class="col py-0">
                    <div class="card border-success shadow">
                        <div class="card-body py-1">
                            <div class="float-right text-success">{{langDateFormat($log->updated_at->timestamp)}}</div>
                                <h4 class="card-title text-success">
                                    <i class="fa fa-user-circle-o fa-lg"></i>
                                    </span> {{$log->username}}
                                </h4>
                                <div class="px-4">
        @endif

        <!-- changes of the user -->
        <p class="card-text m-b-0">
            <i class="fa {{$bclasses[$log->method]}}" style="width: 13px"></i> {{ trans("messages.dashboard.log.$log->method") }}
            <a href="admin/{{$log->model}}/{{$log->model_id}}"> {{ \App\Http\Controllers\BaseViewController::translate_view($log->model, 'Header')}}</a>
            @if ($last_log_user_id != $log->user_id)
                <span class="pull-right text-muted">{{$log->updated_at->diffForHumans()}}</span>
            @endif
            @if ($changes)
                @if ($log->method === 'created')
                    @if (strlen($changes[0]) > $previewLength)
                        <span style="margin-left: 10px">{{substr($changes[0], 0, $previewLength)}}</span>
                        <button class="btn btn-xs btn-outline-secondary" type="button"
                                data-target="#details_{{$log->id}}"
                                data-toggle="collapse"> ...
                        </button>
                    @else
                        <span style="margin-left: 10px">{{$changes[0]}}</span>
                    @endif
                @else
                    <button class="btn btn-xs btn-outline-secondary" type="button"
                            data-target="#details_{{$log->id}}"
                            data-toggle="collapse">{{ trans_choice('view.showChanges', count($changes), ['num' => count($changes)]) }}
                    </button>
                @endif
                </p>
                <div class="collapse p-3 ml-4 border rounded m-b-10" id="details_{{$log->id}}">
                    <div class="p-2 text-monospace">
                        @foreach($changes as $change)
                            <div>{!! $change !!}</div>
                        @endforeach
                    </div>
                </div>
            @else
        </p>
            @endif

        @php
            if ($last_log_user_id != $log->user_id) {
                $last_log_user_id = $log->user_id;
            }
        @endphp

        <!-- End of changes of current user -->
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
