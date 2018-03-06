<?php

BaseRoute::group([], function() {

	BaseRoute::get('Customer/{field}/{search}', array('as' => 'CustomerTopo.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show'));
	BaseRoute::get('Customer/prox', array('as' => 'CustomerTopo.show_prox', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_prox'));
	BaseRoute::get('Customer/bad', array('as' => 'CustomerTopo.show_bad', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_bad'));
	BaseRoute::get('CustomerRect/{x1}/{x2}/{y1}/{y2}', array('as' => 'CustomerRect.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_rect'));
	BaseRoute::get('CustomerPoly/{poly}', array('as' => 'CustomerPoly.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_poly'));
	BaseRoute::get('CustomerModem/{topo_dia}/{ids}', array('as' => 'CustomerModem.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_modem_ids'));

	BaseRoute::resource('Mpr', 'Modules\HfcCustomer\Http\Controllers\MprController');
	BaseRoute::get('Mpr/{id}/update_geopos/{new_gp}', array('as' => 'Mpr.update_geopos', 'uses' => 'Modules\HfcCustomer\Http\Controllers\MprController@update_geopos'));
	BaseRoute::resource('MprGeopos', 'Modules\HfcCustomer\Http\Controllers\MprGeoposController');

});

Route::group(['middleware' => 'auth:view', 'prefix' => 'app/data/hfccustomer'], function () {
	Route::get('{type}/{filename}', array('uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@get_file'));
});
