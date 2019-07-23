<?php

namespace Modules\PropertyManagement\Http\Controllers;

class RealtyController extends \BaseController
{
    /**
     * Defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        // label has to be the same like column in sql table
        $fields = [
            ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'],
            ['form_type' => 'text', 'name' => 'number', 'description' => 'Number'],
            ['form_type' => 'text', 'name' => 'street', 'description' => 'Street'],
            ['form_type' => 'text', 'name' => 'house_nr', 'description' => 'House number'],
            ['form_type' => 'text', 'name' => 'zip', 'description' => 'Zip'],
            ['form_type' => 'text', 'name' => 'city', 'description' => 'City', 'space' => 1],

            ['form_type' => 'checkbox', 'name' => 'group_contract', 'description' => trans('dt_header.group_contract')],
            ['form_type' => 'checkbox', 'name' => 'concession_agreement', 'description' => trans('dt_header.realty.concession_agreement')],
            ['form_type' => 'text', 'name' => 'agreement_from', 'description' => trans('dt_header.realty.agreement_from'), 'checkbox' => 'show_on_concession_agreement'],
            ['form_type' => 'text', 'name' => 'agreement_to', 'description' => trans('dt_header.realty.agreement_to'), 'checkbox' => 'show_on_concession_agreement'],
            ['form_type' => 'text', 'name' => 'last_restoration', 'description' => trans('dt_header.realty.last_restoration')],
            ['form_type' => 'text', 'name' => 'administration', 'description' => trans('dt_header.realty.administration')],
            ['form_type' => 'text', 'name' => 'expansion_degree', 'description' => trans('dt_header.expansion_degree'), 'space' => 1],

            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
        ];

        return $fields;
    }
}
