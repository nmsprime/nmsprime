@extends ('Layout.split-nopanel')

<head>

	<link href="{{asset('/modules/hfcbase/alert.css')}}" rel="stylesheet" type="text/css" media="screen"/>
	<script type="text/javascript" src="{{asset('/modules/hfcbase/alert.js')}}"></script>

	<script src="{{asset('/modules/hfcbase/OpenLayers-2.13.1/OpenLayers.js')}}"></script>
	<script src="https://maps.google.com/maps/api/js?v=3.2&sensor=false"></script>

	@include ('hfcbase::Tree.topo-api')

</head>



@section('content_top')
	<li><a href="">{{ trans("view.Header_Topography - Modems") }} </a></li>
@stop

@section('content_left')
	<div class="row">
		<div data-toggle="buttons">
			<label class="btn btn-primary active m-5">
				<input type="radio" name="options" value="none" id="noneToggle" onchange="toggleControl(this);" autocomplete="off" checked/>
				{{ trans("view.navigate") }}
			</label>
			<label class="btn btn-primary m-5">
				<input type="radio" name="options" value="box" id="boxToggle" onchange="toggleControl(this);" autocomplete="off"/>
				{{ trans("view.draw box") }}
			</label>
			<label class="btn btn-primary m-5">
				<input type="radio" name="options" value="polygon" id="polygonToggle" onchange="toggleControl(this);" autocomplete="off"/>
				{{ trans("view.draw polygon") }}
			</label>
			<label class="btn btn-primary m-5">
				<input type="radio" name="options" value="modify" id="modifyToggle" onchange="toggleControl(this);" autocomplete="off" />
				{{ trans("view.modify") }}
			</label>
		</div>
		<ul class="nav nav-pills d-flex ml-auto">
		<?php
			$par = Route::getCurrentRoute()->parameters();
			$cur_row = \Input::has('row') ? \Input::get('row') : 'us_pwr';
			foreach (['us_pwr' => 'US Power', 'us_snr' => 'US SNR', 'ds_pwr' => 'DS Power', 'ds_snr' => 'DS SNR'] as $key => $val) {
				$par['row'] = $key;
				$class = ($cur_row === $key) ? 'active' : '';
				echo("<li role=\"presentation\" class=\"$class\">".link_to_route(Route::getCurrentRoute()->getName(), $val, $par).'</li>');
			}
		?>
		</ul>
	</div>
	<div class="row m-t-20">
		<div class="col-md-12 d-flex" id="map" style="height:75vh"></div>
	</div>
@stop

