<?php

Route::get('/provvoipenvia/cron/{job}', array('as' => 'ProvVoipEnvia.cron', 'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@cron'));

BaseRoute::group([], function() {

	BaseRoute::get('/provvoipenvia/index', array('as' => 'ProvVoipEnvia.index', 'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@index'));
	BaseRoute::get('/provvoipenvia/request/{job}', array('as' => 'ProvVoipEnvia.request', 'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@request'));

	BaseRoute::get('/enviaorderdocument/{id}/show', array('as' => 'EnviaOrderDocument.show', 'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderDocumentController@show'));

	BaseRoute::get('/EnviaOrder/{EnviaOrder}/marksolved', array('as' => 'EnviaOrder.marksolved', 'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderController@mark_solved'));

	BaseRoute::resource('EnviaContract', 'Modules\ProvVoipEnvia\Http\Controllers\EnviaContractController');
	BaseRoute::resource('EnviaOrder', 'Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderController');
	BaseRoute::resource('EnviaOrderDocument', 'Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderDocumentController');
});
