@extends ('provmon::split')


@section('content_cacti')

	<iframe frameborder="0" scrolling="no" width=100% height=5000
		src="../../../cacti/graph_view.php?action=preview&filter={{$modem->hostname}}" name="imgbox" id="imgbox">
	</iframe>

@stop

@section('content_ping')
		@foreach ($ping as $line)

				<table>
				<tr>
					<td> 
						{{$line}}
					</td>
				</tr>

				</table>
			
		@endforeach
@stop

@section('content_lease')

		@foreach ($lease as $line)

				<table>
				<tr>
					<td> 
						{{$line}}<br><br>
					</td>
				</tr>

				</table>

		@endforeach

@stop

@section('content_log')
		@foreach ($log as $line)

				<table>
				<tr>
					<td> 
						{{$line}}
					</td>
				</tr>

				</table>
			
		@endforeach
@stop