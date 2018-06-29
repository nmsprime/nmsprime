<?php

BaseRoute::group([], function() {

	BaseRoute::get('', [
		'as' => 'Dashboard.index',
		'uses' => 'Modules\Dashboard\Http\Controllers\DashboardController@index'
	]);

});
