<?php 
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\Billingbase\Entities\Product;
use Modules\BillingBase\Entities\CostCenter;
use Modules\ProvBase\Entities\Qos;

class ProductController extends \BaseModuleController {
	
    /**
     * defines the formular fields for the edit and create view
     */
	public function get_form_fields($model = null)
	{
		if (!$model)
			$model = new Product;

		$qos_val = $model->html_list(Qos::all(), 'name');
		$qos_val['0'] = null; //[0 => null];
		ksort($qos_val);

		$list = array_merge([''], $model->html_list(CostCenter::all(), 'name'));

		$tax = array('form_type' => 'checkbox', 'name' => 'tax', 'description' => 'with Tax calculation ?');
		if ($model->tax === null)
			$tax = array_merge($tax, ['checked' => true, 'value' => 1]);

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name'),
			array('form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => Product::getPossibleEnumValues('type', true)),
			array('form_type' => 'select', 'name' => 'qos_id', 'description' => 'Qos (Data Rate)', 'value' => $qos_val),
			array('form_type' => 'select', 'name' => 'voip_tariff', 'description' => 'Phone Tariff', 'value' => Product::getPossibleEnumValues('voip_tariff')),
			array('form_type' => 'select', 'name' => 'billing_cycle', 'description' => 'Billing Cycle', 'value' => Product::getPossibleEnumValues('billing_cycle')),
			array('form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'Cost Center (optional assignment)', 'value' => $list),
			array('form_type' => 'text', 'name' => 'price', 'description' => 'Price (Net)'),
			$tax,
		);
	}

	/**
	 * @author Nino Ryschawy
	 */
	public function prep_rules($rules, $data)
	{
		switch ($data['type']) 
		{
			case 'Credit':
				$rules['billing_cycle'] = 'In:Once,Monthly';
				$rules['qos_id'] = 'In:0';
				$rules['voip_tariff'] = 'In:""';
				$rules['price'] = 'In:"",0';
				break;

			case 'Device':
				$rules['billing_cycle'] = 'In:Once';
				$rules['qos_id'] = 'In:0';
				$rules['voip_tariff'] = 'In:""';
				break;

			case 'Internet':
				$rules['voip_tariff'] = 'In:""';
				break;
			
			case 'Other':
				$rules['qos_id'] = 'In:0';
				$rules['voip_tariff'] = 'In:""';
				break;

			case 'TV':
				$rules['qos_id'] = 'In:0';
				$rules['voip_tariff'] = 'In:""';
				break;

			case 'Voip':
				$rules['qos_id'] = 'In:0';
				break;

			default:
				break;
		}

		foreach ($rules as $key => $value)
		{
			if (strpos($value, 'required_if') !== false)
				$rules[$key] .= '|not_null';
		}

		// dd($rules, $data);
		return $rules;
	}

}