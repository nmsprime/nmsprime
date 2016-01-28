<?php

// Authentification is necessary before accessing a route
Route::group(array('before' => 'auth'), function() {

	Route::get('Customer/{field}/{search}', array('as' => 'CustomerTopo.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show'));
	Route::get('CustomerRect/{x1}/{x2}/{y1}/{y2}', array('as' => 'CustomerTopo.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@showRect'));

});