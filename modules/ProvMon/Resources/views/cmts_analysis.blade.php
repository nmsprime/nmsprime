@extends ('provmon::cmts_split')


@section('content_dash')
	@if ($dash)
		<font color="grey">{{$dash}}</font>
	@else
		<font color="green"><b>TODO</b></font>
	@endif
@stop

@section('content_cacti')

	@if ($host_id)
		<iframe id="cacti-diagram" src="/cacti/graph_view.php?action=preview&columns=2&host_id={{$host_id}}" sandbox="allow-forms allow-scripts allow-pointer-lock allow-popups allow-same-origin" width="100%" height="100%" onload="resizeIframe(this)" scrolling="no" style="overflow:hidden; display:block; min-height: 100%; border: none; position: relative;"></iframe>
	@else
		<font color="red">{{trans('messages.modem_no_diag')}}</font><br>
		{{ trans('messages.modem_monitoring_error') }}
	@endif

@stop

@section('content_ping')

	@if ($ping)
		<font color="green"><b>CMTS is Online</b></font><br>
		@foreach ($ping as $line)
				<table>
				<tr>
					<td>
						 <font color="grey">{{$line}}</font>
					</td>
				</tr>
				</table>
		@endforeach
	@else
		<font color="red">{{trans('messages.modem_offline')}}</font>
	@endif

@stop


@section('content_realtime')
	@if ($realtime)

		<font color="green"><b>{{$realtime['forecast']}}</b></font><br>

		@foreach ($realtime['measure'] as $tablename => $table)
			<h5>{{$tablename}}</h5>
			<table width="100%">
				@foreach ($table as $rowname => $row)
					<tr>
						<th width="120px">
							{{$rowname}}
						</th>

						@foreach ($row as $linename => $line)
							<td>
								 <font color="grey">{{htmlspecialchars($line)}}</font>
							</td>
						@endforeach
					</tr>
				@endforeach
			</table>
		@endforeach

	@else
		<font color="red">{{trans('messages.modem_offline')}}</font>
	@endif
@stop
