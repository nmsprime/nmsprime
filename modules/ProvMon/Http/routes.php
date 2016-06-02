<?php

BaseRoute::group([], function() {

	BaseRoute::get('provmon/{id}', array ('as' => 'Provmon.index', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@analyses'));
	BaseRoute::get('provmon_cpe/{id}', array ('as' => 'Provmon.cpe', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@cpe_analysis'));
	BaseRoute::get('provmon_mta/{id}', array ('as' => 'Provmon.mta', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@mta_analysis'));
	BaseRoute::post('provmon/{id}', array ('as' => 'Provmon.flood_ping', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@analyses'));

});
