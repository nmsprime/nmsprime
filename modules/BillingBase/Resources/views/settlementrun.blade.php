@extends ('Layout.split')

@section('content_right')

	{{ Form::open(array('route' => ['SettlementRun.update', 1], 'method' => 'put')) }}
	{{ Form::submit('Run Accounting Command for current Month') }}
	{{ Form::close() }}



@stop
	@include ('bootstrap.panel', array ('content' => 'content_right',
							'view_header' => 'Files',
							'md' => 3))
