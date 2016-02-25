<?php

// Route::group(['middleware' => 'web', 'prefix' => 'billingbase', 'namespace' => 'Modules\BillingBase\Http\Controllers'], function()
// {
// 	Route::get('/', 'BillingBaseController@index');
// });

Route::group(array('before' => 'auth'), function() {
	Route::resource('Price', 'Modules\BillingBase\Http\Controllers\PriceController');
	Route::get('Price/fulltextSearch', array('as' => 'Price.fulltextSearch', 'uses' => 'Modules\ProvBase\Http\Controllers\PriceController@fulltextSearch'));

});