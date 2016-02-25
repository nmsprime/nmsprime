<?php namespace Modules\Hfccustomer\Http\Controllers;

use Pingpong\Modules\Routing\Controller;

class MprController extends \BaseModuleController {

    /**
     * defines the formular fields for the edit and create view
     */
	public function get_form_fields($model = null)
	{
		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name'),
			array('form_type' => 'text', 'name' => 'value', 'description' => 'Value (deprecated)'),
			array('form_type' => 'select', 'name' => 'tree_id', 'description' => 'Tree', 'hidden' => '1'),
			array('form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => 
				array(1 => 'position rectangle', 2 => 'position polygon', 3 => 'nearest amp/node object', 4 => 'assosicated upstream interface', 5 => 'cluster (deprecated)')),

			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description')
		);
	}
	
}