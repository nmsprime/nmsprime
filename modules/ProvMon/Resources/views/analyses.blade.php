@extends ('provmon::split')


@section('content_dash')
	@if ($dash)
		<font color="grey">{{$dash}}</font>
	@else
		<b>TODO</b>
	@endif
@stop

@section('content_cacti')

	@if ($host_id)
		<iframe id="cacti-diagram" src="/cacti/graph_view.php?action=preview&columns=2&host_id={{$host_id}}" sandbox="allow-scripts allow-same-origin" width="100%" height="100%" onload="resizeIframe(this)" scrolling="no" style="min-height: 100%; border: none;"></iframe>
	@else
		<font color="red">{{trans('messages.modem_no_diag')}}</font><br>
		{{ trans('messages.modem_monitoring_error') }}
	@endif
	@include('provmon::cacti-height')
@stop

@section('content_ping')
	<div class="tab-content">
		<div class="tab-pane fade in" id="ping-test">
			@if ($online)
				<font color="green"><b>Modem is Online</b></font><br>
			@else
				<font color="red">{{trans('messages.modem_offline')}}</font>
			@endif
			{{-- pings are appended dynamically here by javascript --}}
		</div>

		<div class="tab-pane fade in" id="flood-ping">
			<?php $route = \Route::getCurrentRoute()->getUri(); ?>
					<form route="$route" method="POST">Type:
						<input type="hidden" name="_token" value="{{ csrf_token() }}"></input>
						<select class="select2 form-control m-b-20" name="flood_ping" style="width : 100 %">
							<option value="1">low load: 500 packets of 56 Byte</option> {{-- needs approximately 5 sec --}}
							<option value="2">average load: 1000 packets of 736 Byte</option> {{-- needs approximately 10 sec --}}
							<option value="3">big load: 2500 packets of 56 Byte</option> {{-- needs approximately 30 sec --}}
							<option value="4">huge load: 2500 packets of 1472 Byte</option> {{-- needs approximately 30 sec --}}
						</select>

				{{-- Form::open(['route' => ['ProvMon.flood_ping', $view_var->id]]) --}}
				@if (isset($flood_ping))
					@foreach ($flood_ping as $line)
							<table class="m-t-20">
							<tr>
								<td>
									 <font color="grey">{{$line}}</font>
								</td>
							</tr>
							</table>
					@endforeach
				@endif
					<div class="text-center">
						<button class="btn btn-primary m-t-10" type="submit">Send Ping</button>
					</div>
					</form>
		</div>
	</div>
@stop

@section('content_log')
<div class="tab-content">
	<div class="tab-pane fade in" id="log">
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
	</div>
	<div class="tab-pane fade in" id="lease">
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
	</div>
	<div class="tab-pane fade in" id="configfile">
		@if ($configfile)
			<font color="green"><b>Modem Configfile</b></font><br>
			@foreach ($configfile as $line)
				<table>
					<tr>
						<td>
						 <font color="grey">{{$line}}</font>
						</td>
					</tr>
				</table>
			@endforeach
		@else
			<font color="red">{{ trans('messages.modem_configfile_error')}}</font>
		@endif
	</div>

	<div class="tab-pane fade in" id="eventlog">
		@if ($eventlog)
			<div class="table-responsive">
				<table class="table streamtable table-bordered" width="100%">
					<thead>
						<tr class='active'>
							<th width="20px"></th>
							@foreach (array_shift($eventlog) as $col_name)
								<th class='text-center'>{{$col_name}}</th>
							@endforeach
						</tr>
					</thead>
					<tbody>
					@foreach ($eventlog as $row)
						<tr class = "{{$row[2]}}">
							<td></td>
							@foreach ($row as $idx => $data)
								@if($idx != 2)
									<td><font>{{$data}}</font></td>
								@endif
							@endforeach
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
		@else
			<font color="red">{{ trans('messages.modem_eventlog_error')}}</font>
		@endif
	</div>
</div>

@stop

@if (\PPModule::is_active('HfcCustomer'))
	@section('content_proximity_search')

		{{ Form::open(array('route' => 'CustomerTopo.show_prox', 'method' => 'GET')) }}
		{{ Form::label('radius', 'Radius / m', ['class' => 'col-md-2 control-label']) }}
		{{ Form::hidden('id', $modem->id); }}
		{{ Form::number('radius', '1000') }}
		<input type="submit" value="Search...">
		{{ Form::close() }}

	@stop
@endif


@section('content_realtime')
	@if ($realtime)
		<font color="green"><b>{{$realtime['forecast']}}</b></font><br>
		@foreach ($realtime['measure'] as $tablename => $table)
		<h4>{{$tablename}}</h4>
			@if ($tablename == "Downstream" || $tablename == "Upstream" )
			<div class="table-responsive">
				<table class="table streamtable table-bordered" width="100%">
					<thead>
						<tr class="active">
							<th> </th>
							<th>#</th>
							@foreach ($table as $colheader => $colarray)
								@if ($colheader == "Modulation Profile")
									<th class="text-center">Modulation</th>
								@endif
								@if ($colheader != "Operational CHs %" && $colheader != "Modulation Profile")
									<th class="text-center">{{$colheader}}</th>
								@endif
							@endforeach
						</tr>
					</thead>
					<tbody>
						<?php $max = count(current($table)); ?>
						@foreach(current($table) as $i => $dummy)
						<tr>
							<td width="20"> </td>
							<td width="20"> {{ $i }}</td>
							@foreach ($table as $colheader => $colarray)
								@if ($colheader != "Operational CHs %")
									<?php
										if(!isset($colarray[$i]))
											continue;
											$mod = ($tablename == "Downstream") ? $mod = "Modulation" :	$mod = "SNR dB";
										if(!isset($table[$mod][$i]))
										    continue;
										switch ( \App\Http\Controllers\BaseViewController::get_quality_color(Str::lower($tablename), '64qam' ,Str::lower($colheader),htmlspecialchars($colarray[$i])) ){
										case 0:
												$color = "success";
												break;
										case 1:
												$color = "warning";
												break;
										case 2:
												$color = "danger";
												break;
										default:
												$color = "";
										}
									?>
									<td class="text-center {{ $color }}"> <font color="grey"> {{ $colarray[$i] }} </font> </td>

								@endif
							@endforeach
						</tr>
						@endforeach
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
	@else
		<font color="red">{{trans('messages.modem_offline')}}</font>
	@endif
@stop


@section ('javascript')

<script type="text/javascript">

@if ($ip)

	$(document).ready(function() {

		setTimeout(function() {

			var source = new EventSource(" {{ route('ProvMon.realtime_ping', $ip) }}");

			source.onmessage = function(e) {
				// close connection
				if (e.data == 'finished')
				{
					source.close();
					return;
				}

				document.getElementById('ping-test').innerHTML += e.data;
			}

		}, 500);
	});
@endif
</script>

<script language="javascript">
	$(document).ready(function() {
		$('table.streamtable').DataTable(
		{
		{{-- Translate Datatables Base --}}
			@include('datatables.lang')
		responsive: {
			details: {
				type: 'column' {{-- auto resize the Table to fit the viewing device --}}
			}
		},
		autoWidth: false,
		paging: false,
		info: false,
		searching: false,
		aoColumnDefs: [ {
			className: 'control',
			orderable: false,
			targets:   [0]
		} ]
		});
});
</script>
@include('Generic.handlePanel')
@stop
