@extends ('Layout.default')


<!-- Widgets -->
@section ('contracts')
	@include('dashboard::widgets.contracts')
@stop

@section ('incomes')
	@include('dashboard::widgets.income')
@stop

@section ('provvoipenvia')
	@include('dashboard::widgets.provvoipenvia')
@stop

@section ('tickets')
	@include('dashboard::widgets.ticket')
@stop

@section ('date')
	@include('dashboard::widgets.date')
@stop

{{--@section ('quickstart')--}}
    {{--@include('dashboard::widgets.quickstart')--}}
{{--@stop--}}


<!-- Panels -->
@section ('contract_analytics')
	@include('dashboard::panels.contract_analytics')
@stop

@section ('income_analytics')
	@include('dashboard::panels.income_analytics')
@stop

{{--@section ('quickstart')--}}
    {{--@include('dashboard::panels.quickstart')--}}
{{--@stop--}}

@section('content')
	<div class="col-md-12">

		<h1 class="page-header">{{ $title }}</h1>

		<div class="row">
			{{-- Contracts --}}
			@DivOpen(3)
				@include ('bootstrap.widget',
					array (
						'content' => 'contracts',
						'widget_icon' => 'users',
						'widget_bg_color' => 'green',
					)
				)
			@DivClose()

			{{-- Income --}}
			@if (\PPModule::is_active('billingbase'))
				@if ($allowed_to_see['accounting'] === true)

					@DivOpen(3)
						@include ('bootstrap.widget',
							array (
								'content' => 'incomes',
								'widget_icon' => 'euro',
								'widget_bg_color' => 'blue',
							)
						)
					@DivClose()
				@endif
			@endif

			{{-- Placeholder --}}
			@if (\PPModule::is_active('provvoipenvia'))

				@DivOpen(3)
					@include ('bootstrap.widget',
						array (
							'content' => 'provvoipenvia',
							'widget_icon' => 'info',
							'widget_bg_color' => 'aqua',
						)
					)
				@DivClose()
			@endif

			{{-- Tickets --}}
			@DivOpen(3)
				@include ('bootstrap.widget',
					array (
						'content' => 'tickets',
						'widget_icon' => 'ticket',
						'widget_bg_color' => 'orange',
					)
				)
			@DivClose()

			{{-- Date --}}
			@DivOpen(3)
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
            {{-- Quickstart --}}
            @DivOpen(3)
                @include('dashboard::widgets.quickstart')
            @DivClose()
        </div>

        <div class="row">
			{{-- Contract chart --}}
            @if ($contracts > 0)
				@DivOpen(8)
					@include ('bootstrap.panel', array ('content' => "contract_analytics", 'view_header' => 'Contract Analytics', 'md' => 12, 'height' => 'auto'))
				@DivClose()
			@endif

			{{-- Income chart --}}
			@if (\PPModule::is_active('billingbase'))
				@if ($allowed_to_see['accounting'] === true)
					@if (isset($income['total']))

						@DivOpen(4)
							@include ('bootstrap.panel', array ('content' => "income_analytics", 'view_header' => 'Income Details', 'md' => 12, 'height' => 'auto'))
						@DivClose()
					@endif
				@endif
			@endif
		</div>
	</div>
@stop

<script type="text/javascript">
    if (typeof window.jQuery == 'undefined') {
        document.write('<script src="{{asset('components/assets-admin/plugins/jquery/jquery-1.9.1.min.js')}}">\x3C/script>');
    }
</script>
<script src="{{asset('components/assets-admin/plugins/chart/Chart.min.js')}}"></script>

<script type="text/javascript">

    window.onload = function() {

        // line chart contracts
        var chart_data_contracts = {{ json_encode($chart_data_contracts) }};

        if (chart_data_contracts.length != 0) {

            var labels = chart_data_contracts['labels'];
            var contracts = chart_data_contracts['contracts'];
            var ctx = document.getElementById('contracts-chart').getContext('2d');
            var contractChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: contracts,
                        backgroundColor: "rgba(0, 172, 172, 0.8)",
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

        // bar chart income
        var chart_data_income = {{ json_encode($chart_data_income) }};

        if (chart_data_income.length != 0) {

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
    }
</script>
