<?php

namespace Modules\BillingBase\Http\Controllers;

class CompanyController extends \BaseController
{
    protected $file_upload_paths = [
        'logo' => 'app/config/billingbase/logo/',
        'conn_info_template_fn' => 'app/config/ccc/template/',
    ];

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        $logos = self::get_storage_file_list('billingbase/logo/');

        // label has to be the same like column in sql table
        $a = [
            ['form_type' => 'text', 'name' => 'name', 'description' => 'Name'],
            ['form_type' => 'text', 'name' => 'street', 'description' => 'Street'],
            ['form_type' => 'text', 'name' => 'zip', 'description' => 'Zip'],
            ['form_type' => 'text', 'name' => 'city', 'description' => 'City'],

            ['form_type' => 'text', 'name' => 'phone', 'description' => 'Phone'],
            ['form_type' => 'text', 'name' => 'fax', 'description' => 'Fax'],
            ['form_type' => 'text', 'name' => 'web', 'description' => 'Web address'],
            ['form_type' => 'text', 'name' => 'mail', 'description' => 'Mail address', 'space' => '1'],

            ['form_type' => 'text', 'name' => 'registration_court_1', 'description' => 'Registration Court 1'],
            ['form_type' => 'text', 'name' => 'registration_court_2', 'description' => 'Registration Court 2'],
            ['form_type' => 'text', 'name' => 'registration_court_3', 'description' => 'Registration Court 3'],

            ['form_type' => 'text', 'name' => 'management', 'description' => 'Management', 'options' => ['placeholder' => 'Max Mustermann, Luise Musterfrau'], 'help' => trans('helper.Company_Management')],
            ['form_type' => 'text', 'name' => 'directorate', 'description' => 'Directorate', 'options' => ['placeholder' => 'Max Mustermann, Luise Musterfrau'], 'help' => trans('helper.Company_Directorate')],

            ['form_type' => 'text', 'name' => 'tax_id_nr', 'description' => 'Sales Tax Id Nr'],
            ['form_type' => 'text', 'name' => 'tax_nr', 'description' => 'Tax Nr', 'space' => '1'],

            ['form_type' => 'text', 'name' => 'transfer_reason', 'description' => 'Transfer Reason for Invoices', 'space' => '1', 'help' => trans('helper.Company_TransferReason'), 'options' => ['placeholder' => '{contract_nr} {invoice_nr}']],

            ['form_type' => 'select', 'name' => 'logo', 'description' => 'Choose logo', 'value' => $logos],
            ['form_type' => 'file', 'name' => 'logo_upload', 'description' => 'Upload logo'],
        ];

        $b = [];
        if (\Module::collections()->has('Ccc')) {
            $files = self::get_storage_file_list('ccc/template/');

            $b = [
                ['form_type' => 'select', 'name' => 'conn_info_template_fn', 'description' => 'Connection Info Template', 'value' => $files, 'help' => trans('helper.conn_info_template')],
                ['form_type' => 'file', 'name' => 'conn_info_template_fn_upload', 'description' => 'Upload Template', 'help' => trans('helper.tex_template')],
            ];
        }

        return array_merge($a, $b);
    }
}
