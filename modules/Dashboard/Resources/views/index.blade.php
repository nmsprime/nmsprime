@extends ('Layout.default')


@section('content')
	<div class="col-md-12">

		<h1 class="page-header">{{ $title }}</h1>
		@include('dashboard::timeline.logs', array('content'=>'logs'))
	</div>
@stop
