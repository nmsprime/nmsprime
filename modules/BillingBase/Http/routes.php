<?php

// Route::group(['middleware' => 'web', 'prefix' => 'billingbase', 'namespace' => 'Modules\BillingBase\Http\Controllers'], function()
// {
// 	Route::get('/', 'BillingBaseController@index');
// });

BaseRoute::group([], function() {

	BaseRoute::resource('Product', 'Modules\BillingBase\Http\Controllers\ProductController');
	BaseRoute::resource('Item', 'Modules\BillingBase\Http\Controllers\ItemController');
	BaseRoute::resource('SepaMandate', 'Modules\BillingBase\Http\Controllers\SepaMandateController');
	BaseRoute::resource('SepaAccount', 'Modules\BillingBase\Http\Controllers\SepaAccountController');
	BaseRoute::resource('CostCenter', 'Modules\BillingBase\Http\Controllers\CostCenterController');
	BaseRoute::resource('Company', 'Modules\BillingBase\Http\Controllers\CompanyController');
	BaseRoute::resource('Salesman', 'Modules\BillingBase\Http\Controllers\SalesmanController');

	// Route::get('BillingBase', array('as' => 'BillingBase.edit', 'uses' => 'Modules\BillingBase\Http\Controllers\BillingBaseController@edit'));
	BaseRoute::resource('BillingBase', 'Modules\BillingBase\Http\Controllers\BillingBaseController');
	BaseRoute::resource('SettlementRun', 'Modules\BillingBase\Http\Controllers\SettlementRunController');

});