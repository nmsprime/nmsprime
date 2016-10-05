<?php
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\Billingbase\Entities\Product;
use Modules\BillingBase\Entities\CostCenter;
use Modules\ProvBase\Entities\Qos;
use Modules\ProvVoip\Entities\PhoneTariff;

class ProductController extends \BaseController {

    /**
     * defines the formular fields for the edit and create view
     */
	public function view_form_fields($model = null)
	{

		if (!$model)
			$model = new Product;

		// the options should start with a 0 entry which is chosen if nothing is given explicitely
		// (watch $this->prepare_rules())
		// don't use array_merge for this because that reassignes the index!
		$qos_val = $this->_add_empty_first_element_to_options($model->html_list(Qos::all(), 'name'), null);
		$ccs = $this->_add_empty_first_element_to_options($model->html_list(CostCenter::all(), 'name'));
		$sales_tariffs = $this->_add_empty_first_element_to_options(PhoneTariff::get_sale_tariffs());
		$purchase_tariffs = $this->_add_empty_first_element_to_options(PhoneTariff::get_purchase_tariffs());

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
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name', 'help' => trans('helper.Product_Name')),
			array('form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => $types, 'select' => $type_selects, 'help' => trans('helper.Product_Type')),
			array('form_type' => 'select', 'name' => 'qos_id', 'description' => 'Qos (Data Rate)', 'value' => $qos_val, 'select' => 'Internet'),
			array('form_type' => 'select', 'name' => 'voip_sales_tariff_id', 'description' => 'Phone Sales Tariff', 'value' => $sales_tariffs, 'select' => 'Voip'),
			array('form_type' => 'select', 'name' => 'voip_purchase_tariff_id', 'description' => 'Phone Purchase Tariff', 'value' => $purchase_tariffs, 'select' => 'Voip'),
			array('form_type' => 'select', 'name' => 'billing_cycle', 'description' => 'Billing Cycle' , 'value' => Product::getPossibleEnumValues('billing_cycle')),
			array('form_type' => 'text', 'name' => 'cycle_count', 'description' => 'Number of Cycles', 'select' => 'Device Other', 'help' => trans('helper.Product_Number_of_Cycles')),
			array('form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'Cost Center (optional)', 'value' => $ccs),
			array('form_type' => 'text', 'name' => 'price', 'description' => 'Price (Net)', 'select' => 'Internet Voip TV Device Other'),
			$tax,
			array('form_type' => 'checkbox', 'name' => 'bundled_with_voip', 'description' => 'Bundled with VoIP product?', 'select' => 'Internet'),
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
				$rules['voip_sales_tariff_id'] = 'In:0';
				$rules['voip_purchase_tariff_id'] = 'In:0';
				$rules['price'] = 'In:"",0';
				break;

			case 'Device':
				$rules['qos_id'] = 'In:0';
				$rules['voip_sales_tariff_id'] = 'In:0';
				$rules['voip_purchase_tariff_id'] = 'In:0';
				break;

			case 'Internet':
				$rules['billing_cycle'] = 'In:Monthly,Quarterly,Yearly';
				$rules['voip_sales_tariff_id'] = 'In:0';
				$rules['voip_purchase_tariff_id'] = 'In:0';
				break;

			case 'Other':
				$rules['qos_id'] = 'In:0';
				$rules['voip_sales_tariff_id'] = 'In:0';
				$rules['voip_purchase_tariff_id'] = 'In:0';
				break;

			case 'TV':
				$rules['billing_cycle'] = 'In:Monthly,Quarterly,Yearly';
				$rules['qos_id'] = 'In:0';
				$rules['voip_sales_tariff_id'] = 'In:0';
				$rules['voip_purchase_tariff_id'] = 'In:0';
				break;

			case 'Voip':
				$rules['billing_cycle'] = 'In:Monthly,Quarterly,Yearly';
				$rules['qos_id'] = 'In:0';
				break;

			default:
				break;
		}

		return parent::prepare_rules($rules, $data);
	}

}
