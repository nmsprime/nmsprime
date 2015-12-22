@extends ('Layout.default')

@section('content_top')

	@include ('provmon::layouts.top', ['type' => 'CPE'])

@stop

@section ('content')

<div class="row col-md-12">

	{{-- We need to include sections dynamically: always content left and if needed content right - more than 1 time possible --}}

	<div class="col-md-8 ui-sortable">
		@include ('bootstrap.panel-no-div', array ('content' => 'content_dash', 'view_header' => 'Dashboard / Forecast', 'md' => 8))
		@include ('bootstrap.panel-no-div', array ('content' => 'content_log', 'view_header' => 'Logfile', 'md' => 8))
	</div>

	<div class="col-md-4 ui-sortable">
		@include ('bootstrap.panel-no-div', array ('content' => 'content_lease', 'view_header' => 'DHCP Log', 'md' => 4))	
	</div>


</div>

@stop