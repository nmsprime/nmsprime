<?php 
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\SepaMandate;

class SepaMandateController extends \BaseModuleController {

	protected $index_create_allowed = false;
	
    /**
     * defines the formular fields for the edit and create view
     */
	public function get_form_fields($model = null)
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

	

	protected function prepare_input_post_validation($data)
	{
		if ($data['sepa_valid_from'] == '')
			$data['sepa_valid_from'] = date('Y-m-d');
		if ($data['sepa_holder'] == '')
		{
			$contract = Contract::find($data['contract_id']);
			$data['sepa_holder'] = $contract->firstname.' '.$contract->lastname;
		}
		// dd($data, $data['contract_id']);
		
		return $data;
	}
}