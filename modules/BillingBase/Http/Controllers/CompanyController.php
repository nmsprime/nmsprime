<?php 
namespace Modules\Billingbase\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\Billingbase\Entities\Company;

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
		$logos = $files['logo'];
		$templates = $files['template'];


		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'text', 'name' => 'name', 'description' => 'Name'),
			array('form_type' => 'text', 'name' => 'street', 'description' => 'Street'),
			array('form_type' => 'text', 'name' => 'zip', 'description' => 'Zip'),
			array('form_type' => 'text', 'name' => 'city', 'description' => 'City'),

			array('form_type' => 'text', 'name' => 'phone', 'description' => 'Phone'),
			array('form_type' => 'text', 'name' => 'fax', 'description' => 'Fax'),
			array('form_type' => 'text', 'name' => 'web', 'description' => 'Web address'),
			array('form_type' => 'text', 'name' => 'mail', 'description' => 'Mail address'),

			array('form_type' => 'text', 'name' => 'registration_court_1', 'description' => 'Registration Court 1'),
			array('form_type' => 'text', 'name' => 'registration_court_2', 'description' => 'Registration Court 2'),
			array('form_type' => 'text', 'name' => 'registration_court_3', 'description' => 'Registration Court 3'),

			array('form_type' => 'text', 'name' => 'management', 'description' => 'Management (Comma separated)', 'options' => ['placeholder' => 'Max Mustermann, Luise Musterfrau']),
			array('form_type' => 'text', 'name' => 'directorate', 'description' => 'Directorate (Comma separated)', 'options' => ['placeholder' => 'Max Mustermann, Luise Musterfrau']),

			array('form_type' => 'text', 'name' => 'tax_id_nr', 'description' => 'Sales Tax Id Nr'),
			array('form_type' => 'text', 'name' => 'tax_nr', 'description' => 'Tax Nr'),

			array('form_type' => 'select', 'name' => 'logo', 'description' => 'Choose logo', 'value' => $logos),
			array('form_type' => 'select', 'name' => 'template', 'description' => 'Choose template file for bill', 'value' => $templates),
			array('form_type' => 'file', 'name' => 'logo_upload', 'description' => 'Upload logo or template'),

		);
	}

	/**
	 * Overwrites the base method to handle file uploads
	 */
	protected function store()
	{
		// check and handle uploaded firmware files
		$this->handle_file_upload('logo', '/tftpboot/bill/');

		// finally: call base method
		return parent::store();
	}

	public function update($id)
	{
		$this->handle_file_upload('logo', '/tftpboot/bill/');

		return parent::update($id);
	}

}