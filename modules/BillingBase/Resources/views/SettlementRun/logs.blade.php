<div class='row'>
    {{-- Download logfile button --}}
    @DivOpen(3)
        {{ Form::open(array('route' => ['SettlementRun.log_dl', $view_var->id], 'method' => 'get')) }}
            {{ Form::submit(trans('view.sr_dl_logs') , ['style' => 'simple']) }}
        {{ Form::close() }}
    @DivClose()

    {{-- Link for help messages --}}
    @DivOpen(8)
    @DivClose()
    @DivOpen(1)
        <div class="m-b-20" align='left'>
            <a href="https://devel.roetzer-engineering.com/confluence/display/NMS/SettlementRun+Logs" target='_default' data-toggle="popover" data-container="body" data-trigger="hover" title="" data-placement="right"
            data-content="{{trans('messages.log_msg_descr')}}" data-original-title=""><i class="fa fa-2x text-info p-t-5 fa-question-circle"></i></a>
        </div>
    @DivClose()
</div>

<div class="table-responsive">
    <table class="table streamtable table-bordered" width="100%">
    <thead>
        <tr class="active">
            <th></th>
            <th>Time</th>
            <th>Type</th>
            <th>Message</th>
        </tr>
    </thead>
    <tbody>
        <div class='row'>

        {{-- Table with log entries --}}
        @if (isset($logs))
            @foreach($logs as $row)
                <tr class="{{ $row['color'] }}">
                    <td></td>
                    <?php unset($row['color']) ?>

                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        @endif
    </tbody>
    </table>
</div>
