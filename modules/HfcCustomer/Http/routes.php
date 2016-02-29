<?php

// Authentification is necessary before accessing a route
Route::group(array('before' => 'auth'), function() {

	Route::get('Customer/{field}/{search}', array('as' => 'CustomerTopo.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show'));
	Route::get('CustomerRect/{x1}/{x2}/{y1}/{y2}', array('as' => 'CustomerRect.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_rect'));
	Route::get('CustomerModem/{topo_dia}/{ids}', array('as' => 'CustomerModem.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_modem_ids'));

	Route::resource('Mpr', 'Modules\HfcCustomer\Http\Controllers\MprController');
	Route::get('Mpr/fulltextSearch', array('as' => 'Mpr.fulltextSearch', 'uses' => 'Modules\HfcCustomer\Http\Controllers\MprController@fulltextSearch'));

	Route::resource('MprGeopos', 'Modules\HfcCustomer\Http\Controllers\MprGeoposController');
	Route::get('MprGeopos/fulltextSearch', array('as' => 'MprGeopos.fulltextSearch', 'uses' => 'Modules\HfcCustomer\Http\Controllers\MprGeoposController@fulltextSearch'));

});
