@extends ('Layout.default')

@section ('tickets')
	<h4>{{ \App\Http\Controllers\BaseViewController::translate_view('Tickets', 'Dashboard') }}</h4>
	<p>
		@if ($tickets['total'])
			{{ $tickets['total'] }}
		@else
			{{ \App\Http\Controllers\BaseViewController::translate_view('NoTickets', 'Dashboard') }}
		@endif
	</p>
@stop

@section ('date')
	<h4>{{ \App\Http\Controllers\BaseViewController::translate_view('Date', 'Dashboard') }}</h4>
	<p>{{ date('d.m.Y') }}</p>
@stop

@section('content')

	<div class="col-md-12">

		<h1 class="page-header">{{ $title }}</h1>

		{{--Quickstart--}}

		<div class="row">
			<div class="col-md-12">
				@include('Generic.quickstart')
			</div>
		</div>

		<div class="row">
			@if ($tickets && $tickets['total'])
			@section ('ticket_table')
				@include('ticketsystem::panels.ticket_table')
			@stop
			@include ('bootstrap.panel', array ('content' => "ticket_table", 'view_header' => trans('messages.dashbrd_ticket'), 'md' => 7, 'height' => 'auto', 'i' => '5'))
			@endif

			<div class="col-md-5">
				<div class="row">
					@DivOpen(6)
					@include ('bootstrap.widget',
						array (
							'content' => 'tickets',
							'widget_icon' => 'ticket',
							'widget_bg_color' => 'orange',
							'link_target' => '#anchor-tickets',
						)
					)
					@DivClose()
					@DivOpen(6)
					@include ('bootstrap.widget',
                        array (
                            'content' => 'date',
                            'widget_icon' => 'calendar',
                            'widget_bg_color' => 'purple',
                        )
                    )
					@DivClose()
				</div>
				<div class="row">
					<div class="col-md-12">
						@include('ticketsystem::widgets.documentation')
					</div>
				</div>
			</div>
		</div>
	</div>
	
@stop
