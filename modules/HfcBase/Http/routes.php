<?php

BaseRoute::group([], function() {

	BaseRoute::resource('HfcBase', 'Modules\HfcBase\Http\Controllers\HfcBaseController');

	BaseRoute::get('Tree/erd/{field}/{search}', [
		'as' => 'TreeErd.show',
		'uses' => 'Modules\HfcBase\Http\Controllers\TreeErdController@show',
		'middleware' => ['can:view,Modules\HfcBase\Entities\HfcBase'],
	]);

	BaseRoute::get('Tree/topo/{field}/{search}', [
		'as' => 'TreeTopo.show',
		'uses' => 'Modules\HfcBase\Http\Controllers\TreeTopographyController@show',
		'middleware' => ['can:view,Modules\HfcBase\Entities\HfcBase'],
	]);

});

Route::group(['prefix' => 'app/data/hfcbase'], function () {

	Route::get('{type}/{filename}', [
		'uses' => 'Modules\HfcBase\Http\Controllers\HfcBaseController@get_file',
		'middleware' => ['web', 'can:download,Modules\HfcBase\Entities\HfcBase'],
	]);

});
