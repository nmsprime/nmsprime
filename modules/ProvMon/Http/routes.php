<?php

BaseRoute::group([], function() {

	BaseRoute::get('provmon/{id}', array ('as' => 'ProvMon.index', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@analyses'));
	BaseRoute::get('provmon_cpe/{id}', array ('as' => 'ProvMon.cpe', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@cpe_analysis'));
	BaseRoute::get('provmon_mta/{id}', array ('as' => 'ProvMon.mta', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@mta_analysis'));
	BaseRoute::get('provmon_cmts/{id}', array ('as' => 'ProvMon.cmts', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@cmts_analysis'));
	BaseRoute::post('provmon/{id}', array ('as' => 'ProvMon.flood_ping', 'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@analyses'));

});
