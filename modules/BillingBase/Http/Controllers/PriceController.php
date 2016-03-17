<?php 
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\Billingbase\Entities\Price;
use Modules\BillingBase\Entities\CostCenter;
use Modules\ProvBase\Entities\Qos;

class PriceController extends \BaseModuleController {
	
    /**
     * defines the formular fields for the edit and create view
     */
	public function get_form_fields($model = null)
	{
		if (!$model)
			$model = new Price;

		$qos_val = $model->html_list(Qos::all(), 'name');
		$qos_val['0'] = null; //[0 => null];
		ksort($qos_val);

		$list = array_merge([''], $model->html_list(CostCenter::all(), 'name'));

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name'),
			array('form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => Price::getPossibleEnumValues('type', true)),
			array('form_type' => 'select', 'name' => 'qos_id', 'description' => 'Qos (Data Rate)', 'value' => $qos_val),
			array('form_type' => 'select', 'name' => 'voip_tariff', 'description' => 'Phone Tariff', 'value' => Price::getPossibleEnumValues('voip_tariff')),
			array('form_type' => 'select', 'name' => 'billing_cycle', 'description' => 'Billing Cycle', 'value' => Price::getPossibleEnumValues('billing_cycle')),
			array('form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'Cost Center', 'value' => $list),
			array('form_type' => 'text', 'name' => 'price', 'description' => 'Price'),
		);
	}

	/**
	 * @author Nino Ryschawy
	 */
	public function prep_rules($rules, $data)
	{
		if ($data['type'] == 'TV')
		{
			$rules['qos_id'] = 'In:0';
			$rules['voip_tariff'] = 'In:""';
		}
		// dd($rules, $data);

		return $rules;
	}

}