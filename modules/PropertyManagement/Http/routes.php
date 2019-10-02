<?php

BaseRoute::group([], function () {
    BaseRoute::resource('Apartment', 'Modules\PropertyManagement\Http\Controllers\ApartmentController');
    BaseRoute::resource('Contact', 'Modules\PropertyManagement\Http\Controllers\ContactController');
    BaseRoute::resource('Node', 'Modules\PropertyManagement\Http\Controllers\NodeController');
    BaseRoute::resource('Realty', 'Modules\PropertyManagement\Http\Controllers\RealtyController');
});
