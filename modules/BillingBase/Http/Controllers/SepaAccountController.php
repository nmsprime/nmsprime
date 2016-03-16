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
			$model = new SepaMandate;

		// label has to be the same like column in sql table
		return array(
			// array('form_type' => 'text', 'name' => 'signature_date', 'description' => 'Date of Signature', 'options' => ['placeholder' => 'YYYY-MM-DD']),
		);
	}

	
}