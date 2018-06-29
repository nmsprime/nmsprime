<?php

BaseRoute::group([], function() {

	BaseRoute::get('provmon/{id}', [
		'as' => 'ProvMon.index',
		'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@analyses',
		'middleware' => ['can:edit,Modules\ProvBase\Entities\Modem'],
	]);

	BaseRoute::get('provmon_cpe/{id}', [
		'as' => 'ProvMon.cpe',
		'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@cpe_analysis',
		'middleware' => ['can:edit,Modules\ProvBase\Entities\Modem'],
	]);

	BaseRoute::get('provmon_mta/{id}', [
		'as' => 'ProvMon.mta',
		'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@mta_analysis',
		'middleware' => ['can:edit,Modules\ProvBase\Entities\Modem'],
	]);

	BaseRoute::get('provmon_cmts/{id}', [
		'as' => 'ProvMon.cmts',
		'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@cmts_analysis',
		'middleware' => ['can:edit,Modules\ProvBase\Entities\Modem'],
	]);

	BaseRoute::post('provmon/{id}', [
		'as' => 'ProvMon.flood_ping',
		'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@analyses',
		'middleware' => ['can:edit,Modules\ProvBase\Entities\Modem'],
	]);

	BaseRoute::get('provmon/ping/{ip}', [
		'as' => 'ProvMon.realtime_ping',
		'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@realtime_ping',
		'middleware' => ['can:edit,Modules\ProvBase\Entities\Modem'],
	]);

});
