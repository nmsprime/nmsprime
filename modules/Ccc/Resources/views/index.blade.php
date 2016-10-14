@extends('ccc::layouts.master')

@section('content_left')

	<table class="table">
		@foreach($invoices as $file)
			<tr><td> {{ HTML::linkRoute('Customer.Download', $file->getFilename(), ['contract_id' => $contract_id, 'filename' => $file->getFilename()]) }} </td></tr>
		@endforeach
	</table>

@stop

@section('content')

	@include ('bootstrap.panel', array ('content' => 'content_left', 'invoices' => $invoices, 'view_header' => trans('messages.Invoices'), 'md' => 4))

@stop
