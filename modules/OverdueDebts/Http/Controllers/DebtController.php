<?php

namespace Modules\OverdueDebts\Http\Controllers;

use View;
use Yajra\Datatables\Datatables;
use Modules\OverdueDebts\Entities\Debt;
use App\Http\Controllers\BaseViewController;

class DebtController extends \BaseController
{
    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        if (! $model->date) {
            $model->date = date('Y-m-d');
        }

        if (! $model->cleared && (! $model->id || ! Debt::where('parent_id', $model->id)->count())) {
            $selectList[null] = null;
            $contract_id = $model->contract_id ?: \Request::get('contract_id');

            // Type 1 (sta): Bank file upload with whole history of debts
            // Type 2 (csv): Debt import from financial accounting software (no history, only still open debts)
            $debts = Debt::where('contract_id', $contract_id)->where('cleared', 0)
                ->whereNull('parent_id')
                ->where('id', '!=', $model->id)
                ->get();

            foreach ($debts as $debt) {
                $selectList[$debt->id] = $debt->label();
            }

            $fields1[] = ['form_type' => 'select', 'name' => 'parent_id', 'description' => 'Debt to clear', 'value' => $selectList];
        } else {
            $fields1[] = ['form_type' => 'text', 'name' => 'parent_id', 'description' => 'Debt to clear', 'hidden' => 1];
        }

        // label has to be the same like column in sql table
        $fields2 = [
            ['form_type' => 'text', 'name' => 'contract_id', 'description' => 'Contract', 'hidden' => 1],
            ['form_type' => 'text', 'name' => 'voucher_nr', 'description' => 'Voucher number'],
            ['form_type' => 'text', 'name' => 'number', 'description' => 'Payment number', 'space' => 1],
            ['form_type' => 'text', 'name' => 'amount', 'description' => 'Amount'],
            ['form_type' => 'text', 'name' => 'missing_amount', 'description' => 'Missing amount', 'hidden' => 'C', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'bank_fee', 'description' => 'Bank fee'],
            ['form_type' => 'text', 'name' => 'total_fee', 'description' => 'Total fee', 'help' => trans('overduedebts::help.debt.total_fee'), 'space' => 1],
            ['form_type' => 'text', 'name' => 'date', 'description' => 'Voucher date'],         // Belegdatum
            ['form_type' => 'text', 'name' => 'due_date', 'description' => 'RCD', 'space' => 1],

            ['form_type' => 'text', 'name' => 'indicator', 'description' => 'Dunning indicator'],
            ['form_type' => 'text', 'name' => 'dunning_date', 'description' => 'Dunning date', 'space' => 1],

            ['form_type' => 'checkbox', 'name' => 'cleared', 'description' => trans('overduedebts::view.cleared'), 'options' => ['onclick' => "return false;", 'readonly']],
            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
        ];

        return array_merge($fields1, $fields2);
    }

    public function prepare_input($data)
    {
        $data['indicator'] = $data['indicator'] ?? 0;
        $data['bank_fee'] = $data['bank_fee'] ?? 0;
        $data['total_fee'] = $data['total_fee'] ?? 0;

        return parent::prepare_input($data);
    }

    /**
     * Separate index page for the resulting outstanding payments of each customer
     *
     * Here the all the customers with a sum unequal zero of all amounts and total fees of his debts are shown
     *
     * @return View
     */
    public function result()
    {
        $model = static::get_model_obj();
        $headline = trans('overduedebts::view.debt.headline');
        $view_header = BaseViewController::translate_view('Overview', 'Header');
        $create_allowed = $delete_allowed = false;

        $view_path = 'Generic.index';
        $ajax_route_name = 'Debt.result.data';

        return View::make($view_path, $this->compact_prep_view(compact('headline', 'view_header', 'model', 'create_allowed', 'delete_allowed', 'ajax_route_name')));
    }

    /**
     * Index table for overdue debts list - containing all debts that are not yet cleared
     *
     * Note: Filter is applied in Debt::view_index_label()
     */
    public function result_datatables_ajax()
    {
        return parent::index_datatables_ajax();
    }
}
