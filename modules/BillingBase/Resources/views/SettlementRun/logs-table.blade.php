<div class='row'>
<div class="table-responsive">
    <table class="table streamtable table-bordered" width="100%">
    <thead>
        <tr class="active" style="text-align: center;">
            <th style="padding: 6px; vertical-align: inherit;"></th>
            <th style="padding: 6px; vertical-align: inherit;">{{ trans('view.Time') }}</th>
            <th style="padding: 6px; vertical-align: inherit;">{{ trans('view.Level') }}</th>
            <th style="padding: 6px; vertical-align: inherit;">
                <span style="vertical-align: inherit;">{{ trans('view.Message') }}</span>
                <a href="https://devel.roetzer-engineering.com/confluence/display/NMS/SettlementRun+Logs" target='_default' data-toggle="popover" data-container="body" data-trigger="hover" title="" data-placement="right"
                data-content="{{trans('messages.log_msg_descr')}}" data-original-title="">
                    <i class="fa fa-2x text-info fa-question-circle" style="float: inline-end; font-size: 22px; vertical-align: inherit"></i>
                </a>
            </th>
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
</div>
