<?php

BaseRoute::group([], function () {
    BaseRoute::resource('EnviaContract', 'Modules\ProvVoipEnvia\Http\Controllers\EnviaContractController');
    BaseRoute::resource('EnviaOrder', 'Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderController');
    BaseRoute::resource('EnviaOrderDocument', 'Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderDocumentController');

    Route::get('/provvoipenvia/cron/{job}', [
        'as' => 'ProvVoipEnvia.cron',
        'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@cron',
        // TODO: @xee8ai @Patrick: which auth middleware should be used?
        // none? -> create middleware cron or move Route to routes/Commmands.php
    ]);

    BaseRoute::get('/provvoipenvia/index', [
        'as' => 'ProvVoipEnvia.index',
        'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@index',
        'middleware' => ['can:view,Modules\ProvVoipEnvia\Entities\ProvVoipEnvia'],
    ]);

    BaseRoute::get('/provvoipenvia/request/{job}', [
        'as' => 'ProvVoipEnvia.request',
        'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController@request',
        'middleware' => ['can:update,Modules\ProvVoipEnvia\Entities\ProvVoipEnvia'],
    ]);

    BaseRoute::get('/enviaorderdocument/{id}/show', [
        'as' => 'EnviaOrderDocument.show',
        'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderDocumentController@show',
        'middleware' => ['can:view,Modules\ProvVoipEnvia\Entities\EnviaOrderDocument'],
    ]);

    BaseRoute::get('/EnviaOrder/{EnviaOrder}/marksolved', [
        'as' => 'EnviaOrder.marksolved',
        'uses' => 'Modules\ProvVoipEnvia\Http\Controllers\EnviaOrderController@mark_solved',
        'middleware' => ['can:update,Modules\ProvVoipEnvia\Entities\EnviaOrderDocument'],
    ]);
});
