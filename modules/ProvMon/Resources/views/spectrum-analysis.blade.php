<!-- DFT Spectrum -->

<center>
	<div style="padding-bottom: 10px">
		<button id="showSpectrum" class="btn btn-primary">{{trans('messages.createSpectrum')}}</button>
	</div>
		<span id='pleaseWait' class="alert-info fade in m-b-15" style="color: #00f"></span>
		<span id='notValid' class="alert-danger fade in m-b-15" style="color: #f00"></span>
</center>
<div id="wrapper"></div>

@section('spectrum')
<script src="{{asset('components/assets-admin/plugins/chart/Chart.min.js')}}"></script>
<script language="javascript">

function makeSpectrum(amplitudes, span) {

	document.getElementById("wrapper").innerHTML='<canvas id="spectrum"></canvas>';

	var ctx = document.getElementById('spectrum').getContext('2d');
	var chart = new Chart(ctx, {
		type: 'line',
		data: {
			labels: span,
			datasets: [{
				data: amplitudes,
				borderColor: '#000',
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
				         return t.xLabel + 'MHz: ' + t.yLabel + 'dBmV';
				    }
		        }
			}
		}
	});
};

$(document).ready(function() {
	$('#showSpectrum').click( function(e) {
		document.getElementById("pleaseWait").innerHTML = "{{trans('messages.pleaseWait')}}";
		document.getElementById("notValid").innerHTML = '';
        $.ajax({
            type:"GET",
            url:"{{route('ProvMon.createSpectrum', ['id' => $id])}}",
            data:{ _token: "{{ csrf_token() }}", },
            dataType:"json",
            success: function(result) {
            	if (result == null) {
					document.getElementById("pleaseWait").innerHTML = '';
					document.getElementById("notValid").innerHTML = "{{ trans('messages.noSpectrum') }}";

            		return;
            	}
				document.getElementById("pleaseWait").innerHTML = '';
				document.getElementById("notValid").innerHTML = '';

               	makeSpectrum(result.amplitudes, result.span);
            },
            error: function() {
				document.getElementById("pleaseWait").innerHTML = '';
				document.getElementById("notValid").innerHTML = "{{ trans('messages.noSpectrum') }}";
            }
        });
    });
})

</script>
@stop
