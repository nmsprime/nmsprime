<?php

namespace Modules\PropertyManagement\Http\Controllers;

class ApartmentController extends \BaseController
{
    /**
     * Defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        $realties = selectList('realty', ['number', 'name'], true, ' - ');

        // label has to be the same like column in sql table
        $fields = [
            ['form_type' => 'select', 'name' => 'realty_id', 'description' => 'Realty', 'value' => $realties],
            ['form_type' => 'text', 'name' => 'number', 'description' => 'Number'],
            ['form_type' => 'text', 'name' => 'floor', 'description' => 'Floor'],

            ['form_type' => 'checkbox', 'name' => 'connected', 'description' => trans('dt_header.apartment.connected')],
            ['form_type' => 'checkbox', 'name' => 'occupied', 'description' => trans('dt_header.apartment.occupied')],

            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
        ];

        return $fields;
    }
}
