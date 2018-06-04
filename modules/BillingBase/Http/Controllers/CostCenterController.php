<?php
namespace Modules\BillingBase\Http\Controllers;

use Nwidart\Modules\Routing\Controller;
use Modules\BillingBase\Entities\{CostCenter, SepaAccount};

class CostCenterController extends \BaseController {

    /**
     * defines the formular fields for the edit and create view
     */
	public function view_form_fields($model = null)
	{
		if (!$model)
			$model = new CostCenter;

		// the options should start with a 0 entry which is chosen if nothing is given explicitely
		// (watch $this->prepare_rules())
		// don't use array_merge for this because that reassignes the index!
		$list = $this->_add_empty_first_element_to_options($model->html_list(SepaAccount::all(), 'name'));

		for($i=0; $i<13; $i++)
			$months[$i] = $i;

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name'),
			array('form_type' => 'text', 'name' => 'number', 'description' => 'Number'),
			array('form_type' => 'select', 'name' => 'sepaaccount_id', 'description' => 'Associated SEPA Account', 'value' => $list, 'hidden' => 0),
			array('form_type' => 'select', 'name' => 'billing_month', 'description' => 'Month to create Bill', 'value' => $months, 'help' => trans('helper.CostCenter_BillingMonth')),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'),
		);
	}

}
