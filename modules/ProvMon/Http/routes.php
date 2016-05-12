<?php

BaseRoute::group([], function() {

	Route::get('provmon/{id}', array ('as' => 'Provmon.index', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@analyses'));
	Route::get('provmon_cpe/{id}', array ('as' => 'Provmon.cpe', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@cpe_analysis'));
	Route::get('provmon_mta/{id}', array ('as' => 'Provmon.mta', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@mta_analysis'));
	Route::post('provmon/{id}', array ('as' => 'Provmon.flood_ping', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@analyses'));

});
