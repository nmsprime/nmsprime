<?php

BaseRoute::group([], function () {
    BaseRoute::get('provmon/{id}', [
        'as' => 'ProvMon.index',
        'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@analyses',
        'middleware' => ['can:view_analysis_pages_of,Modules\ProvBase\Entities\Modem'],
    ]);

    BaseRoute::get('provmon_cpe/{id}', [
        'as' => 'ProvMon.cpe',
        'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@cpe_analysis',
        'middleware' => ['can:view_analysis_pages_of,Modules\ProvBase\Entities\Modem'],
    ]);

    BaseRoute::get('provmon_mta/{id}', [
        'as' => 'ProvMon.mta',
        'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@mta_analysis',
        'middleware' => ['can:view_analysis_pages_of,Modules\ProvBase\Entities\Modem'],
    ]);

    BaseRoute::get('provmon_cmts/{id}', [
        'as' => 'ProvMon.cmts',
        'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@cmts_analysis',
        'middleware' => ['can:view_analysis_pages_of,Modules\ProvBase\Entities\Cmts'],
    ]);

    BaseRoute::post('provmon/{id}', [
        'as' => 'ProvMon.flood_ping',
        'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@analyses',
        'middleware' => ['can:view_analysis_pages_of,Modules\ProvBase\Entities\Modem'],
    ]);

    BaseRoute::get('provmon/{id}/spectrum', [
        'as' => 'ProvMon.createSpectrum',
        'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@getSpectrumData',
        'middleware' => ['can:view_analysis_pages_of,Modules\ProvBase\Entities\Modem'],
    ]);

    BaseRoute::get('provmon/ping/{ip}', [
        'as' => 'ProvMon.realtime_ping',
        'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@realtime_ping',
        'middleware' => ['can:view_analysis_pages_of,Modules\ProvBase\Entities\Modem'],
    ]);

    BaseRoute::get('NetElement/{id}/diagram', [
        'as' => 'ProvMon.diagram_edit',
        'uses' => 'Modules\ProvMon\Http\Controllers\ProvMonController@diagram_edit',
        'middleware' => ['can:view,Modules\HfcReq\Entities\NetElement'],
    ]);
});
