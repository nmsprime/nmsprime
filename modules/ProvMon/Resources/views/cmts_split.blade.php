@extends ('Layout.default')

@section('content_top')

	@include ('provmon::layouts.top')

@stop


@section ('content')

<div class="row col-md-12">

	{{-- We need to include sections dynamically: always content left and if needed content right - more than 1 time possible --}}

	@include ('bootstrap.panel-no-div', array ('content' => 'content_dash', 'view_header' => 'Dashboard / Forecast', 'md' => 12))
	@include ('bootstrap.panel-no-div', array ('content' => 'content_realtime', 'view_header' => \App\Http\Controllers\BaseViewController::translate_label('Real Time Values'), 'md' => 12))
	@include ('bootstrap.panel-no-div', array ('content' => 'content_cacti', 'view_header' => 'Monitoring', 'md' => 12))
	@include ('bootstrap.panel-no-div', array ('content' => 'content_ping', 'view_header' => 'Ping Test', 'md' => 12))

</div>

@stop
