@extends('ccc::layouts.master')

@section('content')

<div id="page-container" class="fade page-sidebar-fixed page-header-fixed in">

	<div id="header" class="header navbar navbar-default navbar-fixed-top">
		<div class="container-fluid">
			<div class="navbar-header">
				<h1 id="item_name">{{ trans('messages.ccc') }}</h1>
			</div>
			<div id="header-navbar" class="collapse navbar-collapse">
				<ul class="nav navbar-nav navbar-right">
					<li><a class="btn btn-theme" data-click="scroll-to-target" href="{{route('CustomerAuth.logout')}}">{{trans('messages.log_out')}}</a></li>
				</ul>
			</div>
		</div>
	</div>


	<div id="sidebar" class="sidebar">
	</div>


	<div id="content" class="content">
		<h2 class="content-title">{{ trans('messages.Invoices') }}</h2>
		<div class="row">
			<div class="col-md-2 col-sm-4">
				<table class="table">
					@foreach($invoices as $file)
						<tr><td> {{ HTML::linkRoute('Customer.Download', $file->getFilename(), ['contract_id' => $contract_id, 'filename' => $file->getFilename()]) }} </td></tr>
					@endforeach
				</table>
			</div>
		</div>
	</div>

</div>
@stop

