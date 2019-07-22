<?php

BaseRoute::group([], function () {
    BaseRoute::resource('Node', 'Modules\PropertyManagement\Http\Controllers\NodeController');
});
