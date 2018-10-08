@extends ('Layout.default')


<!-- Widgets -->
@foreach ($view as $content => $bool)
	@section ($content)
		@if ($bool && \View::exists('dashboard::widgets.'.$content))
			@include('dashboard::widgets.'.$content)
		@endif
	@stop
@endforeach


@section('content')
	<div class="col-md-12">

		<h1 class="page-header">{{ $title }}</h1>

		<!-- Widgets -->
		<div class="row">
			@if ($view['contracts'])
				@DivOpen(3)
					@include ('bootstrap.widget', array (
							'content' => 'contracts',
							'widget_icon' => 'users',
							'widget_bg_color' => 'green',
							'link_target' => '#anchor-contracts',
						)
					)
				@DivClose()
			@endif

			@if ($view['income'])
				@DivOpen(3)
					@include ('bootstrap.widget',
						array (
							'content' => 'income',
							'widget_icon' => 'euro',
							'widget_bg_color' => 'blue',
							'link_target' => '#anchor-income',
						)
					)
				@DivClose()
			@endif

			@if ($view['provvoipenvia'])
				<!-- placeholder -->
				@DivOpen(3)
					@include ('bootstrap.widget',
						array (
							'content' => 'provvoipenvia',
							'widget_icon' => 'info',
							'widget_bg_color' => 'aqua',
							'link_target' => '#anchor-provvoipenvia',
						)
					)
				@DivClose()
			@endif

			@if ($view['tickets'])
				@DivOpen(3)
					@include ('bootstrap.widget',
						array (
							'content' => 'tickets',
							'widget_icon' => 'ticket',
							'widget_bg_color' => 'orange',
							'link_target' => '#anchor-tickets',
						)
					)
				@DivClose()
			@endif

			@if ($view['date'])
				@DivOpen(3)
					@include ('bootstrap.widget',
						array (
							'content' => 'date',
							'widget_icon' => 'calendar',
							'widget_bg_color' => 'purple',
						)
					)
				@DivClose()
			@endif
		</div>

		<!-- Quickstart -->
		<div class="row">
			<div class="col-auto-md">
				@include('dashboard::widgets.quickstart')
			</div>

			@if ($view['hfc'])
				@DivOpen(3)
					@include('dashboard::widgets.hfc')
				@DivClose()
			@endif

			<div class="col-auto-md">
				@include('dashboard::widgets.documentation')
			</div>
		</div>


		<!-- Panels -->
		<div class="row">
			@if($netelements)
				@section ('impaired_netelements')
					@include('dashboard::panels.impaired_netelements')
				@stop
				@include ('bootstrap.panel', array ('content' => "impaired_netelements", 'view_header' => 'Impaired Netelements', 'md' => 6, 'height' => 'auto', 'i' => '1'))
			@endif

			@if($services)
				@section ('impaired_services')
					@include('dashboard::panels.impaired_services')
				@stop
				@include ('bootstrap.panel', array ('content' => "impaired_services", 'view_header' => 'Impaired Services', 'md' => 6, 'height' => 'auto', 'i' => '2'))
			@endif

			@if ($view['contracts'])
				@section ('contract_analytics')
					@include('dashboard::panels.contract_analytics')
				@stop
				@include ('bootstrap.panel', array ('content' => "contract_analytics", 'view_header' => 'Contract Analytics', 'md' => 8, 'height' => 'auto', 'i' => '3'))
			@endif

			@if ($view['contracts'] && $data['contracts']['table'])
				@section ('weekly_contracts')
					@include('dashboard::panels.weekly_contracts')
				@stop
				@include ('bootstrap.panel', array ('content' => "weekly_contracts", 'view_header' => 'Weekly Customers', 'md' => 4, 'height' => 'auto', 'i' => '1'))
			@endif

			@if ($view['income'])
				@section ('income_analytics')
					@include('dashboard::panels.income_analytics')
				@stop
				@include ('bootstrap.panel', array ('content' => "income_analytics", 'view_header' => 'Income Details', 'md' => 4, 'height' => 'auto', 'i' => '4'))
			@endif

			@if (isset($data['news']) && $data['news'])
				@section ('news')
					@include('dashboard::panels.news')
				@stop
				@include ('bootstrap.panel', array ('content' => "news", 'view_header' => 'News', 'md' => 4, 'height' => '350px', 'i' => '5'))
			@endif

			@if ($view['tickets'] && $data['tickets']['total'])
				@section ('ticket_table')
					@include('dashboard::panels.ticket_table')
				@stop
				@include ('bootstrap.panel', array ('content' => "ticket_table", 'view_header' => trans('messages.dashbrd_ticket'), 'md' => 4, 'height' => 'auto', 'i' => '5'))
			@endif

		</div>
	</div>
