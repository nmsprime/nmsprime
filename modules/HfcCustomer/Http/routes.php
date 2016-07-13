<?php

BaseRoute::group([], function() {

	BaseRoute::get('Customer/{field}/{search}', array('as' => 'CustomerTopo.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show'));
	BaseRoute::get('CustomerRect/{x1}/{x2}/{y1}/{y2}', array('as' => 'CustomerRect.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_rect'));
	BaseRoute::get('CustomerModem/{topo_dia}/{ids}', array('as' => 'CustomerModem.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_modem_ids'));

	BaseRoute::resource('Mpr', 'Modules\HfcCustomer\Http\Controllers\MprController');
	BaseRoute::resource('MprGeopos', 'Modules\HfcCustomer\Http\Controllers\MprGeoposController');

});

Route::group(['middleware' => 'auth:view', 'prefix' => 'app/data/hfccustomer'], function () {
	Route::get('kml/{filename}', array('uses' => 'Modules\HfcCustomer\Http\Controllers\HfcCustomerController@get_file'));
});
