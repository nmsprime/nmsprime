@extends('ccc::layouts.master')

@section('content')

	<h1>Customer Control Center</h1>

	<p>
		This view is loaded from module: {{ config('ccc.name') }}
	</p>

	<a href="{{route('CccAuth.logout')}}">{{trans('messages.log_out')}}</a>

	<br><br>For a first step a simple foreach statement over all invoices and CDRs will be fine :)

@stop