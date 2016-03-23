<?php

// Route::group(['middleware' => 'web', 'prefix' => 'billingbase', 'namespace' => 'Modules\BillingBase\Http\Controllers'], function()
// {
// 	Route::get('/', 'BillingBaseController@index');
// });

Route::group(array('before' => 'auth'), function() {
	Route::resource('Product', 'Modules\BillingBase\Http\Controllers\ProductController');
	Route::resource('Item', 'Modules\BillingBase\Http\Controllers\ItemController');
	Route::resource('SepaMandate', 'Modules\BillingBase\Http\Controllers\SepaMandateController');
	Route::resource('SepaAccount', 'Modules\BillingBase\Http\Controllers\SepaAccountController');
	Route::resource('CostCenter', 'Modules\BillingBase\Http\Controllers\CostCenterController');
	Route::get('Product/fulltextSearch', array('as' => 'Product.fulltextSearch', 'uses' => 'Modules\BillingBase\Http\Controllers\ProductController@fulltextSearch'));
	Route::get('Item/fulltextSearch', array('as' => 'Item.fulltextSearch', 'uses' => 'Modules\BillingBase\Http\Controllers\ItemController@fulltextSearch'));
	Route::get('SepaMandate/fulltextSearch', array('as' => 'SepaMandate.fulltextSearch', 'uses' => 'Modules\BillingBase\Http\Controllers\SepaMandateController@fulltextSearch'));
	Route::get('SepaAccount/fulltextSearch', array('as' => 'SepaAccount.fulltextSearch', 'uses' => 'Modules\BillingBase\Http\Controllers\SepaAccountController@fulltextSearch'));
	Route::get('CostCenter/fulltextSearch', array('as' => 'CostCenter.fulltextSearch', 'uses' => 'Modules\BillingBase\Http\Controllers\CostCenterController@fulltextSearch'));

	// Route::get('BillingBase', array('as' => 'BillingBase.edit', 'uses' => 'Modules\BillingBase\Http\Controllers\BillingBaseController@edit'));
	Route::resource('BillingBase', 'Modules\BillingBase\Http\Controllers\BillingBaseController');

});