<?php 
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\CostCenter;
use Modules\BillingBase\Entities\SepaAccount;

class CostCenterController extends \BaseModuleController {
	
    /**
     * defines the formular fields for the edit and create view
     */
	public function get_form_fields($model = null)
	{
		if (!$model)
			$model = new CostCenter;

		$list = array_merge([''], $model->html_list(SepaAccount::all(), 'name'));

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name'),
			array('form_type' => 'select', 'name' => 'sepa_account_id', 'description' => 'Associated SEPA Account', 'value' => $list),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'),
		);
	}

	
}