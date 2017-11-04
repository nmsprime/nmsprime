<?php

// Route::group(['middleware' => 'web', 'prefix' => 'billingbase', 'namespace' => 'Modules\BillingBase\Http\Controllers'], function()
// {
// 	BaseRoute::get('/', 'BillingBaseController@index');
// });

BaseRoute::group([], function() {

	BaseRoute::resource('Product', 'Modules\BillingBase\Http\Controllers\ProductController');
	BaseRoute::resource('Item', 'Modules\BillingBase\Http\Controllers\ItemController');
	BaseRoute::resource('SepaMandate', 'Modules\BillingBase\Http\Controllers\SepaMandateController');
	BaseRoute::resource('SepaAccount', 'Modules\BillingBase\Http\Controllers\SepaAccountController');
	BaseRoute::resource('CostCenter', 'Modules\BillingBase\Http\Controllers\CostCenterController');
	BaseRoute::resource('Company', 'Modules\BillingBase\Http\Controllers\CompanyController');
	BaseRoute::resource('Salesman', 'Modules\BillingBase\Http\Controllers\SalesmanController');
	BaseRoute::resource('Invoice', 'Modules\BillingBase\Http\Controllers\InvoiceController');

	// BaseRoute::get('BillingBase', array('as' => 'BillingBase.edit', 'uses' => 'Modules\BillingBase\Http\Controllers\BillingBaseController@edit'));
	BaseRoute::resource('BillingBase', 'Modules\BillingBase\Http\Controllers\BillingBaseController');
	BaseRoute::resource('SettlementRun', 'Modules\BillingBase\Http\Controllers\SettlementRunController');
	Route::get('SettlementRun/download/{id}/{sepaacc}/{key}', ['as' => 'Settlement.download', 'uses' => 'Modules\BillingBase\Http\Controllers\SettlementRunController@download']);
	Route::get('SettlementRun/check_state', ['as' => 'SettlementRun.check_state', 'uses' => 'Modules\BillingBase\Http\Controllers\SettlementRunController@check_state']);
	Route::get('SettlementRun/log_dl/{id}', ['as' => 'SettlementRun.log_dl', 'uses' => 'Modules\BillingBase\Http\Controllers\SettlementRunController@download_logs']);
});
