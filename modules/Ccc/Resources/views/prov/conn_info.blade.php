{{-- Error Message --}}
@if (Session::has('download_error'))
	@DivOpen(12)
		<h5 style='color:red' id=''>{{ Session::get('download_error') }}</h5>
	@DivClose()
	{{ Session::forget('download_error') }}
@endif

<h3>
	{{HTML::linkRoute('Contract.ConnInfo', 'Download', [$view_var->id])}}
</h3>
