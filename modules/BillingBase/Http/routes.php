<?php

BaseRoute::group([], function () {
    BaseRoute::resource('Product', 'Modules\BillingBase\Http\Controllers\ProductController');
    BaseRoute::resource('Item', 'Modules\BillingBase\Http\Controllers\ItemController');
    BaseRoute::resource('SepaMandate', 'Modules\BillingBase\Http\Controllers\SepaMandateController');
    BaseRoute::resource('SepaAccount', 'Modules\BillingBase\Http\Controllers\SepaAccountController');
    BaseRoute::resource('CostCenter', 'Modules\BillingBase\Http\Controllers\CostCenterController');
    BaseRoute::resource('Company', 'Modules\BillingBase\Http\Controllers\CompanyController');
    BaseRoute::resource('Salesman', 'Modules\BillingBase\Http\Controllers\SalesmanController');
    BaseRoute::resource('Invoice', 'Modules\BillingBase\Http\Controllers\InvoiceController');
    BaseRoute::resource('NumberRange', 'Modules\BillingBase\Http\Controllers\NumberRangeController');
    BaseRoute::resource('BillingBase', 'Modules\BillingBase\Http\Controllers\BillingBaseController');
    BaseRoute::resource('SettlementRun', 'Modules\BillingBase\Http\Controllers\SettlementRunController');

    BaseRoute::get('SettlementRun/download/{id}/{sepaacc}/{key}', [
        'as' => 'SettlementRun.download',
        'uses' => 'Modules\BillingBase\Http\Controllers\SettlementRunController@download',
        'middleware' => ['can:download,Modules\BillingBase\Entities\SettlementRun'],
    ]);

    BaseRoute::get('SettlementRun/check_state/stream', [
        'as' => 'SettlementRun.check_state',
        'uses' => 'Modules\BillingBase\Http\Controllers\SettlementRunController@check_state',
        'middleware' => ['can:view,Modules\BillingBase\Entities\SettlementRun'],
    ]);

    BaseRoute::get('SettlementRun/log_dl/{id}', [
        'as' => 'SettlementRun.log_dl',
        'uses' => 'Modules\BillingBase\Http\Controllers\SettlementRunController@download_logs',
        'middleware' => ['can:download,Modules\BillingBase\Entities\SettlementRun'],
    ]);

    BaseRoute::put('SettlementRun/create_post_invoices_pdf/{id}', [
        'as' => 'SettlementRun.create_post_invoices_pdf',
        'uses' => 'Modules\BillingBase\Http\Controllers\SettlementRunController@create_post_invoices_pdf',
        'middleware' => ['can:download,Modules\BillingBase\Entities\SettlementRun'],
    ]);
});
