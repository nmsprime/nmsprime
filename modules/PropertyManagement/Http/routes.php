<?php

BaseRoute::group([], function () {
    BaseRoute::resource('Apartment', 'Modules\PropertyManagement\Http\Controllers\ApartmentController');
    BaseRoute::resource('Node', 'Modules\PropertyManagement\Http\Controllers\NodeController');
    BaseRoute::resource('Realty', 'Modules\PropertyManagement\Http\Controllers\RealtyController');
});
