<?php

BaseRoute::group([], function() {

	Route::get('Dashboard', ['as' => 'Dashboard.index', 'uses' => 'Modules\Dashboard\Http\Controllers\DashboardController@index']);
});
