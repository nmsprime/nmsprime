<!DOCTYPE html>

<html lang="en">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Module Ccc</title>
		@include ('bootstrap.header')
	</head>

	<body <?php if(isset($body_onload)) echo "onload=$body_onload()";?> >
		<div id="page-container" class="fade page-sidebar-fixed page-header-fixed in">
			<div id="content" class="content">

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

				@include ('bootstrap.panel', array ('content' => 'content', 'invoices' => $invoices, 'view_header' => trans('messages.Invoices'), 'md' => 4))

				@include ('bootstrap.footer')

			</div>
		</div>
	</body>

</html>