<!doctype html>
<html>

<head>
	<meta charset="utf-8">
	<title>{{$html_title}}</title>
	@include ('bootstrap.header')

</head>

<body <?php if(isset($body_onload)) echo "onload=$body_onload()";?> >

	<div id="page-container" class="fade page-sidebar-fixed page-header-fixed in">

	@include ('Layout.header')

	@include ('bootstrap.sidebar')

		<div id="content" class="content p-t-15">
			<div class="row">
				@yield ('content')
			</div>
		</div>
	</div>

@include ('bootstrap.footer')
@yield ('javascript')
@yield ('javascript_extra')

{{-- scroll to top btn --}}
<a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade p-l-5" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>

</body>

</html>
