<?php namespace Modules\Hfccustomer\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class MprGeoposController extends \BaseModuleController {

    /**
     * defines the formular fields for the edit and create view
     */
	public function view_form_fields($model = null)
	{
		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name'),
			array('form_type' => 'text', 'name' => 'x', 'description' => 'X'),
			array('form_type' => 'text', 'name' => 'y', 'description' => 'Y'),
			array('form_type' => 'select', 'name' => 'mpr_id', 'description' => 'Tree', 'hidden' => '1'),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description')
		);
	}

}