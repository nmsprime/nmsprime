<?php

namespace Modules\PropertyManagement\Http\Controllers;

class RealtyController extends \BaseController
{
    /**
     * Defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        $nodes = selectList('node', 'name', true);

        $administrations = \DB::table('contact')->whereNull('deleted_at')->where('administration', 1)->get();
        $localContacts = \DB::table('contact')->whereNull('deleted_at')->where('administration', 0)->get();

        $administrations = $model->html_list($administrations, ['firstname1', 'lastname1'], true, ' ');
        $localContacts = $model->html_list($localContacts, ['firstname1', 'lastname1'], true, ' ');

        // label has to be the same like column in sql table
        $fields = [
            ['form_type' => 'select', 'name' => 'node_id', 'description' => trans('propertymanagement::view.Node'), 'value' => $nodes, 'space' => 1],

            ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'],
            ['form_type' => 'text', 'name' => 'number', 'description' => 'Number', 'space' => 1],
            ['form_type' => 'text', 'name' => 'street', 'description' => 'Street', 'autocomplete' => []],
            ['form_type' => 'text', 'name' => 'house_nr', 'description' => 'House number'],
            ['form_type' => 'text', 'name' => 'zip', 'description' => 'Zip', 'autocomplete' => []],
            ['form_type' => 'text', 'name' => 'city', 'description' => 'City', 'autocomplete' => []],
            ['form_type' => 'text', 'name' => 'district', 'description' => 'District', 'autocomplete' => [], 'space' => 1],

            ['form_type' => 'checkbox', 'name' => 'group_contract', 'description' => trans('dt_header.group_contract')],
            ['form_type' => 'checkbox', 'name' => 'concession_agreement', 'description' => trans('dt_header.realty.concession_agreement')],
            ['form_type' => 'text', 'name' => 'agreement_from', 'description' => trans('dt_header.realty.agreement_from'), 'checkbox' => 'show_on_concession_agreement'],
            ['form_type' => 'text', 'name' => 'agreement_to', 'description' => trans('dt_header.realty.agreement_to'), 'checkbox' => 'show_on_concession_agreement'],
            ['form_type' => 'text', 'name' => 'last_restoration_on', 'description' => trans('dt_header.realty.last_restoration_on')],
            ['form_type' => 'text', 'name' => 'expansion_degree', 'description' => trans('dt_header.expansion_degree'), 'space' => 1],

            ['form_type' => 'select', 'name' => 'contact_id', 'value' => $administrations, 'description' => trans('dt_header.realty.contact_id')],
            ['form_type' => 'select', 'name' => 'contact_local_id', 'value' => $localContacts, 'description' => trans('dt_header.realty.contact_local_id'), 'space' => 1],

            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
        ];

        return $fields;
    }
}
