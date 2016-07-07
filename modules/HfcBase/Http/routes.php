<?php

BaseRoute::group([], function() {

	BaseRoute::resource('Tree', 'Modules\HfcBase\Http\Controllers\TreeController');
	BaseRoute::resource('HfcBase', 'Modules\HfcBase\Http\Controllers\HfcBaseController');

	BaseRoute::get('tree/fulltextSearch', array('as' => 'Tree.fulltextSearch', 'uses' => 'Modules\HfcBase\Http\Controllers\TreeController@fulltextSearch'));

	BaseRoute::get('Tree/erd/{field}/{search}', array('as' => 'TreeErd.show', 'uses' => 'Modules\HfcBase\Http\Controllers\TreeErdController@show'));
	BaseRoute::get('Tree/topo/{field}/{search}', array('as' => 'TreeTopo.show', 'uses' => 'Modules\HfcBase\Http\Controllers\TreeTopographyController@show'));


	BaseRoute::get('Tree/{id}/delete', array('as' => 'Tree.delete', 'uses' => 'Modules\HfcBase\Http\Controllers\TreeController@delete'));

});

// TODO: proper user authentication needed
Route::get('app/data/hfcbase/{type}/{file}', function($type = null, $file = null)
{
	$path = storage_path("app/data/hfcbase/$type/$file");
	// return 404 if user is not logged in or if type is neither 'erd' nor 'kml'
	if(!Auth::user() || ($type != 'erd' && $type != 'kml')) {
		return App::abort(404);
	}
	if (file_exists($path))
		return Response::file($path);
});