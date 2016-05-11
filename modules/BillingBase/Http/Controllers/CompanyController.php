<?php 
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\Billingbase\Entities\Company;
use Input;

class CompanyController extends \BaseModuleController {
	
	/**
	 * defines the formular fields for the edit and create view
	 */
	public function get_form_fields($model = null)
	{
		if (!$model)
			$model = new Company;

		$files = $model->billing_files();
		// dd($files);
		$logos = $files ? $files['logo'] : null;
		$templates = $files ? $files['template'] : null;

		// TODO: Translation
		$help = 'The Text of the separate four \'Invoice Text\'-Fields is automatically chosen dependent on the total charge and SEPA Mandate and is set in the appropriate Invoice for the Customer. It is possible to use all data field keys of the Bill Class as placeholder in the form of {fieldname} to build a kind of template. These are replaced by the actual value of the Invoice.';

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name'),
			array('form_type' => 'text', 'name' => 'street', 'description' => 'Street'),
			array('form_type' => 'text', 'name' => 'zip', 'description' => 'Zip'),
			array('form_type' => 'text', 'name' => 'city', 'description' => 'City'),

			array('form_type' => 'text', 'name' => 'phone', 'description' => 'Phone'),
			array('form_type' => 'text', 'name' => 'fax', 'description' => 'Fax'),
			array('form_type' => 'text', 'name' => 'web', 'description' => 'Web address'),
			array('form_type' => 'text', 'name' => 'mail', 'description' => 'Mail address', 'space' => '1'),

			array('form_type' => 'text', 'name' => 'registration_court_1', 'description' => 'Registration Court 1'),
			array('form_type' => 'text', 'name' => 'registration_court_2', 'description' => 'Registration Court 2'),
			array('form_type' => 'text', 'name' => 'registration_court_3', 'description' => 'Registration Court 3'),

			array('form_type' => 'text', 'name' => 'management', 'description' => 'Management (Comma separated)', 'options' => ['placeholder' => 'Max Mustermann, Luise Musterfrau']),
			array('form_type' => 'text', 'name' => 'directorate', 'description' => 'Directorate (Comma separated)', 'options' => ['placeholder' => 'Max Mustermann, Luise Musterfrau']),

			array('form_type' => 'text', 'name' => 'tax_id_nr', 'description' => 'Sales Tax Id Nr'),
			array('form_type' => 'text', 'name' => 'tax_nr', 'description' => 'Tax Nr', 'space' => '1'),

			array('form_type' => 'text', 'name' => 'invoice_text_sepa_positiv', 'description' => 'Invoice Text for positiv Amount with Sepa Mandate', 'help' => $help),
			array('form_type' => 'text', 'name' => 'invoice_text_sepa_negativ', 'description' => 'Invoice Text for negativ Amount with Sepa Mandate'),
			array('form_type' => 'text', 'name' => 'invoice_text_positiv', 'description' => 'Invoice Text for positiv Amount without Sepa Mandate'),
			array('form_type' => 'text', 'name' => 'invoice_text_negativ', 'description' => 'Invoice Text for negativ Amount without Sepa Mandate'),
			array('form_type' => 'text', 'name' => 'transfer_reason', 'description' => 'Transfer Reason for Invoices', 'space' => '1'),

			array('form_type' => 'select', 'name' => 'logo', 'description' => 'Choose logo', 'value' => $logos),
			array('form_type' => 'file', 'name' => 'logo_upload', 'description' => 'Upload logo'),
			array('form_type' => 'select', 'name' => 'template', 'description' => 'Choose template file for invoice', 'value' => $templates),
			array('form_type' => 'file', 'name' => 'template_upload', 'description' => 'Upload template'),

		);
	}

	/**
	 * Overwrites the base methods to handle file uploads
	 */
	protected function store($redirect = true)
	{
		// check and handle uploaded firmware files
		$this->handle_file_upload('logo', '/tftpboot/bill/logo/');
		$this->handle_file_upload('template', '/tftpboot/bill/template/');

		// finally: call base method
		return parent::store();
	}

	public function update($id)
	{
		$this->handle_file_upload('logo', '/tftpboot/bill/logo/');
		$this->handle_file_upload('template', '/tftpboot/bill/template/');

		return parent::update($id);
	}

	// Also overwritten because we don't want a field to be updated, just upload a file
	// protected function handle_file_upload($field, $dst_path)
	// {
	// 	if (Input::hasFile($field))
	// 	{
	// 		// get filename
	// 		$filename = Input::file($field)->getClientOriginalName();

	// 		// move file
	// 		Input::file($field)->move($dst_path, $filename);
	// 	}
	// }

}