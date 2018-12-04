<!-- DFT Spectrum -->

<center>
	<button id="showSpectrum" class="btn btn-primary" style="padding: 10px">{{trans('messages.createSpectrum')}}</button>
	<font color="red">
		<p id="notValid" style="padding: 10px"></p>
	</font>
</center>
<div>
    <canvas id="spectrum"></canvas>
</div>

@section('spectrum')
<script src="{{asset('components/assets-admin/plugins/chart/Chart.min.js')}}"></script>
<script language="javascript">

function makeSpectrum(amplitudes, span) {

	var ctx = document.getElementById('spectrum').getContext('2d');
	var chart = new Chart(ctx, {
		type: 'line',
		data: {
			labels: span,
			datasets: [{
				data: amplitudes,
				borderColor: 'rgb(0, 0, 0)',
				borderWidth: '2',
				fill: false,
				pointRadius:'1',
			}]
		},
		options: {
			legend: {
				display: false
			},
			maintainAspectRatio: true,
			scales: {
				xAxes: [{
        			barPercentage: 0.2,
        			scaleLabel: {
        			    display: true,
        			    labelString: 'f/MHz',
        			},
        			ticks: {
						autoSkip: true,
						maxTicksLimit: 20,
        			}
    			}],
				yAxes: [{
					scaleLabel: {
					    display: true,
					    labelString: "{{trans('messages.levelDb')}}",
					},
					ticks: {
						beginAtZero: true,
					}
				}]
			},
			tooltips: {
		        callbacks: {
		        	title: function() {
		        		return '';
		        	},
		            label: function(t) {
				         return t.xLabel + 'MHz: ' + t.yLabel + 'dB';
				    }
		        }
			}
		}
	});
};

$(document).ready(function() {
	$('#showSpectrum').click( function(e) {
        $.ajax({
            type:"GET",
            url:"{{route('ProvMon.createSpectrum', ['id' => $id])}}",
            data:{ _token: "{{ csrf_token() }}", },
            dataType:"json",
            success: function(result) {
            	if (result == null) {
					document.getElementById("notValid").innerHTML = "{{ trans('messages.noSpectrum') }}";

            		return;
            	}

               	makeSpectrum(result.amplitudes, result.span);
            }
        });
    });
})

</script>
@stop
