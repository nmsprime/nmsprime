<?php

// Route::group(['middleware' => 'web', 'prefix' => 'billingbase', 'namespace' => 'Modules\BillingBase\Http\Controllers'], function()
// {
// 	Route::get('/', 'BillingBaseController@index');
// });

CoreRoute::group([], function() {

	CoreRoute::resource('Product', 'Modules\BillingBase\Http\Controllers\ProductController');
	CoreRoute::resource('Item', 'Modules\BillingBase\Http\Controllers\ItemController');
	CoreRoute::resource('SepaMandate', 'Modules\BillingBase\Http\Controllers\SepaMandateController');
	CoreRoute::resource('SepaAccount', 'Modules\BillingBase\Http\Controllers\SepaAccountController');
	CoreRoute::resource('CostCenter', 'Modules\BillingBase\Http\Controllers\CostCenterController');
	CoreRoute::resource('Company', 'Modules\BillingBase\Http\Controllers\CompanyController');
	CoreRoute::resource('Salesman', 'Modules\BillingBase\Http\Controllers\SalesmanController');

	// Route::get('BillingBase', array('as' => 'BillingBase.edit', 'uses' => 'Modules\BillingBase\Http\Controllers\BillingBaseController@edit'));
	CoreRoute::resource('BillingBase', 'Modules\BillingBase\Http\Controllers\BillingBaseController');

});