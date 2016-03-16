<?php 
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\Price;
use Config;

class ItemController extends \BaseModuleController {
	
	/**
	 * defines the formular fields for the edit and create view
	 */
	public function get_form_fields($model = null)
	{
		if (!$model)
			$model = new Item;

		$items = Price::where('type', '=', 'device')->orWhere('type', '=', 'other')->get();
		$b[0] = null;
		for ($i=0; $i < 24; $i++)
		{ 
			$b[date('Y-m-01', strtotime("now +$i months"))] = date('Y-m', strtotime("now +$i months"));
			if ($i < 11)
				$cnt[$i+1] = $i+1;
		}

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract', 'value' => $model->contract(), 'hidden' => '1'),
			array('form_type' => 'select', 'name' => 'price_id', 'description' => 'Item', 'value' => $model->html_list($items, 'name'), 'hidden' => '0'), 
			array('form_type' => 'select', 'name' => 'count', 'description' => 'Count', 'value' => $cnt),
			array('form_type' => 'select', 'name' => 'payment_from', 'description' => 'Payment from', 'value' => $b),
			array('form_type' => 'select', 'name' => 'payment_to', 'description' => 'Payment to (Only for One Time Payments', 'value' => $b),
			array('form_type' => 'text', 'name' => 'credit_amount', 'description' => 'Credit Amount (Additional - only for Credits)'),
			array('form_type' => 'text', 'name' => 'accounting_text', 'description' => 'Accounting Text (optional)')
		);
	}	


	/**
	 * @author Nino Ryschawy
	 */
	public function prep_rules($rules, $data)
	{
		if ($data['payment_from'])
			$rules['payment_to'] = 'required|date';
		if ($data['payment_to'])
			$rules['payment_from'] = 'required|date';
		// dd($rules, $data);

		return $rules;
	}

	public function index()
	{
		return \View::make('errors.generic');
	}
}