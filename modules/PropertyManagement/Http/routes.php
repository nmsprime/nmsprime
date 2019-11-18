<?php

BaseRoute::group([], function () {
    BaseRoute::resource('Apartment', 'Modules\PropertyManagement\Http\Controllers\ApartmentController');
    BaseRoute::resource('Contact', 'Modules\PropertyManagement\Http\Controllers\ContactController');
    BaseRoute::resource('Node', 'Modules\PropertyManagement\Http\Controllers\NodeController');
    BaseRoute::resource('Realty', 'Modules\PropertyManagement\Http\Controllers\RealtyController');
    Route::get('CutoffList', [
        'as' => 'CutoffList.index',
        'uses' => 'Modules\PropertyManagement\Http\Controllers\CutoffListController@index',
        'middleware' => ['web', 'can:view,Modules\PropertyManagement\Entities\Realty'],
    ]);
    Route::get('CutoffList/datatables', [
        'as' => 'CutoffList.data',
        'uses' => 'Modules\PropertyManagement\Http\Controllers\CutoffListController@index_datatables_ajax',
        'middleware' => ['web', 'can:view,Modules\PropertyManagement\Entities\Realty'],
    ]);
});
