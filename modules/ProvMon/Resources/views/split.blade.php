@extends ('Layout.split-nopanel')

@section('content_top')

	@include ('provmon::layouts.top')

@stop


@section ('content_left')

<div class="row">

	{{-- We need to include sections dynamically: always content left and if needed content right - more than 1 time possible --}}

	<div class="col-md-7 ui-sortable">
		@include ('bootstrap.panel-no-div', array ('content' => 'content_dash', 'view_header' => 'Dashboard', 'md' => 8, 'i' => 1))
		@include ('bootstrap.panel-no-div', array ('content' => 'content_realtime', 'view_header' => \App\Http\Controllers\BaseViewController::translate_label('Real Time Values'), 'md' => 8, 'i' => 2))
	</div>

	<div class="col-md-5 ui-sortable">

		@include ('bootstrap.panel-no-div', array ('content' => 'content_ping', 'view_header' => '<ul class="nav nav-pills" id="ping-tab">
						<li role="presentation"><a href="#ping-test" data-toggle="pill">Default Ping</a></li>
						<li role="presentation"><a href="#flood-ping" data-toggle="pill">Flood-Ping</a></li>
					</ul>', 'md' => 4, 'i' => 4))
		@include ('bootstrap.panel-no-div', array ('content' => 'content_log', 'view_header' => '<ul class="nav nav-pills" id="loglease">
						<li role="presentation"><a href="#log" data-toggle="pill">Log</a></li>
						<li role="presentation"><a href="#lease" data-toggle="pill">Lease</a></li>
						<li role="presentation"><a href="#configfile" data-toggle="pill">Configfile</a></li>
						<li role="presentation"><a href="#eventlog" data-toggle="pill">Eventlog</a></li>
					</ul>', 'md' => 4, 'i' => 5))
		@if (\Module::collections()->has('HfcCustomer'))
			@include ('bootstrap.panel-no-div', array ('content' => 'content_proximity_search', 'view_header' => 'Proximity Search', 'md' => 4, 'i' => 6))
		@endif
	</div>

	<div class="col-md-12 ui-sortable">
		@include ('bootstrap.panel-no-div', array ('content' => 'content_cacti', 'view_header' => 'Monitoring', 'md' => 12, 'i' => 3))
	</div>

</div>

@stop
