<?php

Route::group(['middleware' => 'web', 'prefix' => 'propertymanagement', 'namespace' => 'Modules\PropertyManagement\Http\Controllers'], function()
{
    Route::get('/', 'PropertyManagementController@index');
});
