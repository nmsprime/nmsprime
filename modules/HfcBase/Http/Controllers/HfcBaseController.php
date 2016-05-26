<?php

namespace Modules\HfcBase\Http\Controllers;

use App\Http\Controllers\BaseModuleController;


class HfcBaseController extends BaseModuleController {

	// The Html Link Target
	protected $html_target = '';

	/**
     * defines the formular fields for the edit and create view
     */
	public function view_form_fields($model = null)
	{
		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'ro_community', 'description' => 'SNMP Read Only Community'),
			array('form_type' => 'text', 'name' => 'rw_community', 'description' => 'SNMP Read Write Community'),
			);
	}


}