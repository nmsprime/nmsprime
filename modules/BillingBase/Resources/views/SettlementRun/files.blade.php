<!-- Dont cache Files for download as they would never change -->
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">

@if (isset($relation['view']['vars']))
    <div class="row">

        <!-- rerun button -->
        @if ($button['rerun'])
            <div class="col-12 text-center m-b-20">
                {{ Form::open(array('route' => ['SettlementRun.update', $view_var->id,] ,'method' => 'put')) }}
                    {{ Form::hidden('rerun', true) }}
                    <div class="row">
                        @if (isset($relation['view']['vars']['sepaaccs']))
                            <label for="description" style="margin-top: 10px;" class="col-md-3,5 control-label">{{ trans('messages.sr_repeat') }}</label>
                            <div class="col-md-4">
                                {{ Form::select('sepaaccount', $relation['view']['vars']['sepaaccs'], 0, [], ['style' => 'simple']) }}
                            </div>
                        @else
                            @DivOpen(4)
                            @DivClose
                        @endif
                        <div class="col-md-3">
                            {{ Form::submit( \App\Http\Controllers\BaseViewController::translate_view('Rerun Accounting Command', 'Button') , ['style' => 'simple']) }}
                        </div>
                    </div>
                {{ Form::close() }}
            </div>
        @endif

        <!-- button  to create invoices PDF for postal delivery -->
        @if ($button['postal'])
            <div class="col-md-12 text-center m-b-20">
                <div class="row">
                    <div class="col-md-1"></div>
                    {{ Form::open(array('route' => ['SettlementRun.create_post_invoices_pdf', $view_var->id,] ,'method' => 'put')) }}
                    {{ Form::submit( \App\Http\Controllers\BaseViewController::translate_view('create_post_invoices_pdf', 'Button') , ['style' => 'simple']) }}
                    {{ Form::close() }}
                </div>
            </div>
        @endif

        <!-- progress bar + message -->
        @if (\Session::get('job_id'))
            {{-- SettlementRunCommand running --}}
            <div class="alert alert-warning fade in m-b-15">{{ $status_msg }}</div>
            <div id="progress-msg" class="col-10"></div>
            <div class="col-10">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                </div>
            </div>
        @endif

        <!-- all accounting record files & invoices -->
        @foreach($relation['view']['vars']['files'] as $sepaacc => $files)
            @DivOpen(6)
                <table class="table table-bordered">
                <th class="text-center active"> {{ $sepaacc }} </th>
                @foreach ($files as $key => $file)
                    <tr><td class="text-center">{{ HTML::linkRoute('SettlementRun.download', $file->getFilename(), ['id' => $view_var->id, 'sepaacc' => $sepaacc, 'key' => $key]) }}</td></tr>
                @endforeach
                </table>
            @DivClose()
        @endforeach

    </div>
@endif

@section ('javascript_extra')

    @if (\Session::get('job_id'))
        <script type="text/javascript">

            $(document).ready(function()
            {
                setTimeout(function()
                {
                    var source = new EventSource("{!! route('SettlementRun.check_state') !!}");
                    source.onmessage = function(e)
                    {
                        if (e.data == 'reload')
                            location.reload();
                        else
                        {
                            var data = e.data ? JSON.parse(e.data) : {message: ''};
                            // document.getElementById('state').innerHTML = e.data;
                            $("#progress-msg").html(data.message);
                            if (data.hasOwnProperty('value')) {
                                $(".progress-bar").html(data.value + " %");
                                $(".progress-bar").css('width', data.value + "%");
                            }
                        }
                    }

                }, 500);
            });
        </script>
    @endif

@stop
