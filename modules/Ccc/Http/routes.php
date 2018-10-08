<?php

// This is done inside admin GUI
BaseRoute::group([], function () {
    BaseRoute::resource('Ccc', 'Modules\Ccc\Http\Controllers\CccController');

    BaseRoute::get('contract/conn_info/{id}', [
        'as' => 'Contract.ConnInfo',
        'uses' => 'Modules\Ccc\Http\Controllers\CccUserController@connection_info_download',
        'middleware' => ['can:view,Modules\ProvBase\Entities\Contract'],
    ]);
});

Route::group(['middleware' => ['web'], 'prefix' => 'customer'], function () {
    Route::get('login', [
        'as' => 'customerLogin',
        'uses' => 'Modules\Ccc\Http\Controllers\LoginController@showLoginForm',
    ]);

    Route::post('login', [
        'as' => 'customerLogin.post',
        'uses' => 'Modules\Ccc\Http\Controllers\LoginController@login',
    ]);

    Route::post('logout', [
        'as' => 'customerLogout.post',
        'uses' => 'Modules\Ccc\Http\Controllers\LoginController@logout',
    ]);
});

Route::group(['middleware' => ['web', 'auth:ccc'], 'prefix' => 'customer'], function () {
    Route::get('', [
        'as' => 'HomeCcc',
        'uses' => 'Modules\Ccc\Http\Controllers\CccUserController@show',
    ]);

    Route::get('password', [
        'as' => 'CustomerPsw',
        'uses' => 'Modules\Ccc\Http\Controllers\CccUserController@psw_update',
    ]);

    Route::post('password', [
        'as' => 'CustomerPsw',
        'uses' => 'Modules\Ccc\Http\Controllers\CccUserController@psw_update',
    ]);

    Route::get('home/download/{invoice}', [
        'as' => 'Customer.Download',
        'uses' => 'Modules\Ccc\Http\Controllers\CccUserController@download',
    ]);
});
