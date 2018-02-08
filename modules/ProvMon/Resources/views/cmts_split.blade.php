@extends ('Layout.split-nopanel')

@section('content_top')

	@include ('provmon::layouts.top')

@stop


@section ('content_left')

<div class="row">

	{{-- We need to include sections dynamically: always content left and if needed content right - more than 1 time possible --}}
	<div class="col-md-7 ui-sortable">
		@include ('bootstrap.panel-no-div', array ('content' => 'content_dash', 'view_header' => 'Dashboard / Forecast', 'md' => 8, 'i' => 1))
		@include ('bootstrap.panel-no-div', array ('content' => 'content_realtime', 'view_header' => \App\Http\Controllers\BaseViewController::translate_label('Real Time Values'), 'md' => 8, 'i' => 2))
	</div>
	<div class="col-md-5 ui-sortable">
		@include ('bootstrap.panel-no-div', array ('content' => 'content_cacti', 'view_header' => 'Monitoring', 'md' => 8, 'i' => 3))
		@include ('bootstrap.panel-no-div', array ('content' => 'content_ping', 'view_header' => 'Ping Test', 'md' => 4, 'i' => 4))
	</div>

</div>

@stop
