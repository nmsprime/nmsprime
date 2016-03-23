<?php namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\BillingBase;

class BillingBaseController extends \BaseModuleController {
	
	public function index()
	{
		return view('billingbase::index');
	}

	public function get_form_fields($model = null)
	{
		return [
			array('form_type' => 'text', 'name' => 'rcd', 'description' => 'Requested Collection Day', 'options' => ['placeholder' => 'YYYY-MM-DD']),
			array('form_type' => 'select', 'name' => 'currency', 'description' => 'Currency', 'value' => BillingBase::getPossibleEnumValues('currency')),
			array('form_type' => 'text', 'name' => 'tax', 'description' => 'Tax'),
		];
	}
	
}