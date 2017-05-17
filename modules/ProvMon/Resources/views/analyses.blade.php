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
	<?php $realtime=array(
		"measure" => [ "System" =>
					[ "SysDescr"	=> [0 => "Thomson PacketCable E-MTA <<HW_REV: 1.0.; VENDOR: Thomson; BOOTR: 2.1.7dm; SW_REV: ST8B.01.45; MODEL: THG540>>"],
      				"Firmware"		=> [0 => "ST8B.01.45"],
      				"Uptime"		=> [0 => "116 Days 1 Hours 24 Min 13 Sec"],
      				"DOCSIS"		=> [0 => "DOCSIS 2.0"]
      				],
      			"Downstream" =>
      				["Frequency MHz" => [0 => 338, 			1 => 339, 		2 => 340],
      				"Modulation" 	=> [0 => "QAM256", 		1 => "QAM256", 	2 => "QAM256"],
      				"Power dBmV"	=> [0 => 5.90, 			1 => 5.9, 		2 => 5.9 ],
      				"MER dB" 		=> [0 => 39.8, 			1 => 39.8, 		2 => 40.9 ],
      				"Microreflection -dBc" =>  [0 => "30", 	1 => "30", 		2 => "30"],
      				"Operational CHs %" => [0 => 100, 		1 => 100, 		2 => 100]
    				],
  				"Upstream" =>
  					["Frequency MHz" => [0 => 25.4, 			1 => 25.6 ],
  					"Power dBmV" 	=> [0 => 35.2, 				1 => 35.1 ],
      				"Width MHz" 	=> [0 => 6.4, 				1 => 6.5 ],
      				"Modulation Profile" => [0 => "0",			1 => "0"],
      				"SNR dB" 		=> [0 => 36.1,				1 => 30.1],
      				"Operational CHs %" => [0 => 100,			1 => 100]
    				],
    			"CMTS" =>
    				[ "Hostname" 	=> [0 => "dev-cable-gw01"]
    				]
  				],
  		"forecast" => "TODO"
	); ?>

	@foreach ($realtime['measure'] as $tablename => $table)
		<h4>{{$tablename}}</h4>
			@if ($tablename == "Downstream" || $tablename == "Upstream"  )
			<div class="table-responsive">
				<table class="table streamtable table-bordered table-hover" width="100%">
					<thead>
						<tr class="active">
							<th> </th>
							<th>#</th>
							@foreach ($table as $colheader => $colarray)
								@if ($colheader != "Operational CHs %")
								<th>{{$colheader}}</th>
								@endif
							@endforeach
						</tr>
					</thead>
					<tbody>
						<?php $max = count(current($table)); ?>
						@for ($i = 0; $i < $max ; $i++)
						<tr>
							<td width="20"></td>
							<td width="20"> {{ $i+1 }}</td>
							@foreach ($table as $colheader => $colarray)
								@if ($colheader != "Operational CHs %")
								<td align="center"> <font color="grey"> {{ htmlspecialchars( $colarray[$i] ) }} </font> </td>
								@endif
							@endforeach
						</tr>
						@endfor
					</tbody>
				</table>
			</div>
			@else
			<table class="table">
			@foreach ($table as $rowname => $row)
				<tr>
					<th width="15%">
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
			@endif
	@endforeach
@stop
