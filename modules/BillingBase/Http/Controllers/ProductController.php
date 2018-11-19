<?php

namespace Modules\BillingBase\Http\Controllers;

use Modules\ProvBase\Entities\Qos;
use Modules\BillingBase\Entities\Product;
use Modules\ProvVoip\Entities\PhoneTariff;
use Modules\BillingBase\Entities\CostCenter;

class ProductController extends \BaseController
{
    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Product;
        }

        // don't use array_merge for this because that reassignes the index!
        $qos_val = $this->_add_empty_first_element_to_options($model->html_list(Qos::all(), 'name'), null);
        $ccs = $this->_add_empty_first_element_to_options($model->html_list(CostCenter::all(), 'name'));
        $sales_tariffs = $this->_add_empty_first_element_to_options(PhoneTariff::get_sale_tariffs());
        $purchase_tariffs = $this->_add_empty_first_element_to_options(PhoneTariff::get_purchase_tariffs());

        // Internet, Voip, TV, Device, Credit, Other
        $types = $type_selects = Product::getPossibleEnumValues('type', true);
        unset($type_selects[0]);

        // label has to be the same like column in sql table
        return [
            ['form_type' => 'text', 'name' => 'name', 'description' => 'Name', 'help' => trans('helper.Product_Name')],
            ['form_type' => 'select', 'name' => 'type', 'description' => 'Type', 'value' => $types, 'select' => $type_selects, 'options' => ['translate' => true], 'help' => trans('helper.Product_Type')],
            ['form_type' => 'select', 'name' => 'qos_id', 'description' => 'Qos (Data Rate)', 'value' => $qos_val, 'select' => 'Internet'],
            ['form_type' => 'select', 'name' => 'voip_sales_tariff_id', 'description' => 'Phone Sales Tariff', 'value' => $sales_tariffs, 'select' => 'Voip'],
            ['form_type' => 'select', 'name' => 'voip_purchase_tariff_id', 'description' => 'Phone Purchase Tariff', 'value' => $purchase_tariffs, 'select' => 'Voip'],
            ['form_type' => 'select', 'name' => 'billing_cycle', 'description' => 'Billing Cycle', 'value' => Product::getPossibleEnumValues('billing_cycle'), 'options' => ['translate' => true]],
            ['form_type' => 'text', 'name' => 'maturity_min', 'description' => 'Minimum Maturity', 'select' => 'Internet Voip', 'help' => trans('helper.Product_maturity_min')],         // Laufzeit, tarif life time
            ['form_type' => 'text', 'name' => 'maturity', 'description' => 'Maturity', 'select' => 'Internet Voip', 'help' => trans('helper.Product_maturity')],         // Laufzeit, tarif life time
            ['form_type' => 'text', 'name' => 'period_of_notice', 'description' => 'Period of Notice', 'select' => 'Internet Voip', 'help' => trans('helper.Product_pod')], 		// Kündigungsfrist
            ['form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'Cost Center (optional)', 'value' => $ccs],
            ['form_type' => 'text', 'name' => 'price', 'description' => 'Price (Net)'],
            array_merge(['form_type' => 'checkbox', 'name' => 'tax', 'description' => 'with Tax calculation ?', 'select' => 'TV Credit'], $model->tax === null ? ['checked' => true, 'value' => 1] : []),
            ['form_type' => 'text', 'name' => 'email_count', 'description' => 'No. of email addresses', 'select' => 'Internet', 'hidden' => 1],
            ['form_type' => 'checkbox', 'name' => 'bundled_with_voip', 'description' => 'Bundled with VoIP product?', 'select' => 'Internet', 'help' => trans('helper.Product_bundle')],
        ];
    }

    /**
     * @author Nino Ryschawy
     */
    public function prepare_rules($rules, $data)
    {
        if (in_array($data['type'], ['Internet', 'Voip', 'TV'])) {
            $rules['billing_cycle'] = 'In:Monthly,Quarterly,Yearly';
        }

        return parent::prepare_rules($rules, $data);
    }

    public function prepare_input($data)
    {
        switch ($data['type']) {
            case 'Credit':
            case 'Device':
            case 'Other':
            case 'TV':
                $data['qos_id'] = 0;
            case 'Internet':
                $data['voip_sales_tariff_id'] = 0;
                $data['voip_purchase_tariff_id'] = 0;
                break;

            case 'Voip':
                $data['qos_id'] = 0;
                break;

            default:
                break;
        }

        $data = parent::prepare_input($data);

        // remove spaces and use upper case for content of these fields
        $fields = ['maturity', 'maturity_min', 'period_of_notice'];

        foreach ($fields as $field) {
            $data[$field] = $data[$field] ? strtoupper(str_replace(' ', '', $data[$field])) : null;
        }

        return $data;
    }
}
