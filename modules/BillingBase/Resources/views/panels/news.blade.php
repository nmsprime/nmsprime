
@if (isset($news['text']))
	<div>
		<h4>
		{!!$news['text']!!}
		</h4>
	</div>
	<hr>
@endif


@if (isset($news['youtube']))
	<div>
		<iframe class="col-sm-12" height="350px" width="350" frameborder="0" wmode="Opaque" allowfullscreen=""
				src="{{$news['youtube']}}?wmode=transparent">
		</iframe>
	</div>
	<hr>
@endif
