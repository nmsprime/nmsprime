@extends ('provmon::layouts.cpe_split')


@section('content_dash')
	@if ($dash)
		<font color="grey">{{$dash}}</font>
	@else
		<font color="green"><b>TODO</b></font>
	@endif
@stop

@section('content_lease')

	@if ($lease)
		<font color="green"><b>CPE has a valid lease</b></font><br>
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