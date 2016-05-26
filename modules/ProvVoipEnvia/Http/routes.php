<?php

BaseRoute::group([], function() {

	Route::get('provvoipenvia/index', array('as' => 'ProvVoipEnvia.index', 'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@index'));
	Route::get('provvoipenvia/request/{job}', array('as' => 'ProvVoipEnvia.request', 'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@request'));
	Route::get('provvoipenvia/cron/{job}', array('as' => 'ProvVoipEnvia.cron', 'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@cron'));

	BaseRoute::resource('EnviaOrder', 'Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderController');
	BaseRoute::resource('EnviaOrderDocument', 'Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderDocumentController',
		['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy', 'show']]);
});
