
@extends('Generic.edit')

@section('content_left')

	@parent

		<?php

			if ($additional_data['user_actions']) {
				echo '<div class="col-md-12" style="margin-top: 30px; padding-top: 20px; border-top:solid #888 1px">';
				$tmp = array();
				foreach ($additional_data['user_actions'] as $linktext => $link) {
					array_push($tmp, '<a href="'.$link.'" target="_self">» '.$linktext.'</a>');
				}
				echo implode('<br>', $tmp);
				echo '</div>';
			}
		?>

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
