<style>
	a:hover {
		text-decoration: none;
	}
</style>

<div class="widget widget-stats bg-grey">
	{{-- info/data --}}
	<div class="stats-info d-flex">
		<div class="btn btn-dark m-5 m-r-10">
			{!! HTML::decode (HTML::linkRoute('Contract.create',
				'<h3><div class="text-center"><i style="color: white;" class="img-center fa fa-address-book-o"></i></div></h3>
				<div style="color: white;" class="username text-ellipsis text-center">'))
			!!}{{ trans('view.Dashboard_AddContract') }}</div>
		</div>
		<div class="btn btn-dark m-5 m-r-10 m-l-10">
			{!! HTML::decode (HTML::linkRoute('Ticket.create',
				'<h3><div class="text-center" style="color: white;"><i class="img-center fa fa-ticket"></i></div></h3>
				<div style="color: white;" class="username text-ellipsis text-center">'))
			!!}{{ trans('view.Dashboard_AddTicket') }}</div>
		</div>
		<div class="btn btn-dark m-5 m-l-10">
			{!! HTML::decode (HTML::linkRoute('Modem.firmware',
				'<h3><div class="text-center" style="color: white;"><i class="img-center fa fa-file-code-o"></i></div></h3>
				<div style="color: white;" class="username text-ellipsis text-center">Firmwares</div>'))
			!!}
		</div>
		<div class="btn btn-dark m-5 m-l-10">
			{!! HTML::decode (HTML::linkRoute('CustomerTopo.show_impaired',
				'<h3><div class="text-center" style="color: white;"><i class="img-center fa fa-hdd-o text-danger"></i></div></h3>
				<div style="color: white;" class="username text-ellipsis text-center">'))
			!!}{{ trans('view.Dashboard_ImpairedModem') }}</div>
		</div>
    </div>
    {{-- reference link --}}
	<div class="stats-link"><a href="#">{{ trans('view.Dashboard_Quickstart') }}</a></div>
</div>

