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

        <div class="row">
            @section ('income_analytics')
                @include('billingbase::panels.income_analytics')
            @stop
            @include ('bootstrap.panel', array ('content' => "income_analytics", 'view_header' => trans('view.Dashboard_IncomeAnalytics'), 'md' => 7, 'height' => 'auto', 'i' => '4'))

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
            </div>
        </div>
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
        });
    </script>
@stop
