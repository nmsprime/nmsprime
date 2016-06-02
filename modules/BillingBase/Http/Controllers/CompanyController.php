<?php
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\Billingbase\Entities\Company;
use Input;

class CompanyController extends \BaseController {

	protected $edit_left_md_size = 7;

	/**
	 * defines the formular fields for the edit and create view
	 */
	public function view_form_fields($model = null)
	{
		if (!$model)
			$model = new Company;

		$logos = $model->logos();

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

			array('form_type' => 'text', 'name' => 'management', 'description' => 'Management', 'options' => ['placeholder' => 'Max Mustermann, Luise Musterfrau'], 'help' => 'Comma separated list of names'),
			array('form_type' => 'text', 'name' => 'directorate', 'description' => 'Directorate', 'options' => ['placeholder' => 'Max Mustermann, Luise Musterfrau'], 'help' => 'Comma separated list of names'),

			array('form_type' => 'text', 'name' => 'tax_id_nr', 'description' => 'Sales Tax Id Nr'),
			array('form_type' => 'text', 'name' => 'tax_nr', 'description' => 'Tax Nr', 'space' => '1'),

			array('form_type' => 'text', 'name' => 'transfer_reason', 'description' => 'Transfer Reason for Invoices', 'space' => '1', 'help' => 'Template from all Invoice class data field keys - Contract Number and Invoice Nr is default', 'options' => ['placeholder' => '{contract_nr} {invoice_nr}']),

			array('form_type' => 'select', 'name' => 'logo', 'description' => 'Choose logo', 'value' => $logos),
			array('form_type' => 'file', 'name' => 'logo_upload', 'description' => 'Upload logo'),

		);
	}

	/**
	 * Overwrites the base methods to handle file uploads
	 */
	public function store($redirect = true)
	{
		// check and handle uploaded firmware files
		$this->handle_file_upload('logo', storage_path('/app/config/billingbase/logo/'));

		// finally: call base method
		return parent::store();
	}

	public function update($id)
	{
		$this->handle_file_upload('logo', storage_path('/app/config/billingbase/logo/'));

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