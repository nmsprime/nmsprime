
@extends('Generic.index')

@section('content_left')

<?php

	$filter = \Input::get('show_filter', 'all');
	if ($filter != 'action_needed') {
		$filter = 'all';
	}

	if ($filter == 'all') {
		echo '<h5><b>List of all EnviaOrders</b></h5>';
		echo '<a href="?show_filter=action_needed" target="_self"> »show only EnviaOrders needing user interaction</a><br>';
	}
	elseif ($filter == 'action_needed') {
		echo '<h5><b>List of EnviaOrders needing user interaction</b></h5>';
		echo '<a href="?show_filter=all" target="_self"> »show all EnviaOrders</a><br>';
	}
?>

	@parent

@stop
