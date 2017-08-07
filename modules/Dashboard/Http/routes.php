<?php

//Route::group(['middleware' => 'web', 'prefix' => 'dashboard', 'namespace' => 'Modules\Dashboard\Http\Controllers'], function()
//{
//	Route::get('/', 'DashboardController@index');
//});

BaseRoute::group([], function() {

	BaseRoute::resource('Dashboard', 'Modules\Dashboard\Http\Controllers\DashboardController@index');
});