<?php

BaseRoute::group([], function () {
    BaseRoute::resource('HfcBase', 'Modules\HfcBase\Http\Controllers\HfcBaseController');

    BaseRoute::get('Tree/erd/{field}/{search}', [
        'as' => 'TreeErd.show',
        'uses' => 'Modules\HfcBase\Http\Controllers\TreeErdController@show',
        'middleware' => ['can:view,Modules\HfcBase\Entities\TreeErd'],
    ]);

    BaseRoute::get('Tree/topo/{field}/{search}', [
        'as' => 'TreeTopo.show',
        'uses' => 'Modules\HfcBase\Http\Controllers\TreeTopographyController@show',
        'middleware' => ['can:view,Modules\HfcBase\Entities\TreeErd'],
    ]);

    BaseRoute::get('data/hfcbase/{type}/{filename}', [
        'as' => 'HfcBase.get_file',
        'uses' => 'Modules\HfcBase\Http\Controllers\HfcBaseController@get_file',
        'middleware' => ['can:view,Modules\HfcBase\Entities\TreeErd'],
    ]);

    BaseRoute::post('troubledashboard/{type}/{id}/{mute}', [
        'as' => 'TroubleDashboard.mute',
        'uses' => 'Modules\HfcBase\Http\Controllers\TroubleDashboardController@muteProblem',
        'middleware' => ['can:view,Modules\HfcBase\Entities\HfcBase'],
    ]);

    BaseRoute::get('vicinity-graph/show/{modemIds}', [
        'as' => 'VicinityGraph.show',
        'uses' => 'Modules\HfcBase\Http\Controllers\VicinityGraphController@show',
        'middleware' => ['can:view,Modules\HfcBase\Entities\HfcBase'],
    ]);
});
