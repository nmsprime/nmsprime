<?php
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\CostCenter;
use Modules\BillingBase\Entities\SepaAccount;

class CostCenterController extends \BaseController {

    /**
     * defines the formular fields for the edit and create view
     */
	public function view_form_fields($model = null)
	{
		if (!$model)
			$model = new CostCenter;

		$list = array_merge([''], $model->html_list(SepaAccount::all(), 'name'));
		$months[0] = null;
		for($i=1; $i<13;$i++)
			$months[$i] = $i;

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name'),
			array('form_type' => 'text', 'name' => 'number', 'description' => 'Number'),
			array('form_type' => 'select', 'name' => 'sepa_account_id', 'description' => 'Associated SEPA Account', 'value' => $list),
			array('form_type' => 'select', 'name' => 'billing_month', 'description' => 'Month to create Bill', 'value' => $months, 'help' => 'Default: 6 (June) - if not set'),
			array('form_type' => 'text', 'name' => 'invoice_headline', 'description' => 'Invoice Headline', 'help' => 'Replaces Headline in Invoices created for this Costcenter'),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'),
		);
	}


}