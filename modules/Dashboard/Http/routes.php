<?php

BaseRoute::group([], function () {
    BaseRoute::get('', [
        'as' => 'Dashboard.index',
        'uses' => 'Modules\Dashboard\Http\Controllers\DashboardController@index',
    ]);

    BaseRoute::get('createCSV', [
        'uses' => '\Modules\Dashboard\Http\Controllers\DashboardController@monthly_customers_csv',
    ]);
});
