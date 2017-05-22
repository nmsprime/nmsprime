<?php

BaseRoute::group([], function() {

	BaseRoute::get('Customer/{field}/{search}', array('as' => 'CustomerTopo.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show'));
	BaseRoute::post('Customer/prox', array('as' => 'CustomerTopo.show_prox', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_prox'));
	BaseRoute::get('CustomerRect/{x1}/{x2}/{y1}/{y2}/{row?}', array('as' => 'CustomerRect.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_rect'));
	BaseRoute::get('CustomerModem/{topo_dia}/{ids}', array('as' => 'CustomerModem.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_modem_ids'));

	BaseRoute::resource('Mpr', 'Modules\HfcCustomer\Http\Controllers\MprController');
	BaseRoute::resource('MprGeopos', 'Modules\HfcCustomer\Http\Controllers\MprGeoposController');

});

Route::group(['middleware' => 'auth:view', 'prefix' => 'app/data/hfccustomer'], function () {
	Route::get('{type}/{filename}', array('uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@get_file'));
});
