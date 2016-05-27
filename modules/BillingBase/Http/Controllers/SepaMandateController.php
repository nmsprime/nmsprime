<?php
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\SepaMandate;
use Modules\BillingBase\Entities\SepaAccount;

class SepaMandateController extends \BaseModuleController {

	protected $index_create_allowed = false;

    /**
     * defines the formular fields for the edit and create view
     */
	public function view_form_fields($model = null)
	{
		if (!$model)
			$model = new SepaMandate;

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract', 'hidden' => '1'),
			array('form_type' => 'text', 'name' => 'reference', 'description' => 'Reference Number', 'create' => '1'),
			array('form_type' => 'text', 'name' => 'signature_date', 'description' => 'Date of Signature', 'options' => ['placeholder' => 'YYYY-MM-DD']),
			// $table->enum('state', ['active', 'expired', 'cancelled', 'replaced']);
			array('form_type' => 'text', 'name' => 'sepa_holder', 'description' => 'Bank Account Holder'),
			array('form_type' => 'text', 'name' => 'sepa_iban', 'description' => 'IBAN'),
			array('form_type' => 'text', 'name' => 'sepa_bic', 'description' => 'BIC'),
			array('form_type' => 'text', 'name' => 'sepa_institute', 'description' => 'Bank Institute'),
			array('form_type' => 'text', 'name' => 'sepa_valid_from', 'description' => 'Valid from', 'options' => ['placeholder' => 'YYYY-MM-DD']),
			array('form_type' => 'text', 'name' => 'sepa_valid_to', 'description' => 'Valid to', 'options' => ['placeholder' => 'YYYY-MM-DD']),
			array('form_type' => 'checkbox', 'name' => 'recurring', 'description' => 'Already a Recurring Debit?', 'value' => '1'),
		);
	}


	public function prepare_input($data)
	{
		$data['sepa_bic'] = $data['sepa_bic'] ? : SepaAccount::get_bic($data['sepa_iban']);
		$data['sepa_bic'] = strtoupper($data['sepa_bic']);

		$data['sepa_iban'] = strtoupper($data['sepa_iban']);

		return parent::prepare_input($data);
	}


	public function prepare_rules($rules, $data)
	{
		$rules['sepa_bic'] .= $data['sepa_bic'] ? '' : '|required';

		return parent::prepare_rules($rules, $data);
	}

}