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
		<font color="red">No Diagrams available</font><br>
		This could be because the Modem was not online until now. Please note that Diagrams are only available
		from the point that a modem was online. If all diagrams did not show properly then it should be a
		bigger problem and there should be a cacti misconfiguration. Please consider the administrator on bigger problems.
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
		<font color="red">Modem is Offline</font>
	@endif

@stop

@section('content_lease')

	@if ($lease)
		<font color="green"><b>Modem has a valid lease</b></font><br>
		@foreach ($lease as $line)
				<table>
				<tr>
					<td> 
						 <font color="grey">{{$line}}</font>
					</td>
				</tr>
				</table>
		@endforeach
	@else
		<font color="red">No valid Lease found</font>
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
		<font color="red">Modem was not registering on Server - No log entry found</font>
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
		<font color="red">Modem is Offline</font>
	@endif
@stop