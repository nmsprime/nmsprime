<?php

BaseRoute::group([], function() {

	BaseRoute::resource('HfcBase', 'Modules\HfcBase\Http\Controllers\HfcBaseController');

	BaseRoute::get('tree/fulltextSearch', array('as' => 'Tree.fulltextSearch', 'uses' => 'Modules\HfcReq\Http\Controllers\NetElementController@fulltextSearch'));

	BaseRoute::get('Tree/erd/{field}/{search}', array('as' => 'TreeErd.show', 'uses' => 'Modules\HfcBase\Http\Controllers\TreeErdController@show'));
	BaseRoute::get('Tree/topo/{field}/{search}', array('as' => 'TreeTopo.show', 'uses' => 'Modules\HfcBase\Http\Controllers\TreeTopographyController@show'));

});

Route::group(['middleware' => 'auth:view', 'prefix' => 'app/data/hfcbase'], function () {
	Route::get('{type}/{filename}', array('uses' => 'Modules\HfcBase\Http\Controllers\HfcBaseController@get_file'));
});
