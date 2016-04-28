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
	public function view_form_fields($model = null)
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
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name', 'help' => 'For Credits it is possible to assign a Type by adding the type name to the Name of the Credit - e.g.: "Credit Device"'),
			array('form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => Product::getPossibleEnumValues('type', true)),
			array('form_type' => 'select', 'name' => 'qos_id', 'description' => 'Qos (Data Rate)', 'value' => $qos_val),
			array('form_type' => 'select', 'name' => 'voip_id', 'description' => 'Phone Tariff', 'value' => [0 => '', 1 => 'Basic', 2 => 'Flat']),
			array('form_type' => 'select', 'name' => 'billing_cycle', 'description' => 'Billing Cycle', 'value' => Product::getPossibleEnumValues('billing_cycle')),
			array('form_type' => 'text', 'name' => 'cycle_count', 'description' => 'Number of Cycles'),
			array('form_type' => 'text', 'name' => 'price', 'description' => 'Price (Net)'),
			$tax,
		);
	}

	/**
	 * @author Nino Ryschawy
	 */
	public function prepare_rules($rules, $data)
	{
		// dd($data, $rules);
		switch ($data['type'])
		{
			case 'Credit':
				$rules['billing_cycle'] = 'In:Once,Monthly';
				$rules['qos_id'] = 'In:0';
				$rules['voip_id'] = 'In:0';
				$rules['price'] = 'In:"",0';
				break;

			case 'Device':
				// $rules['billing_cycle'] = 'In:Once';
				$rules['qos_id'] = 'In:0';
				$rules['voip_id'] = 'In:0';
				break;

			case 'Internet':
				$rules['voip_id'] = 'In:0';
				break;

			case 'Other':
				$rules['qos_id'] = 'In:0';
				$rules['voip_id'] = 'In:0';
				break;

			case 'TV':
				$rules['qos_id'] = 'In:0';
				$rules['voip_id'] = 'In:0';
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