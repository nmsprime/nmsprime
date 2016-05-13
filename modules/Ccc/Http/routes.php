<?php


// Home Route, This will redirect depending on valid Login
Route::get('ccc', array('as' => 'Home', 'uses' => 'Modules\Ccc\Http\Controllers\AuthController@home'));


// Auth => login form
Route::get('ccc/auth/login', array('as' => 'CccAuth.login', 'uses' => 'Modules\Ccc\Http\Controllers\AuthController@showLogin'));

// Auth => process form data
Route::post('ccc/auth/login', array('as' => 'CccAuth.login', 'uses' => 'Modules\Ccc\Http\Controllers\AuthController@doLogin'));

// Auth => Logout
Route::get ('ccc/auth/logout', array('as' => 'CccAuth.logout', 'uses' => 'Modules\Ccc\Http\Controllers\AuthController@doLogout'));
Route::post('ccc/auth/logout', array('as' => 'CccAuth.logout', 'uses' => 'Modules\Ccc\Http\Controllers\AuthController@doLogout'));


// CCC internal stuff, with CCC authentication checking
Route::group(['middleware' => 'ccc.base'], function () {

	Route::get ('ccc/home', ['as' => 'HomeCcc', 'uses' => 'Modules\Ccc\Http\Controllers\CccController@show']);
	// TODO: add CCC internal required routing stuff

});
