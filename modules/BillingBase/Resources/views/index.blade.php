@extends ('Layout.default')

@section ('income')
    <h4>{{ \App\Http\Controllers\BaseViewController::translate_view('Net Income', 'Dashboard') }} {{ date('m/Y') }}</h4>
    <p>
        @if ($income_data['total'])
            {{ number_format($income_data['total'], 0, ',', '.') }}
        @else
            {{ number_format(0, 0, ',', '.') }}
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
    </div>
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-7">
                <div class="row">
                    @section ('contract_analytics')
                        @include('billingbase::panels.contract_analytics')
                    @stop
                    @include ('bootstrap.panel', array ('content' => "contract_analytics", 'view_header' => trans('view.Dashboard_ContractAnalytics'), 'md' => 12, 'height' => 'auto', 'i' => '4'))
                </div>
                <div class="row">
                    @section ('income_analytics')
                        @include('billingbase::panels.income_analytics')
                    @stop
                    @include ('bootstrap.panel', array ('content' => "income_analytics", 'view_header' => trans('view.Dashboard_IncomeAnalytics'), 'md' => 12, 'height' => 'auto', 'i' => '3'))
                </div>
            </div>
            <div class="col-md-5">
                <div class="row">
                    @DivOpen(6)
                    @include ('bootstrap.widget',
                        array (
                            'content' => 'income',
                            'widget_icon' => 'euro',
                            'widget_bg_color' => 'blue',
                            'link_target' => '#anchor-income',
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
                        @include('billingbase::widgets.documentation')
                    </div>
                </div>
                <div class="row">
                    @if ($contracts_data['table'])
                    @section ('weekly_contracts')
                        @include('provbase::panels.weekly_contracts')
                    @stop
                    @include ('bootstrap.panel', array ('content' => "weekly_contracts", 'view_header' => trans('view.Dashboard_WeeklyCustomers'), 'md' => 12, 'height' => 'auto', 'i' => '1'))
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="row">
            @if (isset($news) && $news)
            @section ('news')
                @include('billingbase::panels.news')
            @stop
            @include ('bootstrap.panel', array ('content' => "news", 'view_header' => 'News', 'md' => 12, 'height' => '350px', 'i' => '5'))
            @endif
        </div>
    </div>

@stop
@section('javascript')
    <script src="{{asset('components/assets-admin/plugins/chart/Chart.min.js')}}"></script>
    <script language="javascript">

        $(window).on('localstorage-position-loaded load', function () {
            // bar chart income
            var chart_data_income = {!! $income_data ? json_encode($income_data['chart']) : '{}' !!};

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
            // line chart contracts
            var chart_data_contracts = {!! $contracts_data ? json_encode($contracts_data['chart']) : '{}' !!};

            if (Object.getOwnPropertyNames(chart_data_contracts).length != 0) {

                var labels = chart_data_contracts['labels'],
                    contracts = chart_data_contracts['contracts'],
                    internet = chart_data_contracts['Internet_only'],
                    voip = chart_data_contracts['Voip_only'],
                    internetAndVoip = chart_data_contracts['Internet_and_Voip'],
                    ctx = document.getElementById('contracts-chart').getContext('2d');

                var contractChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                                @if (Module::collections()->has('BillingBase'))
                            {
                                label: 'VoIP',
                                data: voip,
                                pointBackgroundColor: 'rgb(42, 98, 254, 1)',
                                borderColor: 'rgb(42, 98, 254, 1)',
                                backgroundColor: 'rgb(42, 98, 254, 0.3)',
                                cubicInterpolationMode: 'monotone'
                            }, {
                                label: 'Internet & Voip',
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
                            },
                                @endif
                            {
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
        });
    </script>
@stop
