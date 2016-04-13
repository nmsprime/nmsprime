<?php 
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\Product;
use Config;

class ItemController extends \BaseModuleController {
	
	/**
	 * defines the formular fields for the edit and create view
	 */
	public function get_form_fields($model = null)
	{
		if (!$model)
			$model = new Item;

		// $items = Product::where('type', '=', 'device')->orWhere('type', '=', 'other')->orWhere('type', '=', 'credit')->get();
		$items = Product::all();
		// $b[0] = null;
		$cnt[0] = null;
		for ($i=0; $i < 24; $i++)
		{ 
		// 	$b[date('Y-m-01', strtotime("now +$i months"))] = date('Y-m', strtotime("now +$i months"));
			if ($i < 10)
				$cnt[$i+1] = $i+1;
		}

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract', 'value' => $model->contract(), 'hidden' => '1'),
			array('form_type' => 'select', 'name' => 'product_id', 'description' => 'Product', 'value' => $model->html_list($items, 'name')), 
			array('form_type' => 'select', 'name' => 'count', 'description' => 'Count', 'value' => $cnt),
			// array('form_type' => 'select', 'name' => 'valid_from', 'description' => 'Payment from', 'value' => $b),
			// array('form_type' => 'select', 'name' => 'valid_to', 'description' => 'Payment to (Only for One Time Payments)', 'value' => $b),
			array('form_type' => 'text', 'name' => 'valid_from', 'description' => 'Valid from (for One Time Payments the fields can be used to split payment)', 'options' => ['placeholder' => 'YYYY-MM-DD']),
			array('form_type' => 'text', 'name' => 'valid_to', 'description' => 'Valid to (Only Y-M is considered then)', 'options' => ['placeholder' => 'YYYY-MM-DD']),
			array('form_type' => 'text', 'name' => 'credit_amount', 'description' => 'Credit Amount (Only for Credits!)'),
			array('form_type' => 'text', 'name' => 'accounting_text', 'description' => 'Accounting Text (optional)')
		);
	}	


	/**
	 * @author Nino Ryschawy
	 */
	public function prep_rules($rules, $data)
	{
		// $tariff_prod_ids = explode(',', substr($rules['count'], strpos($rules['count'], ',')+1)); //tariffs and credits
		// if ($data['valid_from'] && !in_array($data['product_id'], $tariff_prod_ids))
		// 	$rules['valid_to'] = 'required|not_null';

		$rules['count'] = str_replace('product_id', $data['product_id'], $rules['count']);

		// dd($rules, $data);

		return $rules;
	}

	public function index()
	{
		return \View::make('errors.generic');
	}
}