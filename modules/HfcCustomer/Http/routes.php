<?php

BaseRoute::group([], function() {

	BaseRoute::get('Customer/{field}/{search}', array('as' => 'CustomerTopo.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show'));
	BaseRoute::get('CustomerRect/{x1}/{x2}/{y1}/{y2}', array('as' => 'CustomerRect.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_rect'));
	BaseRoute::get('CustomerModem/{topo_dia}/{ids}', array('as' => 'CustomerModem.show', 'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_modem_ids'));

	BaseRoute::resource('Mpr', 'Modules\HfcCustomer\Http\Controllers\MprController');
	BaseRoute::resource('MprGeopos', 'Modules\HfcCustomer\Http\Controllers\MprGeoposController');

});

// TODO: proper user authentication needed
Route::get('app/data/hfccustomer/kml/{file}', function($file = null)
{
	$path = storage_path("app/data/hfccustomer/kml/$file");
	// return 404 if user is not logged in
	if(!Auth::user()) {
		return App::abort(404);
	}
	if (file_exists($path))
		return Response::file($path);
});