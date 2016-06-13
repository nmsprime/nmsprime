<?php

Route::group(['middleware' => 'web', 'prefix' => 'voipmon', 'namespace' => 'Modules\VoipMon\Http\Controllers'], function()
{
	Route::get('/', 'VoipMonController@index');
});