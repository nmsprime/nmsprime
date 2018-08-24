<?php

BaseRoute::group([], function () {
    BaseRoute::resource('Mpr', 'Modules\HfcCustomer\Http\Controllers\MprController');
    BaseRoute::resource('MprGeopos', 'Modules\HfcCustomer\Http\Controllers\MprGeoposController');

    BaseRoute::get('Customer/{field}/{search}', [
        'as' => 'CustomerTopo.show',
        'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show',
        'middleware' => ['can:view,Modules\HfcCustomer\Entities\Mpr'],
    ]);

    BaseRoute::get('Customer/prox', [
        'as' => 'CustomerTopo.show_prox',
        'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_prox',
        'middleware' => ['can:view,Modules\HfcCustomer\Entities\Mpr'],
    ]);

    BaseRoute::get('Customer/impaired', [
        'as' => 'CustomerTopo.show_impaired',
        'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_impaired',
        'middleware' => ['can:view,Modules\HfcCustomer\Entities\Mpr'],
    ]);

    BaseRoute::get('CustomerRect/{x1}/{x2}/{y1}/{y2}', [
        'as' => 'CustomerRect.show',
        'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_rect',
        'middleware' => ['can:view,Modules\HfcCustomer\Entities\MprGeopos'],
    ]);

    BaseRoute::get('CustomerPoly/{poly}', [
        'as' => 'CustomerPoly.show',
        'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_poly',
        'middleware' => ['can:view,Modules\HfcCustomer\Entities\MprGeopos'],
    ]);

    BaseRoute::get('CustomerModem/{topo_dia}/{ids}', [
        'as' => 'CustomerModem.show',
        'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@show_modem_ids',
        'middleware' => ['can:view,Modules\HfcCustomer\Entities\Mpr'],
    ]);

    BaseRoute::get('Mpr/{id}/update_geopos/{new_gp}', [
        'as' => 'Mpr.update_geopos',
        'uses' => 'Modules\HfcCustomer\Http\Controllers\MprController@update_geopos',
        'middleware' => ['can:update,Modules\HfcCustomer\Entities\MprGeopos'],
    ]);

    BaseRoute::get('data/hfccustomer/{type}/{filename}', [
        'as' => 'HfcCustomer.get_file',
        'uses' => 'Modules\HfcCustomer\Http\Controllers\CustomerTopoController@get_file',
        'middleware' => ['can:view,Modules\HfcCustomer\Entities\Mpr'],
    ]);
});
