@extends ('provmon::layouts.cpe_mta_split')


@section('content_dash')
	@if ($dash)
		<font color="grey">{{$dash}}</font>
	@else
		<font color="green"><b>TODO</b></font>
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
		<font color="green"><b>{{$type}} Logfile</b></font><br>
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
		<font color="red">{{$type.' '.trans('messages.cpe_log_error')}}</font>
	@endif
@stop


@section('content_ping')

	@if ($ping)
		<font color="green"><b>{{$type}} is Online</b></font><br>
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
		<font color="red">{{$type}} is Offline</font> <br>
		<font color="grey">{{$ping[5]}}</font>
	@endif

@stop