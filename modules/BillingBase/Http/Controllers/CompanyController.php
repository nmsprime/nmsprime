<?php
namespace Modules\BillingBase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\BillingBase\Entities\Company;
use Input;

class CompanyController extends \BaseController {


	/**
	 * defines the formular fields for the edit and create view
	 */
	public function view_form_fields($model = null)
	{
		$logos = self::get_storage_file_list('billingbase/logo/');

		// label has to be the same like column in sql table
		$a = array(
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

			array('form_type' => 'text', 'name' => 'management', 'description' => 'Management', 'options' => ['placeholder' => 'Max Mustermann, Luise Musterfrau'], 'help' => trans('helper.Company_Management')),
			array('form_type' => 'text', 'name' => 'directorate', 'description' => 'Directorate', 'options' => ['placeholder' => 'Max Mustermann, Luise Musterfrau'], 'help' => trans('helper.Company_Directorate')),

			array('form_type' => 'text', 'name' => 'tax_id_nr', 'description' => 'Sales Tax Id Nr'),
			array('form_type' => 'text', 'name' => 'tax_nr', 'description' => 'Tax Nr', 'space' => '1'),

			array('form_type' => 'text', 'name' => 'transfer_reason', 'description' => 'Transfer Reason for Invoices', 'space' => '1', 'help' => trans('helper.Company_TransferReason'), 'options' => ['placeholder' => '{contract_nr} {invoice_nr}']),

			array('form_type' => 'select', 'name' => 'logo', 'description' => 'Choose logo', 'value' => $logos),
			array('form_type' => 'file', 'name' => 'logo_upload', 'description' => 'Upload logo'),
		);

		$b = [];
		if (\PPModule::is_active('ccc'))
		{
			$files = self::get_storage_file_list('ccc/template/');

			$b = array(
				array('form_type' => 'select', 'name' => 'conn_info_template_fn', 'description' => 'Connection Info Template', 'value' => $files, 'help' => 'Tex Template used to Create Connection Information on the Contract Page for a Customer'),
				array('form_type' => 'file', 'name' => 'conn_info_template_fn_upload', 'description' => 'Upload Template'),
				);

		}

		return array_merge($a, $b);

	}

	/**
	 * Overwrites the base methods to handle file uploads
	 */
	public function store($redirect = true)
	{
		// check and handle uploaded firmware files
		$this->handle_file_upload('logo', storage_path('app/config/billingbase/logo/'));

		if (\PPModule::is_active('ccc'))
			$this->handle_file_upload('conn_info_template_fn', storage_path('app/config/ccc/template/'));

		// finally: call base method
		return parent::store();
	}

	public function update($id)
	{
		$this->handle_file_upload('logo', storage_path('app/config/billingbase/logo/'));

		if (\PPModule::is_active('ccc'))
			$this->handle_file_upload('conn_info_template_fn', storage_path('app/config/ccc/template/'));

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
