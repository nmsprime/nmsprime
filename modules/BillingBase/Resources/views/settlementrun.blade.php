@extends ('Layout.split')

@section('content_right')
	@if (isset($relation['view']['vars']))

		@section('files')

			@if ($rerun_button)
				{{ Form::open(array('route' => ['SettlementRun.update', $view_var->id], 'method' => 'put')) }}
					{{ Form::hidden('rerun', true) }}
					{{ Form::submit( \App\Http\Controllers\BaseViewController::translate_view('Rerun Accounting Command for current Month', 'Button') , ['style' => 'simple']) }}
				{{ Form::close() }}
			@endif

			<br>

			<?php $i=0; $header = 'axh3,'; ?>

			<table class="table">
				@foreach($relation['view']['vars'] as $key => $file)

					@if ($file->getRelativePath() != $header)

						<?php $header = $file->getRelativePath() ?>
						
						@if (!$header && !$i)
							<tr><td><b> {{ \App\Http\Controllers\BaseViewController::translate_label('General') }} </b></td></tr>
							<?php $i++ ?>
						@else
							<tr><td><b> {{ $header }} </b></td></tr>
						@endif

					@endif
					<tr><td> {{ HTML::linkRoute('Settlement.download', $file->getFilename(), ['id' => $view_var->id, 'key' => $key]) }} </td></tr>

				@endforeach
			<table>

		@stop

		@include ('bootstrap.panel', array ('content' => 'files', 'view_header' => 'Files', 'md' => 3))
	
	@endif
@stop
