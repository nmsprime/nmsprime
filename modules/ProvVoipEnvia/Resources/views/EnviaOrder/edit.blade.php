
@extends('Generic.edit')

@section('content_left')

	@parent

	<div class="col-md-12" style="margin-top: 30px; padding-top: 20px; border-top:solid #888 1px">

		<?php

			// show the mailto links
			$tmp = array();
			foreach ($additional_data['mailto_links'] as $to => $link) {
				array_push($tmp, '<a href="'.$link.'">» Mail to '.$to.'</a>');
			}
			echo implode('<br>', $tmp);

		?>

	</div>
@stop
