@extends ('provmon::layouts.cpe_mta_split')


@section('content_dash')
	<div class="btn pull-right">
		@include('Generic.documentation', ['documentation' => $modem->help])
	</div>

	@if ($dash)
		<font color="grey">{!!$dash!!}</font>
	@else
		<font color="green"><b>TODO</b></font>
	@endif
@stop

@section('content_lease')

	@if ($lease)
		<font color="{{$lease['state']}}"><b>{!!$lease['forecast']!!}</b></font><br>
		@foreach ($lease['text'] as $line)
			<table>
				<tr>
					<td>
						 <font color="grey">{!!$line!!}</font>
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
		<font color="green"><b>{{$type}} Logs</b></font><br>
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

@section('content_configfile')
	@if (isset($configfile))
		<font color="green"><b>{{$type}} Configfile ({{$configfile['mtime']}})</b></font><br>
		@if (isset($configfile['warn']))
			<font color="red"><b>{{$configfile['warn']}}</b></font><br>
		@endif
		@foreach ($configfile['text'] as $line)
			<table>
				<tr>
					<td>
					 <font color="grey">{{$line}}</font>
					</td>
				</tr>
			</table>
		@endforeach
	@else
		<font color="red">{{ trans('messages.mta_configfile_error')}}</font>
	@endif
@stop

@section('content_ping')

	@if ($ping)
		<?php
			$color = isset($ping[1]) ? "success" : "warning";
			$text  = isset($ping[1]) ? "$type is Online" : trans('messages.device_probably_online', ['type' => $type]);
		?>
		<font color="{{$color}}"><b>{{$text}}</b></font><br>
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

@section('javascript')
{
	@include('Generic.handlePanel')
}
