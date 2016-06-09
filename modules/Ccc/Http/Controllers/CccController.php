<?php 
namespace Modules\Ccc\Http\Controllers;

use Modules\Ccc\Entities\Ccc;
use Modules\ProvBase\Entities\Contract;

class CccController extends \BaseController {

	/**
	 * defines the formular fields for the edit view
	 */
	public function view_form_fields($model = null)
	{
		if (!$model)
			$model = new CCC;

		$files = CCC::template_files();

		// label has to be the same like column in sql table
		return array(
			array('form_type' => 'select', 'name' => 'template_filename', 'description' => 'Connection Info Template', 'value' => $files, 'help' => 'Tex Template used to Create Connection Information on the Contract Page for a Customer'),
			array('form_type' => 'file', 'name' => 'template_filename_upload', 'description' => 'Upload Template'),
		);
	}



	/**
	 * Overwrites the base methods to handle file uploads
	 */
	public function store($redirect = true)
	{
		$dir = storage_path('app/config/ccc/template/');
		if (!is_dir($dir))
			mkdir($dir, 0700, true);

		// check and handle uploaded firmware files
		$this->handle_file_upload('template_filename', $dir);

		// finally: call base method
		return parent::store();
	}

	public function update($id)
	{
		$dir = storage_path('app/config/ccc/template/');
		if (!is_dir($dir))
			mkdir($dir, 0700, true);

		$this->handle_file_upload('template_filename', storage_path('app/config/ccc/template/'));

		return parent::update($id);
	}


	
	/**
	 * Create and Download Connection Information
	 * @param integer  	$id  	contract id
	 * @return file response() - download box from browser
	 *
	 * @author Torsten Schmidt, Nino Ryschawy
	 */
	public function connection_info_download ($id)
	{
		// TODO: @Nino Ryschawy: create connection information under storage path
		//       and set $pdf to created pdf file (recursive under storage/apps)
		$template_dir = storage_path('app/config/ccc/template/');
		$c = Contract::find($id);
		$data = $c->ccc();

		$template_data = array(
			''

			);

		$logo_path = \PPModule::is_active('billingbase') ? : null;

		$dir = storage_path('app/data/ccc/');
		$filename = 'conn_info_'.$c->id.'.pdf';

		if (!is_file($dir.$filename))
			return $data;

		$pdf = response()->download($dir.$filename);

		return $pdf;
	}


}