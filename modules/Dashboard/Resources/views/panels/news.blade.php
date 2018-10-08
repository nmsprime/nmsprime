
@if (isset($data['news']['text']))
	<div>
		<h4>
		{!!$data['news']['text']!!}
		</h4>
	</div>
	<hr>
@endif


@if (isset($data['news']['youtube']))
	<div>
		<iframe class="col-sm-12" height="350px" width="350" frameborder="0" wmode="Opaque" allowfullscreen=""
				src="{{$data['news']['youtube']}}?wmode=transparent">
		</iframe>
	</div>
	<hr>
@endif
