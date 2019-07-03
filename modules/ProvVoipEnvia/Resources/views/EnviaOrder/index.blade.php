
@extends('Generic.index')

@section('content_left')

<?php

	$filter = \Input::get('show_filter', 'all');
	if ($filter != 'action_needed') {
		$filter = 'all';
	}

	if ($filter == 'all') {
		echo '<h5><b>'.trans('provvoipenvia::view.enviaOrder.listAll').'</b></h5>';
		echo '<a href="?show_filter=action_needed" target="_self"> »'.trans('provvoipenvia::view.enviaOrder.showInteractionNeeding').'</a><br>';
	}
	elseif ($filter == 'action_needed') {
		echo '<h5><b>'.trans('provvoipenvia::view.enviaOrder.listInteractionNeeding').'</b></h5>';
		echo '<a href="?show_filter=all" target="_self"> »'.trans('provvoipenvia::view.enviaOrder.showAll').'</a><br>';
	}
?>

	@parent

@stop
