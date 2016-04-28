<?php namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\BillingBase;

class BillingBaseController extends \BaseModuleController {

	public $name = 'BillingBase';

	public function index()
	{
		return view('billingbase::index');
	}

	public function view_form_fields($model = null)
	{
		return [
			array('form_type' => 'text', 'name' => 'rcd', 'description' => 'Day of Requested Collection Date'),
			array('form_type' => 'select', 'name' => 'currency', 'description' => 'Currency', 'value' => BillingBase::getPossibleEnumValues('currency')),
			array('form_type' => 'text', 'name' => 'tax', 'description' => 'Tax in %'),
			array('form_type' => 'text', 'name' => 'mandate_ref_template', 'description' => 'Mandate Reference Template', 'options' => ['placeholder' => 'e.g.: String - {contract_id}']),
			array('form_type' => 'text', 'name' => 'invoice_nr_start', 'description' => 'Start Invoice Number every Year from'),
			array('form_type' => 'checkbox', 'name' => 'split', 'description' => 'Split Sepa Transfer-Types to different XML-Files'),
		];
	}

}