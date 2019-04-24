
@extends('Generic.index')

@section('content_left')

<?php

	$filter = \Input::get('show_filter', 'all');
	if ($filter != 'action_needed') {
		$filter = 'all';
	}

	if ($filter == 'all') {
		echo '<h5><b>'.trans('provvoipenvia::messages.order_list_all').'</b></h5>';
		echo '<a href="?show_filter=action_needed" target="_self"> »'.trans('provvoipenvia::messages.order_show_interaction_needing').'</a><br>';
	}
	elseif ($filter == 'action_needed') {
		echo '<h5><b>'.trans('provvoipenvia::messages.order_list_interaction_needing').'</b></h5>';
		echo '<a href="?show_filter=all" target="_self"> »'.trans('provvoipenvia::messages.order_show_all').'</a><br>';
	}
?>

	@parent

@stop
