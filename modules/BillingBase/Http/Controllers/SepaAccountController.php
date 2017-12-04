<?php
namespace Modules\BillingBase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\Company;

class SepaAccountController extends \BaseController {

    /**
     * defines the formular fields for the edit and create view
     */
	public function view_form_fields($model = null)
	{
		if (!$model)
			$model = new SepaAccount;

		$list = $model->html_list(Company::all(), 'name');
		$list[null] = null;
		ksort($list);

		$templates = self::get_storage_file_list('billingbase/template');

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Account Name'),
			array('form_type' => 'text', 'name' => 'holder', 'description' => 'Account Holder'),
			array('form_type' => 'text', 'name' => 'creditorid', 'description' => 'Creditor ID'),
			array('form_type' => 'text', 'name' => 'iban', 'description' => 'IBAN'),
			array('form_type' => 'text', 'name' => 'bic', 'description' => 'BIC'),
			array('form_type' => 'text', 'name' => 'institute', 'description' => 'Institute', 'space' => 1),

			array('form_type' => 'select', 'name' => 'company_id', 'description' => 'Company', 'value' => $list),
			array('form_type' => 'text', 'name' => 'invoice_nr_start', 'description' => 'Invoice Number Start', 'help' => trans('helper.BillingBase_InvoiceNrStart'), 'space' => 1),

			array('form_type' => 'text', 'name' => 'invoice_headline', 'description' => 'Invoice Headline', 'help' => trans('helper.SepaAccount_InvoiceHeadline')),
			array('form_type' => 'text', 'name' => 'invoice_text_sepa', 'description' => 'Invoice Text for positive Amount with Sepa Mandate', 'help' => trans('helper.SepaAccount_InvoiceText')),
			array('form_type' => 'text', 'name' => 'invoice_text_sepa_negativ', 'description' => 'Invoice Text for negative Amount with Sepa Mandate'),
			array('form_type' => 'text', 'name' => 'invoice_text', 'description' => 'Invoice Text for positive Amount without Sepa Mandate'),
			array('form_type' => 'text', 'name' => 'invoice_text_negativ', 'description' => 'Invoice Text for negative Amount without Sepa Mandate', 'space' => 1),

			array('form_type' => 'select', 'name' => 'template_invoice', 'description' => 'Choose invoice template file', 'value' => $templates),
			array('form_type' => 'select', 'name' => 'template_cdr', 'description' => 'Choose Call Data Record template file', 'value' => $templates),
			array('form_type' => 'file', 'name' => 'template_invoice_upload', 'description' => 'Upload invoice template'),
			array('form_type' => 'file', 'name' => 'template_cdr_upload', 'description' => 'Upload CDR template', 'space' => 1),

			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'),
		);
	}


	public function prepare_input($data)
	{
		$data['bic'] = $data['bic'] ? : SepaAccount::get_bic($data['iban']);
		$data['bic'] = strtoupper(str_replace(' ', '' , $data['bic']));

		$data['iban'] = strtoupper(str_replace(' ', '' , $data['iban']));
		$data['creditorid'] = strtoupper($data['creditorid']);

		return parent::prepare_input($data);
	}


	public function prepare_rules($rules, $data)
	{
		$rules['bic'] .= $data['bic'] ? '' : '|required';

		return parent::prepare_rules($rules, $data);
	}


	/**
	 * Overwrites the base methods to handle file uploads
	 */
	public function store($redirect = true)
	{
		// check and handle uploaded firmware files
		$this->handle_file_upload('template_invoice', storage_path('app/config/billingbase/template/'));
		$this->handle_file_upload('template_cdr', storage_path('app/config/billingbase/template/'));

		// finally: call base method
		return parent::store();
	}

	public function update($id)
	{
		$this->handle_file_upload('template_invoice', storage_path('app/config/billingbase/template/'));
		$this->handle_file_upload('template_cdr', storage_path('app/config/billingbase/template/'));

		return parent::update($id);
	}
	
}