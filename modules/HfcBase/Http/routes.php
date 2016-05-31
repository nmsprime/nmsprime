<?php

BaseRoute::group([], function() {

	BaseRoute::resource('Tree', 'Modules\HfcBase\Http\Controllers\TreeController');
	BaseRoute::resource('HfcBase', 'Modules\HfcBase\Http\Controllers\HfcBaseController');

	BaseRoute::get('tree/fulltextSearch', array('as' => 'Tree.fulltextSearch', 'uses' => 'Modules\HfcBase\Http\Controllers\TreeController@fulltextSearch'));

	BaseRoute::get('Tree/erd/{field}/{search}', array('as' => 'TreeErd.show', 'uses' => 'Modules\HfcBase\Http\Controllers\TreeErdController@show'));
	BaseRoute::get('Tree/topo/{field}/{search}', array('as' => 'TreeTopo.show', 'uses' => 'Modules\HfcBase\Http\Controllers\TreeTopographyController@show'));


	BaseRoute::get('Tree/{id}/delete', array('as' => 'Tree.delete', 'uses' => 'Modules\HfcBase\Http\Controllers\TreeController@delete'));

});