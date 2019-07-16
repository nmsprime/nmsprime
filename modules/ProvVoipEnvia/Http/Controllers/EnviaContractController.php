<?php

namespace Modules\ProvVoipEnvia\Http\Controllers;

use Illuminate\Support\Facades\View;
use Modules\ProvVoip\Entities\PhoneTariff;

class EnviaContractController extends \BaseController
{
    protected $index_create_allowed = false;
    protected $index_delete_allowed = false;

    /**
     * defines the formular fields for the edit and create view
     *
     * @return 	array
     */
    public function view_form_fields($model = null)
    {
        if ($model) {
            $sale_tariffs = PhoneTariff::where('external_identifier', '=', $model->tariff_id)->where('type', '=', 'sale')->get();
            $purchase_tariffs = PhoneTariff::where('external_identifier', '=', $model->variation_id)->where('type', '=', 'purchase')->get();
        } else {
            $model = new EnviaContract;
            $sale_tariffs = [];
            $purchase_tariffs = [];
        }

        $purchase_value = $model->variation_id;
        if ($purchase_value && $purchase_tariffs) {
            $purchase_value .= ' ⇒ ';
            $_ = [];
            foreach ($purchase_tariffs as $purchase_tariff) {
                array_push($_, $purchase_tariff->name);
            }
            $purchase_value .= implode(', ', $_);
        }
        $model->variation_id = $purchase_value ?: 'n/a'; // save as we do not save the model here

        $sale_value = $model->tariff_id;
        if ($sale_value && $sale_tariffs) {
            $sale_value .= ' ⇒ ';
            $_ = [];
            foreach ($sale_tariffs as $sale_tariff) {
                array_push($_, $sale_tariff->name);
            }
            $sale_value .= implode(', ', $_);
        }
        $model->tariff_id = $sale_value ?: 'n/a'; // save as we do not save the model here

        $ret = [
            ['form_type' => 'text', 'name' => 'envia_customer_reference', 'description' => trans('provvoipenvia::view.enviaContract.custId'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'envia_contract_reference', 'description' => trans('provvoipenvia::view.enviaContract.contId'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'state', 'description' => trans('provvoipenvia::view.enviaContract.state'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'start_date', 'description' => trans('provvoipenvia::view.enviaContract.startDate'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'end_date', 'description' => trans('provvoipenvia::view.enviaContract.endDate'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'prev_id', 'description' => trans('provvoipenvia::view.enviaContract.prevContract'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'next_id', 'description' => trans('provvoipenvia::view.enviaContract.nextContract'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'lock_level', 'description' => trans('provvoipenvia::view.enviaContract.lockLevel'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'method', 'description' => trans('provvoipenvia::view.enviaContract.proto'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'sla_id', 'description' => trans('provvoipenvia::view.enviaContract.sla'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'tariff_id', 'description' => trans('provvoipenvia::view.enviaContract.tariff'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'variation_id', 'description' => trans('provvoipenvia::view.enviaContract.variation'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'contract_id', 'description' => trans('provvoipenvia::view.enviaContract.contractId'), 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'modem_id', 'description' => trans('provvoipenvia::view.enviaContract.modemId'), 'options' => ['readonly']],
        ];

        return $ret;
    }

    /**
     * Overwrite base method.
     *
     * Here we inject the following data:
     *	- information about needed/possible user actions
     *	- mailto: link to envia TEL support as additional data
     *
     * @author Patrick Reichel
     */
    protected function _get_additional_data_for_edit_view($model)
    {
        $additional_data = [
            'relations' => $model->get_relation_information(),
        ];

        return $additional_data;
    }

    /* public function index() */
    /* { */
    /* 	return view('provvoipenvia::index'); */
    /* } */
}
