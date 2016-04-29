<?php 
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\Product;
use Modules\BillingBase\Entities\CostCenter;
use Config;

class ItemController extends \BaseModuleController {

	/**
	 * defines the formular fields for the edit and create view
	 */
	public function get_form_fields($model = null)
	{
		if (!$model)
			$model = new Item;

		$products = Product::select('id', 'type', 'name')->get()->all();

		$prods = $model->html_list($products, 'name');
		$ccs = array_merge([''], $model->html_list(CostCenter::all(), 'name'));

		foreach ($products as $p)
			$types[$p->id] = $p->type; 

		// dd($prods, $types);

		$cnt[0] = null;
		// 	$b[date('Y-m-01', strtotime("now +$i months"))] = date('Y-m', strtotime("now +$i months"));
		for ($i=0; $i < 10; $i++)
			$cnt[$i+1] = $i+1;


		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract', 'value' => $model->contract(), 'hidden' => '1'),
			array('form_type' => 'select', 'name' => 'product_id', 'description' => 'Product', 'value' => $prods, 'select' => $types, 'help' => 'All fields besides Billing Cycle have to be cleared before a type change! Otherwise items can not be saved in most cases'), 
			array('form_type' => 'select', 'name' => 'count', 'description' => 'Count', 'value' => $cnt, 'select' => 'Device Other'),
			array('form_type' => 'text', 'name' => 'valid_from', 'description' => 'Valid from', 'options' => ['placeholder' => 'YYYY-MM-DD'], 'help' => 'for One Time Payments the fields can be used to split payment - Only Y-M is considered then!'),
			array('form_type' => 'text', 'name' => 'valid_to', 'description' => 'Valid to', 'options' => ['placeholder' => 'YYYY-MM-DD']),
			array('form_type' => 'text', 'name' => 'credit_amount', 'description' => 'Credit Amount', 'select' => 'Credit'),
			array('form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'Cost Center (optional)', 'value' => $ccs),
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