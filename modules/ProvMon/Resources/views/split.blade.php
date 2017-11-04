@extends ('Layout.default')

@section('content_top')

	@include ('provmon::layouts.top')

@stop


@section ('content')

<div class="row col-md-12">

	{{-- We need to include sections dynamically: always content left and if needed content right - more than 1 time possible --}}

	<div class="col-md-7 ui-sortable">
		@include ('bootstrap.panel-no-div', array ('content' => 'content_dash', 'view_header' => 'Dashboard', 'md' => 8))
		@include ('bootstrap.panel-no-div', array ('content' => 'content_realtime', 'view_header' => \App\Http\Controllers\BaseViewController::translate_label('Real Time Values'), 'md' => 8))
		@include ('bootstrap.panel-no-div', array ('content' => 'content_cacti', 'view_header' => 'Monitoring', 'md' => 8))
	</div>

	<div class="col-md-5 ui-sortable">

		@include ('bootstrap.panel-no-div', array ('content' => 'content_ping', 'view_header' => '<ul class="nav nav-pills" id="ping-tab">
						<li role="presentation"><a href="#ping-test" data-toggle="pill">Default Ping</a></li>
						<li role="presentation"><a href="#flood-ping" data-toggle="pill">Flood-Ping</a></li>
					</ul>', 'md' => 4))
		@include ('bootstrap.panel-no-div', array ('content' => 'content_log', 'view_header' => '<ul class="nav nav-pills" id="loglease">
						<li role="presentation"><a href="#log" data-toggle="pill">Log</a></li>
						<li role="presentation"><a href="#lease" data-toggle="pill">Lease</a></li>
						<li role="presentation"><a href="#configfile" data-toggle="pill">Configfile</a></li>
						<li role="presentation"><a href="#eventlog" data-toggle="pill">Eventlog</a></li>
					</ul>', 'md' => 4))
		@if (\PPModule::is_active('HfcCustomer'))
			@include ('bootstrap.panel-no-div', array ('content' => 'content_proximity_search', 'view_header' => 'Proximity Search', 'md' => 4))
		@endif
	</div>

</div>

@stop
