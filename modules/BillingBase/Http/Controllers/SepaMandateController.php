<?php

namespace Modules\BillingBase\Http\Controllers;

use Modules\BillingBase\Entities\CostCenter;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\SepaMandate;

class SepaMandateController extends \BaseController
{
    protected $index_create_allowed = false;

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new SepaMandate;
        }

        if (! $model->exists) {
            $contract = \Modules\ProvBase\Entities\Contract::find(\Input::get('contract_id'));
            $model->contract = $contract;

            $model->reference = self::build_mandate_ref($model);
            $model->signature_date = date('Y-m-d');
            $model->holder = $contract->firstname.' '.$contract->lastname;
            $model->valid_from = date('Y-m-d');
        }

        // label has to be the same like column in sql table
        return [
            ['form_type' => 'text', 'name' => 'reference', 'description' => 'Reference Number', 'create' => '1'],
            ['form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract', 'hidden' => '1'],
            ['form_type' => 'text', 'name' => 'holder', 'description' => 'Bank Account Holder'],
            ['form_type' => 'text', 'name' => 'iban', 'description' => 'IBAN'],
            ['form_type' => 'text', 'name' => 'bic', 'description' => 'BIC'],
            ['form_type' => 'text', 'name' => 'institute', 'description' => 'Bank Institute', 'space' => 1],
            ['form_type' => 'text', 'name' => 'signature_date', 'description' => 'Date of Signature', 'options' => ['placeholder' => 'YYYY-MM-DD']],
            ['form_type' => 'text', 'name' => 'valid_from', 'description' => 'Valid from', 'options' => ['placeholder' => 'YYYY-MM-DD']],
            ['form_type' => 'text', 'name' => 'valid_to', 'description' => 'Valid to', 'options' => ['placeholder' => 'YYYY-MM-DD'], 'space' => 1],
            ['form_type' => 'checkbox', 'name' => 'disable', 'description' => 'Disable temporary', 'value' => '1'],
            ['form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'CostCenter', 'value' => $model->html_list(CostCenter::all(), 'name', true), 'help' => trans('helper.sm_cc')],
            ['form_type' => 'select', 'name' => 'state', 'description' => 'State', 'value' => SepaMandate::getPossibleEnumValues('state'), 'space' => 1],
            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
        ];
    }

    public function prepare_input($data)
    {
        $data['bic'] = $data['bic'] ?: SepaAccount::get_bic($data['iban']);
        $data['bic'] = strtoupper(str_replace(' ', '', $data['bic']));
        $data['iban'] = strtoupper(str_replace(' ', '', $data['iban']));
        // $data['signature_date'] = $data['signature_date'] ? : date('Y-m-d');

        $data = parent::prepare_input($data);

        // set this to null if no value is given
        $data = $this->_nullify_fields($data, ['costcenter_id', 'valid_to']);

        return $data;
    }

    public function prepare_rules($rules, $data)
    {
        // don't let BIC be empty when it's not found automatically (in prepare_input())
        if (! $data['bic']) {
            $rules['bic'] .= '|required';
        }

        return parent::prepare_rules($rules, $data);
    }

    /**
     * Replaces placeholders from in Global Config defined mandate reference template with values of mandate or the related contract
     */
    public static function build_mandate_ref($mandate)
    {
        $template = BillingBase::first()->mandate_ref_template;

        if (! $template || (strpos($template, '{') === false)) {
            return $mandate->contract->number;
        }

        // replace placeholder with values
        preg_match_all('/(?<={)[^}]*(?=})/', $template, $matches);

        foreach ($matches[0] as $key) {
            if (array_key_exists($key, $mandate->contract['attributes'])) {
                $template = str_replace('{'.$key.'}', $mandate->contract['attributes'][$key], $template);
            } elseif (array_key_exists($key, $mandate['attributes'])) {
                $template = str_replace('{'.$key.'}', $mandate['attributes'][$key], $template);
            }
        }

        return $template;
    }
}
