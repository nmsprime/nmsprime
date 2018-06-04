<?php

// This is done inside admin GUI
BaseRoute::group([], function() {
	// Global Config - TODO:
	BaseRoute::resource('Ccc', 'Modules\Ccc\Http\Controllers\CccController');
	// Contract: Download Connection Info
	BaseRoute::get('contract/conn_info/{id}', array('as' => 'Contract.ConnInfo', 'uses' => 'Modules\Ccc\Http\Controllers\CccUserController@connection_info_download'));
});

Route::group(['middleware' => 'web', 'prefix' => 'customer'], function () {
	Route::get('login', array('as' => 'customerLogin', 'uses' => 'Modules\Ccc\Http\Controllers\LoginController@showLoginForm'));
	Route::post('login', array('as' => 'customerLogin.post', 'uses' => 'Modules\Ccc\Http\Controllers\LoginController@login'));
	Route::post('logout', array('as' => 'customerLogout.post', 'uses' => 'Modules\Ccc\Http\Controllers\LoginController@logout'));
});

Route::group(['middleware' => 'auth.basic', 'prefix' => 'customer'], function () {
	Route::get ('', ['as' => 'HomeCcc', 'uses' => 'Modules\Ccc\Http\Controllers\CccUserController@show']);
	Route::get ('password', ['as' => 'CustomerPsw', 'uses' => 'Modules\Ccc\Http\Controllers\CccUserController@psw_update']);
	Route::post ('password', ['as' => 'CustomerPsw', 'uses' => 'Modules\Ccc\Http\Controllers\CccUserController@psw_update']);

	// Download Invoice
	Route::get('home/download/{invoice}', array('as' => 'Customer.Download', 'uses' => 'Modules\Ccc\Http\Controllers\CccUserController@download'));
});
