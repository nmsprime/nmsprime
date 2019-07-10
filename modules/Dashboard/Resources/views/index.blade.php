@extends ('Layout.default')


@section('content')
	<div class="col-md-12">

		<h1 class="page-header">{{ $title }}</h1>
		@section ('dashboard_logs')
			@include('dashboard::timeline.logs')
		@stop
		@include ('bootstrap.panel', array ('content' => "dashboard_logs", 'view_header' => 'All updates', 'md' => 7, 'height' => 'auto', 'i' => '2'))
	</div>
@stop
