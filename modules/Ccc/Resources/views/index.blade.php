@extends('ccc::layouts.master')

@section('content')

	<table class="table">
		@foreach($invoices as $file)
			<tr><td> {{ HTML::linkRoute('Customer.Download', $file->getFilename(), ['contract_id' => $contract_id, 'filename' => $file->getFilename()]) }} </td></tr>
		@endforeach
	</table>

@stop

