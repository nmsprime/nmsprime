<?php
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\Company;

class SepaAccountController extends \BaseModuleController {

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

		$templates = $model->templates();
		// TODO: Translation
		$help = 'The Text of the separate four \'Invoice Text\'-Fields is automatically chosen dependent on the total charge and SEPA Mandate and is set in the appropriate Invoice for the Customer. It is possible to use all data field keys of the Invoice Class as placeholder in the form of {fieldname} to build a kind of template. These are replaced by the actual value of the Invoice.';

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Account Name'),
			array('form_type' => 'text', 'name' => 'holder', 'description' => 'Account Holder'),
			array('form_type' => 'text', 'name' => 'creditorid', 'description' => 'Creditor ID'),
			array('form_type' => 'text', 'name' => 'iban', 'description' => 'IBAN'),
			array('form_type' => 'text', 'name' => 'bic', 'description' => 'BIC'),
			array('form_type' => 'text', 'name' => 'institute', 'description' => 'Institute'),
			array('form_type' => 'select', 'name' => 'company_id', 'description' => 'Company', 'value' => $list),
			array('form_type' => 'text', 'name' => 'invoice_headline', 'description' => 'Invoice Headline', 'help' => 'Replaces Headline in Invoices created for this Costcenter'),
			array('form_type' => 'text', 'name' => 'invoice_text_sepa', 'description' => 'Invoice Text for positiv Amount with Sepa Mandate', 'help' => $help),
			array('form_type' => 'text', 'name' => 'invoice_text_sepa_negativ', 'description' => 'Invoice Text for negativ Amount with Sepa Mandate'),
			array('form_type' => 'text', 'name' => 'invoice_text', 'description' => 'Invoice Text for positiv Amount without Sepa Mandate'),
			array('form_type' => 'text', 'name' => 'invoice_text_negativ', 'description' => 'Invoice Text for negativ Amount without Sepa Mandate'),
			array('form_type' => 'select', 'name' => 'template_invoice', 'description' => 'Choose invoice template file', 'value' => $templates),
			array('form_type' => 'select', 'name' => 'template_cdr', 'description' => 'Choose Call Data Record template file', 'value' => $templates),
			array('form_type' => 'file', 'name' => 'template_invoice_upload', 'description' => 'Upload invoice template'),
			array('form_type' => 'file', 'name' => 'template_cdr_upload', 'description' => 'Upload CDR template'),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'),
		);
	}


	public function prepare_rules($rules, $data)
	{
		if (!$data['bic'])
			$rules['bic'] .= '|available:'.$data['iban'];

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