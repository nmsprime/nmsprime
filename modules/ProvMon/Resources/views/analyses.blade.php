@extends ('provmon::split')


@section('content_dash')
	@if ($dash)
		<font color="grey">{{$dash}}</font>
	@else
		<font color="green"><b>TODO</b></font>
	@endif
@stop

@section('content_cacti')

	@if ($monitoring)
		<form action="" method="GET">
			From:<input type="text" name="from" value={{$monitoring['from']}}>
			To:<input type="text" name="to" value={{$monitoring['to']}}>
			<input type="submit" value="Submit">
		</form>
		<br>

		@foreach ($monitoring['graphs'] as $id => $graph)
			<img width=100% src={{$graph}}></img>
			<br><br>
		@endforeach
	@else
		<font color="red">{{trans('messages.modem_no_diag')}}</font><br>
		{{ trans('messages.modem_monitoring_error') }}
	@endif

@stop

@section('content_ping')

	@if ($ping)
		<font color="green"><b>Modem is Online</b></font><br>
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


@section('content_flood_ping')

	<?php $route = \Route::getCurrentRoute()->getUri(); ?>

	<form route="$route" method="POST">Type:
		<input type="hidden" name="_token" value={{ csrf_token() }}>
		<select name="flood_ping">
			<option value="1">lowest load: 100 packets of 56 Byte</option>
			<option value="2">big load: 300 packets of 300 Byte</option>
			<option value="3">huge load: 500 packets of 1472 Byte</option>
			<option value="4">highest load: 1.000 packets of 56 Byte</option>
		</select>
		<input type="submit" value="Send Ping">
	</form>

	<!-- {{ Form::open(['route' => ['Provmon.flood_ping', $view_var->id]]) }} -->

	@if (isset($flood_ping))
		@foreach ($flood_ping as $line)
				<table>
				<tr>
					<td>
						 <font color="grey">{{$line}}</font>
					</td>
				</tr>
				</table>
		@endforeach
	@endif

@stop


@section('content_lease')

	@if ($lease)
		<font color="{{$lease['state']}}"><b>{{$lease['forecast']}}</b></font><br>
		@foreach ($lease['text'] as $line)
				<table>
				<tr>
					<td>
						 <font color="grey">{{$line}}</font>
					</td>
				</tr>
				</table>
		@endforeach
	@else
		<font color="red">{{ trans('messages.modem_lease_error')}}</font>
	@endif

@stop

@section('content_log')
	@if ($log)
		<font color="green"><b>Modem Logfile</b></font><br>
		@foreach ($log as $line)
				<table>
				<tr>
					<td>
						 <font color="grey">{{$line}}</font>
					</td>
				</tr>
				</table>
		@endforeach
	@else
		<font color="red">{{ trans('messages.modem_log_error') }}</font>
	@endif
@stop


@section('content_realtime')
	@if ($realtime)
	
	<font color="green"><b>{{$realtime['forecast']}}</b></font><br>

	@foreach ($realtime['measure'] as $tablename => $table)
		<h5>{{$tablename}}</h5>
		<table class="table datatable table-bordered table-striped responsive" width="100%">
			@if ($tablename == "Downstream" || $tablename == "Upstream"  )
			<tr>
				<th>
					#
				</th>
				@foreach ($table as $colheader => $colarray)
				<th width="120px">
					{{$colheader}}
				</th>
			@endforeach
			</tr>
				<?php $max = count(current($table)); ?>
				@for ($i = 0; $i < $max ; $i++)
					<tr>
						<td> {{ $i }}</td>
						@foreach ($table as $colheader => $colarray)
							<td> <font color="grey"> {{ htmlspecialchars( $colarray[$i] ) }} </font> </td>
						@endforeach
					</tr>
				@endfor
			@else
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
			@endif
		</table>
	@endforeach

	@else
		<font color="red">{{trans('messages.modem_offline')}}</font>
	@endif
@stop
