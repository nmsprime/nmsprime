@if (isset($relation['view']['vars']))

	@if ($finished)

		@DivOpen(3)
		@DivClose()

		@if ($rerun_button)
			{{ Form::open(array('route' => ['SettlementRun.update', $view_var->id], 'method' => 'put')) }}
				{{ Form::hidden('rerun', true) }}
				{{ Form::submit( \App\Http\Controllers\BaseViewController::translate_view('Rerun Accounting Command for current Month', 'Button') , ['style' => 'simple']) }}
			{{ Form::close() }}
		@endif

		<br>

		@foreach($relation['view']['vars'] as $key => $files)
			@DivOpen(6)
				<table class="table table-bordered">
				<th class="text-center active"> {{ $key }} </th>
				@foreach ($files as $file)
					<tr><td class="text-center">{{ HTML::linkRoute('Settlement.download', $file->getFilename(), ['id' => $view_var->id, 'key' => $key]) }}</td></tr>
				@endforeach
				</table>
			@DivClose()
		@endforeach

	@else
		{{-- accountingCommand still running --}}
		<div class="alert alert-warning fade in m-b-15">{{ trans('messages.accCmd_processing') }}</div>

		{{-- Simply refresh every 10 sec - TODO: Replace by Events --}}
		<META HTTP-EQUIV="refresh" CONTENT="10">
	@endif

@endif


