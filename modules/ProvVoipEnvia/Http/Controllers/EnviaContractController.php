<?php

namespace Modules\ProvVoipEnvia\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

use Modules\ProvVoipEnvia\Entities\EnviaContract;
use Modules\ProvVoip\Entities\PhonenumberManagement;
use Modules\ProvVoip\Entities\Phonenumber;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\Contract;


class EnviaContractController extends \BaseController {

	protected $index_create_allowed = false;
	protected $index_delete_allowed = false;


    /**
     * defines the formular fields for the edit and create view
     *
     * @return 	array
     */
	public function view_form_fields($model = null)
	{
		$ret = [
			array('form_type' => 'text', 'name' => 'external_creation_date', 'description' => 'Creation date', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'external_termination_date', 'description' => 'Termination date', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'envia_customer_reference', 'description' => 'Envia customer ID', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'envia_contract_reference', 'description' => 'Envia contract ID', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'state', 'description' => 'State', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'start_date', 'description' => 'Start date', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'end_date', 'description' => 'End date', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'prev_id', 'description' => 'Previous Envia contract ID', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'next_id', 'description' => 'Next Envia contract ID', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'lock_level', 'description' => 'Lock level', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'method', 'description' => 'Method', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'sla_id', 'description' => 'SLA ID', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'tariff_id', 'description' => 'Tariff ID', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'variation_id', 'description' => 'Variation ID', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract ID', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'modem_id', 'description' => 'Modem ID', 'options' => ['readonly']),
		];

		return $ret;
	}



	/**
	 * Overwrite base method.
	 *
	 * Here we inject the following data:
	 *	- information about needed/possible user actions
	 *	- mailto: link to Envia support as additional data
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_additional_data_for_edit_view($model) {

		$additional_data = array(
			'relations' => $model->get_relation_information(),
		);

		return $additional_data;
	}


	/* public function index() */
	/* { */
	/* 	return view('provvoipenvia::index'); */
	/* } */


}