@stop


<script src="{{asset('components/assets-admin/plugins/chart/Chart.min.js')}}"></script>

@section('javascript')
<script language="javascript">

	$(window).on('localstorage-position-loaded load', function() {
		// line chart contracts
		var chart_data_contracts = {!! $view['contracts'] ? json_encode($data['contracts']['chart']) : '{}' !!};

		if (Object.getOwnPropertyNames(chart_data_contracts).length != 0) {

			var labels = chart_data_contracts['labels'];
			var contracts = chart_data_contracts['contracts'];
			var internet = chart_data_contracts['Internet_only'];
			var voip = chart_data_contracts['Voip_only'];
			var internetAndVoip = chart_data_contracts['Internet_and_Voip'];
			var ctx = document.getElementById('contracts-chart').getContext('2d');
			var contractChart = new Chart(ctx, {
				type: 'line',
				data: {
					labels: labels,
					datasets: [{
						label:'VoIP',
						data: voip,
						pointBackgroundColor: 'rgb(42, 98, 254, 1)',
						borderColor: 'rgb(42, 98, 254, 1)',
						backgroundColor: 'rgb(42, 98, 254, 0.3)',
						cubicInterpolationMode: 'monotone'
					}, {
						label:'Internet & Voip',
						data: internetAndVoip,
						pointBackgroundColor: 'rgb(12, 40, 110, 1)',
						borderColor: 'rgb(12, 40, 110, 1)',
						backgroundColor: 'rgb(12, 40, 110, 0.3)',
						cubicInterpolationMode: 'monotone'
					}, {
						label: 'Internet',
						data: internet,
						pointBackgroundColor: 'rgb(0, 170, 132, 1)',
						borderColor: 'rgb(0, 170, 132, 1)',
						backgroundColor: 'rgb(0, 170, 132, 0.3)',
						cubicInterpolationMode: 'monotone'
					}, {
						label: "{!! trans('messages.active contracts') !!}",
						data: contracts,
						pointBackgroundColor: 'rgb(2, 207, 211, 1)',
						borderColor: 'rgb(2, 207, 211, 1)',
						backgroundColor: 'rgb(2, 207, 211, 0.3)',
						cubicInterpolationMode: 'monotone'
					}],
				},
				options: {
					animation: {
						duration: 0,
					},
					legend: {
						display: true,
					},
					maintainAspectRatio: false,
					scales: {
						yAxes: [{
							ticks: {
								beginAtZero: false,
							}
						}]
					}
				}
			});
		}

		// bar chart income
		var chart_data_income = {!! $view['income'] ? json_encode($data['income']['chart']) : '{}' !!};

		if (Object.getOwnPropertyNames(chart_data_income).length != 0) {

			var labels = chart_data_income['labels'];
			var incomes = chart_data_income['data'];
			var ctx = document.getElementById('income-chart').getContext('2d');
			var incomeChart = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: labels,
					datasets: [{
						data: incomes,
						backgroundColor: [
							"rgba(255, 206, 86, 0.8)",
							"rgba(75, 192, 192, 0.8)",
							"rgba(54, 162, 235, 0.8)",
							"rgba(153, 102, 255, 0.8)",
						]
					}],
				},
				options: {
					legend: {
						display: false
					},
					maintainAspectRatio: false,
					scales: {
						yAxes: [{
							ticks: {
								beginAtZero: true
							}
						}]
					}
				}
			});
		}
	});
</script>
@stop
