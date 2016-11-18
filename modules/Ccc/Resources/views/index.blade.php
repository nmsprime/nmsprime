@extends('ccc::layouts.master')

@section('content_left')

	<table class="table">
		@foreach($invoices as $key => $invoice)
			<?php $link = HTML::linkRoute('Customer.Download', $invoice->filename, ['invoice' => $invoice->id]); ?>
			@if ($key % 2)
				<td> {{ $link }} </td></tr>
			@else
				<tr><td> {{ $link }} </td>
			@endif
		@endforeach
	</table>

@stop

@section('content')

	@include ('bootstrap.panel', array ('content' => 'content_left', 'invoices' => $invoices, 'view_header' => trans('messages.Invoices'), 'md' => 4))

@stop
