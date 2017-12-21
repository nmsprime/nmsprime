<?php

BaseRoute::group([], function() {

	BaseRoute::get('Dashboard', ['as' => 'Dashboard.index', 'uses' => 'Modules\Dashboard\Http\Controllers\DashboardController@index']);
});
