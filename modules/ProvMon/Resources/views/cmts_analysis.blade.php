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
		<h4>{{$tablename}}</h4>
			@if ($tablename == "Downstream" || $tablename == "Upstream" )
			<div class="table-responsive">
				<table class="table streamtable table-bordered" width="100%">
					<thead>
						<tr class="active">
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
