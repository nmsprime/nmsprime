<?php

namespace Modules\BillingBase\Http\Controllers;

use Modules\BillingBase\Entities\Company;
use Modules\BillingBase\Entities\SepaAccount;

class SepaAccountController extends \BaseController
{
    protected $file_upload_paths = [
        'template_invoice' => 'app/config/billingbase/template/',
        'template_cdr' 	   => 'app/config/billingbase/template/',
    ];

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new SepaAccount;
        }

        $list = $model->html_list(Company::all(), 'name');
        $list[null] = null;
        ksort($list);

        $templates = self::get_storage_file_list('billingbase/template');

        // label has to be the same like column in sql table
        return [
            ['form_type' => 'text', 'name' => 'name', 'description' => 'Account Name'],
            ['form_type' => 'text', 'name' => 'holder', 'description' => 'Account Holder'],
            ['form_type' => 'text', 'name' => 'creditorid', 'description' => 'Creditor ID'],
            ['form_type' => 'text', 'name' => 'iban', 'description' => 'IBAN'],
            ['form_type' => 'text', 'name' => 'bic', 'description' => 'BIC'],
            ['form_type' => 'text', 'name' => 'institute', 'description' => 'Institute', 'space' => 1],

            ['form_type' => 'select', 'name' => 'company_id', 'description' => 'Company', 'value' => $list, 'hidden' => 0],
            ['form_type' => 'text', 'name' => 'invoice_nr_start', 'description' => 'Invoice Number Start', 'help' => trans('billingbase::help.BillingBase.invoiceNrStart'), 'space' => 1],

            ['form_type' => 'text', 'name' => 'invoice_headline', 'description' => 'Invoice Headline', 'help' => trans('billingbase::help.sepaAccount.invoiceHeadline')],
            ['form_type' => 'text', 'name' => 'invoice_text_sepa', 'description' => 'Invoice Text for positive Amount with Sepa Mandate', 'help' => trans('billingbase::help.sepaAccount.invoiceText')],
            ['form_type' => 'text', 'name' => 'invoice_text_sepa_negativ', 'description' => 'Invoice Text for negative Amount with Sepa Mandate'],
            ['form_type' => 'text', 'name' => 'invoice_text', 'description' => 'Invoice Text for positive Amount without Sepa Mandate'],
            ['form_type' => 'text', 'name' => 'invoice_text_negativ', 'description' => 'Invoice Text for negative Amount without Sepa Mandate', 'space' => 1],

            ['form_type' => 'select', 'name' => 'template_invoice', 'description' => 'Choose invoice template file', 'value' => $templates],
            ['form_type' => 'select', 'name' => 'template_cdr', 'description' => 'Choose Call Data Record template file', 'value' => $templates],
            ['form_type' => 'file', 'name' => 'template_invoice_upload', 'description' => 'Upload invoice template', 'help' => trans('billingbase::help.texTemplate')],
            ['form_type' => 'file', 'name' => 'template_cdr_upload', 'description' => 'Upload CDR template', 'help' => trans('billingbase::help.texTemplate'), 'space' => 1],

            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
        ];
    }

    public function prepare_input($data)
    {
        $data['invoice_nr_start'] = $data['invoice_nr_start'] ?: 1;
        $data['bic'] = $data['bic'] ?: SepaAccount::get_bic($data['iban']);
        $data['bic'] = strtoupper(str_replace(' ', '', $data['bic']));

        $data['iban'] = strtoupper(str_replace(' ', '', $data['iban']));
        $data['creditorid'] = strtoupper($data['creditorid']);

        return parent::prepare_input($data);
    }

    public function prepare_rules($rules, $data)
    {
        $rules['bic'] .= $data['bic'] ? '' : '|required';

        return parent::prepare_rules($rules, $data);
    }
}
