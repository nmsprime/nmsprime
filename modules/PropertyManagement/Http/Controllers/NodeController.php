<?php

namespace Modules\PropertyManagement\Http\Controllers;

class NodeController extends \BaseController
{
    /**
     * Defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        // label has to be the same like column in sql table
        $fields = [
            ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'],
            ['form_type' => 'text', 'name' => 'street', 'description' => 'Street'],
            ['form_type' => 'text', 'name' => 'house_nr', 'description' => 'House number'],
            ['form_type' => 'text', 'name' => 'zip', 'description' => 'Zip'],
            ['form_type' => 'text', 'name' => 'city', 'description' => 'City', 'space' => 1],
            ['form_type' => 'text', 'name' => 'type', 'description' => 'Type of signal'],
            ['form_type' => 'checkbox', 'name' => 'headend', 'description' => 'Headend'],
        ];

        if (\Module::collections()->has('HfcReq')) {
            $netelement = new \Modules\HfcReq\Entities\NetElement;
            $netelements = $netelement->getParentList();

            $fields[] = ['form_type' => 'select', 'name' => 'netelement_id', 'description' => 'NetElement', 'value' => $netelements];
        }

        $fields[] = ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'];

        return $fields;
    }
}
