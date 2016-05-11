<?php

// Authentification is necessary before accessing a route
Route::group(array('before' => 'auth'), function() {

	Route::get('/provvoipenvia/index', array('as' => 'ProvVoipEnvia.index', 'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@index'));
	Route::get('/provvoipenvia/request/{job}', array('as' => 'ProvVoipEnvia.request', 'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@request'));
	Route::get('/provvoipenvia/cron/{job}', array('as' => 'ProvVoipEnvia.cron', 'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@cron'));

	CoreRoute::resource('EnviaOrder', 'Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderController');
	CoreRoute::resource('EnviaOrderDocument', 'Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderDocumentController',
		['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy', 'show']]);
});
