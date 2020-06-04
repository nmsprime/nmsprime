<script src="{{asset('components/assets-admin/plugins/chart/Chart.min.js')}}"></script>
<script language="javascript">
    var chart_data_contracts = '{}';
    $(window).on('localstorage-position-loaded load', function () {
    var modemCanvas = document.getElementById('modem-chart').getContext('2d');
    var serviceCanvas = document.getElementById('service-chart').getContext('2d');
    var netelementCanvas = document.getElementById('netelement-chart').getContext('2d');
    var canvasOptions = {
        responsive: {
            aspectRatio: 1
        },
        //animation: 0,
        legend: {
             display: false
        },
        tooltips : {
            enabled: false,
            custom: function(tooltipModel) {
                // Tooltip Element
                var tooltipEl = document.getElementById('chartjs-tooltip');

                // Create element on first render
                if (!tooltipEl) {
                    tooltipEl = document.createElement('div');
                    tooltipEl.id = 'chartjs-tooltip';
                    tooltipEl.innerHTML = '<table></table>';
                    document.body.appendChild(tooltipEl);
                }

                // Hide if no tooltip
                if (tooltipModel.opacity === 0) {
                    tooltipEl.style.opacity = 0;
                    return;
                }

                // Set caret Position
                tooltipEl.classList.remove('above', 'below', 'no-transform');
                if (tooltipModel.yAlign) {
                    tooltipEl.classList.add(tooltipModel.yAlign);
                } else {
                    tooltipEl.classList.add('no-transform');
                }

                function getBody(bodyItem) {
                    return bodyItem.lines;
                }

                // Set Text
                if (tooltipModel.body) {
                    var titleLines = tooltipModel.title || [];
                    var bodyLines = tooltipModel.body.map(getBody);

                    var innerHtml = '<thead>';

                    titleLines.forEach(function(title) {
                        innerHtml += '<tr><th>' + title + '</th></tr>';
                    });
                    innerHtml += '</thead><tbody>';

                    bodyLines.forEach(function(body, i) {
                        var style = 'background: rgba(0,0,0,1)';
                        style += '; border-color: rgba(0,0,0,1)';
                        style += '; border-width: 2px';
                        var span = '<span style="' + style + '"></span>';
                        innerHtml += '<tr><td>' + span + body + '</td></tr>';
                    });
                    innerHtml += '</tbody>';

                    var tableRoot = tooltipEl.querySelector('table');
                    tableRoot.innerHTML = innerHtml;
                }

                // `this` will be the overall tooltip
                var position = this._chart.canvas.getBoundingClientRect();

                // Display, position, and set styles for font
                tooltipEl.style.opacity = 1;
                tooltipEl.style.position = 'absolute';
                tooltipEl.style.left = position.left + window.pageXOffset + tooltipModel.x + 'px';
                tooltipEl.style.top = position.top + window.pageYOffset + tooltipModel.y + 'px';
                tooltipEl.style.fontFamily = tooltipModel._bodyFontFamily;
                tooltipEl.style.fontSize = tooltipModel.bodyFontSize + 'px';
                tooltipEl.style.fontStyle = tooltipModel._bodyFontStyle;
                tooltipEl.style.padding = tooltipModel.yPadding + 'px ' + tooltipModel.xPadding + 'px';
                tooltipEl.style.pointerEvents = 'none';
                tooltipEl.style.backgroundColor = 'rgba(0,0,0,0.9)';
                tooltipEl.style.color = 'rgb(255,255,255)';
                tooltipEl.style.borderRadius = '5px';
            }
        }
    };

    var modemChart = new Chart(modemCanvas, {
        type: 'doughnut',
        data: {
            datasets: [
                {
                    data: [
                        {{ $modem_statistics->online - $modem_statistics->warning - $modem_statistics->critical }},
                        {{ $modem_statistics->warning }},
                        {{ $modem_statistics->critical }},
                        {{ $modem_statistics->all -$modem_statistics->online }}
                    ],
                    backgroundColor: [
                        'green',
                        'orange',
                        'red',
                        'gray'
                    ],
                }
            ],
            labels: ['ok', 'warning', 'critical', 'offline']
        },

        options: canvasOptions
    });

    var serviceChart = new Chart(serviceCanvas, {
        type: 'doughnut',
        data: {
            datasets: [{
                @php
                    $serviceState = array_count_values($impairedData['services']->pluck('last_hard_state')->toArray());
                @endphp
                data: [
                    {{ $serviceState['0'] ?? '' }},
                    {{ $serviceState['1'] ?? '' }},
                    {{ $serviceState['2'] ?? '' }},
                ],
                backgroundColor: [
                    'green',
                    'orange',
                    'red',
                ]
            }],
            labels: ['online', 'warning', 'critical'],
        },
        options: canvasOptions
    });

    var serviceChart = new Chart(netelementCanvas, {
        type: 'doughnut',
        data: {
            datasets: [{
                @php
                    $serviceState = array_count_values($impairedData['hosts']->pluck('last_hard_state')->toArray());
                @endphp
                data: [
                    {{ $serviceState['0'] ?? '' }},
                    {{ $serviceState['1'] ?? '' }},
                    {{ $serviceState['2'] ?? '' }},
                ],
                backgroundColor: [
                    'green',
                    'orange',
                    'red',
                ]
            }],
            labels: ['online', 'warning', 'critical'],
        },
        options: canvasOptions
    });
});
</script>
