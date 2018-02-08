<?php

// This is done inside admin GUI
BaseRoute::group([], function() {

	// Global Config - TODO:
	BaseRoute::resource('Ccc', 'Modules\Ccc\Http\Controllers\CccController');

	// Contract: Download Connection Info
	BaseRoute::get('contract/conn_info/{id}', array('as' => 'Contract.ConnInfo', 'uses' => 'Modules\Ccc\Http\Controllers\CccAuthuserController@connection_info_download'));
});



// CCC internal stuff, with CCC authentication checking
Route::group(['middleware' => 'ccc.base', 'prefix' => 'customer'], function () {

	Route::get ('home', ['as' => 'HomeCcc', 'uses' => 'Modules\Ccc\Http\Controllers\CccAuthuserController@show']);
	Route::get ('password', ['as' => 'CustomerPsw', 'uses' => 'Modules\Ccc\Http\Controllers\CccAuthuserController@psw_update']);
	Route::post ('password', ['as' => 'CustomerPsw', 'uses' => 'Modules\Ccc\Http\Controllers\CccAuthuserController@psw_update']);

	// Download Invoice
	Route::get('home/download/{invoice}', array('as' => 'Customer.Download', 'uses' => 'Modules\Ccc\Http\Controllers\CccAuthuserController@download'));

	// TODO: add CCC internal required routing stuff
});


// Home Route, This will redirect depending on valid Login
Route::get('customer', array('as' => 'CHome', 'uses' => 'Modules\Ccc\Http\Controllers\AuthController@home'));


// Auth => login form
Route::get('customer/auth/login', array('as' => 'CustomerAuth.login', 'uses' => 'Modules\Ccc\Http\Controllers\AuthController@showLoginForm'));

// Auth => process form data
Route::post('customer/auth/login', array('as' => 'CustomerAuth.login', 'uses' => 'Modules\Ccc\Http\Controllers\AuthController@postLogin'));

// Auth => Logout
Route::get ('customer/auth/logout', array('as' => 'CustomerAuth.logout', 'uses' => 'Modules\Ccc\Http\Controllers\AuthController@getLogout'));
Route::post('customer/auth/logout', array('as' => 'CustomerAuth.logout', 'uses' => 'Modules\Ccc\Http\Controllers\AuthController@getLogout'));

