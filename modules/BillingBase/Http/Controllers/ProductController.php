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

		$qos_val = array_merge([null], $model->html_list(Qos::all(), 'name'));
		$ccs = array_merge([''], $model->html_list(CostCenter::all(), 'name'));

		$tax = array('form_type' => 'checkbox', 'name' => 'tax', 'description' => 'with Tax calculation ?', 'select' => 'TV');
		if ($model->tax === null)
			$tax = array_merge($tax, ['checked' => true, 'value' => 1]);

		// Internet, Voip, TV, Device, Credit, Other
		$types = $type_selects = Product::getPossibleEnumValues('type', true);
		unset($type_selects[0]);

		// label has to be the same like column in sql table
		return array(
			// TODO: pre select field for product types -> smaller list of possible products to choose from
			// array('form_type' => 'text', 'name' => 'type_pre_choice', 'description' => 'Price (Net)', 'select' => 'Internet Voip TV Device Other'),
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name', 'help' => 'For Credits it is possible to assign a Type by adding the type name to the Name of the Credit - e.g.: \'Credit Device\''),
			array('form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => $types, 'select' => $type_selects, 'help' => 'All fields besides Billing Cycle have to be cleared before a type change! Otherwise products can not be saved in most cases'),
			array('form_type' => 'select', 'name' => 'qos_id', 'description' => 'Qos (Data Rate)', 'value' => $qos_val, 'select' => 'Internet'),
			array('form_type' => 'select', 'name' => 'voip_id', 'description' => 'Phone Tariff', 'value' => [0 => '', 1 => 'Basic', 2 => 'Flat'], 'select' => 'Voip'),
			array('form_type' => 'select', 'name' => 'billing_cycle', 'description' => 'Billing Cycle', 'value' => Product::getPossibleEnumValues('billing_cycle')),
			array('form_type' => 'text', 'name' => 'cycle_count', 'description' => 'Number of Cycles', 'select' => 'Device Other'),
			array('form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'Cost Center (optional)', 'value' => $ccs),
			array('form_type' => 'text', 'name' => 'price', 'description' => 'Price (Net)', 'select' => 'Internet Voip TV Device Other'),
			$tax,
		);
	}

	/**
	 * @author Nino Ryschawy
	 */
	public function prep_rules($rules, $data)
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
				$rules['qos_id'] = 'In:0';
				$rules['voip_id'] = 'In:0';
				break;

			case 'Internet':
				$rules['billing_cycle'] = 'In:Monthly,Quarterly,Yearly';
				$rules['voip_id'] = 'In:0';
				break;
			
			case 'Other':
				$rules['qos_id'] = 'In:0';
				$rules['voip_id'] = 'In:0';
				break;

			case 'TV':
				$rules['billing_cycle'] = 'In:Monthly,Quarterly,Yearly';
				$rules['qos_id'] = 'In:0';
				$rules['voip_id'] = 'In:0';
				break;

			case 'Voip':
				$rules['billing_cycle'] = 'In:Monthly,Quarterly,Yearly';
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