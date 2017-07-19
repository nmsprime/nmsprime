<?php

namespace Modules\ProvVoipEnvia\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

use Modules\ProvVoipEnvia\Entities\EnviaContract;
use Modules\ProvVoip\Entities\PhonenumberManagement;
use Modules\ProvVoip\Entities\Phonenumber;
use Modules\ProvVoip\Entities\Phonetariff;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\Contract;


class EnviaContractController extends \BaseController {

	protected $index_create_allowed = false;
	protected $index_delete_allowed = false;

	/* public function index() */
	/* { */
	/* 	return view('provvoipenvia::index'); */
	/* } */


	/**
	 * defines the formular fields for the edit and create view
	 */
	public function view_form_fields($model = null) {

		$ret = array(
			array('form_type' => 'text', 'name' => 'envia_contract_reference', 'description' => 'Envia contract reference', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'envia_customer_reference', 'description' => 'Envia customer reference', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'start_date', 'description' => 'Start date', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'end_date', 'description' => 'End date', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'method', 'description' => 'Method', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'lock_level', 'description' => 'Lock level', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'variation_id', 'description' => 'Variation ID', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'tariff_id', 'description' => 'Tariff ID', 'options' => ['readonly']),
		);

		return $ret;

	}
}
