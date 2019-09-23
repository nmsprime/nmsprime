<?php

namespace Modules\OverdueDebts\Http\Controllers;

class OverdueDebtsController extends \BaseController
{
    public function view_form_fields($model = null)
    {
        $ret1 = [
            ['form_type' => 'text', 'name' => 'fee', 'description' => 'Fee for return debit notes', 'help' => trans('overduedebts::help.fee')],
            ['form_type' => 'checkbox', 'name' => 'total', 'description' => 'Total', 'help' => trans('overduedebts::help.total')],
            ['form_type' => 'text', 'name' => 'payment_period', 'description' => 'Payment period'],
            ['form_type' => 'text', 'name' => 'dunning_charge1', 'description' => trans('overduedebts::view.dunningCharge').' 1'],
            ['form_type' => 'text', 'name' => 'dunning_charge2', 'description' => trans('overduedebts::view.dunningCharge').' 2'],
            ['form_type' => 'text', 'name' => 'dunning_charge3', 'description' => trans('overduedebts::view.dunningCharge').' 3'],
            ['form_type' => 'textarea', 'name' => 'dunning_text1', 'description' => trans('overduedebts::view.dunningText').' 1'],
            ['form_type' => 'textarea', 'name' => 'dunning_text2', 'description' => trans('overduedebts::view.dunningText').' 2'],
            ['form_type' => 'textarea', 'name' => 'dunning_text3', 'description' => trans('overduedebts::view.dunningText').' 3', 'space' =>1],
        ];

        $ret2 = [];

        if (config('overduedebts.debtMgmtType') == 'csv') {
            $ret2 = [
                ['form_type' => 'text', 'name' => 'import_inet_block_amount', 'description' => trans('overduedebts::view.import.amount'), 'help' => trans('overduedebts::help.import.amount')],
                ['form_type' => 'text', 'name' => 'import_inet_block_debts', 'description' => trans('overduedebts::view.import.debts'), 'help' => trans('overduedebts::help.import.debts')],
                ['form_type' => 'text', 'name' => 'import_inet_block_indicator', 'description' => trans('overduedebts::view.import.indicator'), 'help' => trans('overduedebts::help.import.indicator')],
            ];
        }

        return array_merge($ret1, $ret2);
    }
}
