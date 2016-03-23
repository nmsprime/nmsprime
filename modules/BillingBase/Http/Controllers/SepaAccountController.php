<?php 
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\SepaAccount;

class SepaAccountController extends \BaseModuleController {
	
    /**
     * defines the formular fields for the edit and create view
     */
	public function get_form_fields($model = null)
	{
		if (!$model)
			$model = new SepaAccount;

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Account Name'),
			array('form_type' => 'text', 'name' => 'holder', 'description' => 'Account Holder'),
			array('form_type' => 'text', 'name' => 'creditorid', 'description' => 'Creditor ID'),
			array('form_type' => 'text', 'name' => 'iban', 'description' => 'IBAN'),
			array('form_type' => 'text', 'name' => 'bic', 'description' => 'BIC'),
			array('form_type' => 'text', 'name' => 'institute', 'description' => 'Institute'),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'),
		);
	}

	
}