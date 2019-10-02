<?php

namespace Modules\PropertyManagement\Http\Controllers;

class ContactController extends \BaseController
{
    /**
     * Defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        // $nodes = selectList('node', 'name', true);

        // label has to be the same like column in sql table
        $fields = [
            ['form_type' => 'text', 'name' => 'firstname1', 'description' => trans('messages.Firstname').' 1'],
            ['form_type' => 'text', 'name' => 'lastname1', 'description' => trans('messages.Lastname').' 1'],
            ['form_type' => 'text', 'name' => 'firstname2', 'description' => trans('messages.Firstname').' 2'],
            ['form_type' => 'text', 'name' => 'lastname2', 'description' => trans('messages.Lastname').' 2'],
            ['form_type' => 'text', 'name' => 'company', 'description' => 'Company', 'space' => 1],

            ['form_type' => 'text', 'name' => 'tel', 'description' => 'Phone'],
            ['form_type' => 'text', 'name' => 'tel_private', 'description' => 'Phone private'],
            ['form_type' => 'text', 'name' => 'email1', 'description' => trans('messages.E-Mail Address').' 1'],
            ['form_type' => 'text', 'name' => 'email2', 'description' => trans('messages.E-Mail Address').' 2', 'space' => 1],

            ['form_type' => 'checkbox', 'name' => 'administration', 'description' => trans('propertymanagement::view.administration'), 'help' => trans('propertymanagement::help.administration'), 'space' => 1],

            ['form_type' => 'text', 'name' => 'street', 'description' => 'Street', 'autocomplete' => []],
            ['form_type' => 'text', 'name' => 'house_nr', 'description' => 'House number'],
            ['form_type' => 'text', 'name' => 'zip', 'description' => 'Zip', 'autocomplete' => []],
            ['form_type' => 'text', 'name' => 'city', 'description' => 'City', 'autocomplete' => []],
            ['form_type' => 'text', 'name' => 'district', 'description' => 'District', 'autocomplete' => [], 'space' => 1],
        ];

        return $fields;
    }
}
