<?php

namespace Modules\BillingBase\Http\Controllers;

use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\Product;
use Modules\BillingBase\Entities\CostCenter;
use Modules\BillingBase\Entities\BillingBase;

class ItemController extends \BaseController
{
    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Item;
        }

        $products = Product::select('id', 'type', 'name')->orderBy('type')->orderBy('name')->get()->all();

        $prods[0] = '';
        foreach ($products as $p) {
            $prods[$p->id] = $p->type.' - '.$p->name;
        }

        $types = [];
        foreach ($products as $p) {
            $types[$p->id] = $p->type;
        }

        // don't use array_merge for this because that reassignes the index!
        $ccs = $model->html_list(CostCenter::all(), 'name', true);

        for ($i = 1; $i < 99; $i++) {
            $cnt[$i] = $i;
        }

        // bool var to show/hide valid_from/to_fixed fields
        $fluid = ! BillingBase::first()->fluid_valid_dates;

        // label has to be the same like column in sql table
        $fields = [
            ['form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract', 'hidden' => '1'],
            ['form_type' => 'select', 'name' => 'product_id', 'description' => 'Product', 'value' => $prods, 'select' => $types, 'help' => trans('helper.Item_ProductId')],
            ['form_type' => 'select', 'name' => 'count', 'description' => 'Count', 'value' => $cnt],
            ['form_type' => 'text', 'name' => 'valid_from', 'description' => 'Start date', 'options' => ['placeholder' => 'YYYY-MM-DD'], 'help' => trans('helper.Item_ValidFrom')],
            ['form_type' => 'checkbox', 'name' => 'valid_from_fixed', 'description' => 'Active from start date', 'select' => 'Internet Voip', 'help' => trans('helper.Item_ValidFromFixed'), 'hidden' => $fluid, 'checked' => 1, 'value' => 1],
            ['form_type' => 'text', 'name' => 'valid_to', 'description' => 'Valid to', 'options' => ['placeholder' => 'YYYY-MM-DD']],
            ['form_type' => 'checkbox', 'name' => 'valid_to_fixed', 'description' => 'Valid to fixed', 'select' => 'Internet Voip', 'help' => trans('helper.Item_ValidToFixed'), 'hidden' => $fluid, 'checked' => 1, 'value' => 1],
            ['form_type' => 'text', 'name' => 'credit_amount', 'description' => 'Credit Amount', 'select' => 'Credit', 'help' => trans('helper.Item_CreditAmount')],
            ['form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'Cost Center (optional)', 'value' => $ccs],
            ['form_type' => 'text', 'name' => 'accounting_text', 'description' => 'Accounting Text (optional)'],
        ];

        // show negative credit amounts in red - Keep order of fields array or change index here!
        if ($model->credit_amount && $model->credit_amount < 0) {
            $fields[7]['options'] = ['style' => 'color:red; '];
        }

        return $fields;
    }

    /**
     * Set default Input values for empty fields / Autofill empty fields
     */
    public function prepare_input($data)
    {
        // $data['credit_amount'] = $data['credit_amount'] ? abs($data['credit_amount']) : $data['credit_amount'];
        $type = ($p = Product::find($data['product_id'])) ? $p->type : '';

        // set default valid from date to tomorrow for this product types
        // specially for Voip: Has to be created externally – and this will not be done today…
        if ($type == 'Voip') {
            $data['valid_from'] = $data['valid_from'] ?: date('Y-m-d', strtotime('next day'));
        }

        // others: set today as start date when valid_from is fixed and not set, otherwise set to tomorrow - also if valid_from is in past
        elseif ($type == 'Internet') {
            if (isset($data['valid_from_fixed']) && boolval($data['valid_from_fixed'])) {
                $data['valid_from'] = $data['valid_from'] ?: date('Y-m-d');
            } else {
                $data['valid_from'] = $data['valid_from'] > date('Y-m-d') ? $data['valid_from'] : date('Y-m-d', strtotime('next day'));
            }
        } else {
            $data['valid_from'] = $data['valid_from'] ?: date('Y-m-d');
            $data['valid_from_fixed'] = 1;
        }

        $data = parent::prepare_input($data);

        $nullable_fields = [
            'credit_amount',
        ];

        return $this->_nullify_fields($data, $nullable_fields);
    }

    /**
     * @author Nino Ryschawy
     */
    public function prepare_rules($rules, $data)
    {
        // $rules['count'] = str_replace('product_id', $data['product_id'], $rules['count']);

        // termination only allowed on last day of month
        $fix = BillingBase::select('termination_fix')->first()->termination_fix;
        if ($fix && $data['valid_to']) {
            $rules['valid_to'] .= '|In:'.date('Y-m-d', strtotime('last day of this month', strtotime($data['valid_to'])));
        }

        // TODO: simplify when it's safe that valid_to=0000-00-00 can not occure
        if ($data['valid_to'] && $data['valid_to'] != '0000-00-00') {
            $rules['valid_to'] .= '|after:'.$data['valid_from'];
        }

        // check only on creating: new tariff must start after old tariffs start date if valid tariff exists
        // - otherwise after end date of old tariff - does it?
        if (\Str::contains(\URL::previous(), '/Item/create')) {
            $p = Product::find($data['product_id']);

            if (in_array($p->type, ['Internet', 'Voip', 'TV'])) {
                $c = Contract::find($data['contract_id']);
                $tariff = $p ? $c->get_valid_tariff($p->type) : null;

                if ($tariff) {
                    // check if date is after today
                    $start = $tariff->get_start_time();
                    $rules['valid_from'] .= '|after:';
                    $rules['valid_from'] .= $tariff->valid_to && $tariff->valid_to != '0000-00-00' ? $tariff->valid_to : date('Y-m-d', $start);
                }
            }
        }

        return parent::prepare_rules($rules, $data);
    }

    public function index()
    {
        return \View::make('errors.generic');
    }

    /**
     * Show Alert when Credit Amount is negative and Customer will be charged
     */
    public function store($redirect = true)
    {
        if (\Input::get('credit_amount') && \Input::get('credit_amount') < 0) {
            \Session::put('alert', trans('messages.item_credit_amount_negative'));
        }

        // NOTE: ->with or Session::put is the same
        // return parent::store($redirect = true)->with('alert', trans('messages.item_credit_amount_negative'));

        return parent::store($redirect = true);
    }
}
