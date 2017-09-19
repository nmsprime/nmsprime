@extends ('Layout.default')

@section('content_top')

	@include ('provmon::layouts.top', ['type' => $type])

@stop

@section ('content')

<div class="row col-md-12">

	{{-- We need to include sections dynamically: always content left and if needed content right - more than 1 time possible --}}

	<div class="col-md-7 ui-sortable">
		@include ('bootstrap.panel-no-div', array ('content' => 'content_dash', 'view_header' => 'Dashboard / Forecast', 'md' => 8))
		@include ('bootstrap.panel-no-div', array ('content' => 'content_log', 'view_header' => 'DHCP Log', 'md' => 8))
	</div>

	<div class="col-md-5 ui-sortable">
		@include ('bootstrap.panel-no-div', array ('content' => 'content_ping', 'view_header' => 'Ping Test', 'md' => 4))
		@include ('bootstrap.panel-no-div', array ('content' => 'content_lease', 'view_header' => 'DHCP Lease', 'md' => 4))
		@if (isset($configfile))
			@include ('bootstrap.panel-no-div', array ('content' => 'content_configfile', 'view_header' => 'Configfile', 'md' => 4))
		@endif
</div>


</div>

@stop
