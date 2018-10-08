<?php

namespace Modules\HfcCustomer\Http\Controllers;

use Modules\HfcCustomer\Entities\Mpr;

class MprGeoposController extends \BaseController
{
    protected $index_create_allowed = false;

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        // label has to be the same like column in sql table
        return [
            ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'],
            ['form_type' => 'text', 'name' => 'x', 'description' => 'X'],
            ['form_type' => 'text', 'name' => 'y', 'description' => 'Y'],
            ['form_type' => 'select', 'name' => 'mpr_id', 'description' => 'Mpr', 'hidden' => '0', 'value' => $model->html_list(Mpr::all(), 'name')],
            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
        ];
    }
}
