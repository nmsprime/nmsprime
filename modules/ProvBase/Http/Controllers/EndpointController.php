<?php

namespace Modules\ProvBase\Http\Controllers;

use Modules\ProvBase\Entities\{Endpoint, Modem};

class EndpointController extends \BaseController {

	protected $index_create_allowed = false;

    /**
     * defines the formular fields for the edit and create view
     */
	public function view_form_fields($model = null)
	{
		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'hostname', 'description' => 'Hostname'),
			array('form_type' => 'text', 'name' => 'modem_id', 'description' => 'Modem', 'hidden' => 1),
			array('form_type' => 'text', 'name' => 'mac', 'description' => 'MAC Address', 'options' => ['placeholder' => 'AA:BB:CC:DD:EE:FF'], 'help' => trans('helper.mac_formats')),
			array('form_type' => 'checkbox', 'name' => 'fixed_ip', 'description' => 'Fixed IP', 'value' => '1', 'help' => trans('helper.fixed_ip_warning')),
			array('form_type' => 'text', 'name' => 'ip', 'description' => 'Fixed IP', 'checkbox' => 'show_on_fixed_ip'),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'),

		);
	}

	protected function prepare_input_post_validation($data)
	{
		return unify_mac($data);
	}

	function prepare_rules($rules, $data)
	{
		// as we are setting IP to “null” if no fixed IP is used: remove rule in this case
		if ($data['fixed_ip'] == '0')
			$rules['ip'] = '';

		return $rules;
	}

}
