<?php

namespace Modules\ProvVoipEnvia\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

use Modules\ProvVoipEnvia\Entities\EnviaOrder;
use Modules\ProvVoipEnvia\Entities\EnviaOrderDocument;

/* use Modules\ProvVoipEnvia\Entities\EnviaOrders; */

class EnviaOrderDocumentController extends \BaseModuleController {

	protected $index_create_allowed = false;

	// where to save the uploaded documents (relative to /storage/app)
	protected $document_base_path = "modules/ProvVoipEnvia/EnviaOrderDocuments";

	/**
	 * defines the formular fields for the edit and create view
	 */
	public function get_form_fields($model = null) {

		/* dd($model->enviaorder()); */
		/* dd($model); */
		$ret = array(
			array('form_type' => 'select', 'name' => 'document_type', 'description' => 'Document type', 'value' => EnviaOrderDocument::getPossibleEnumValues('document_type')),
			array('form_type' => 'text', 'name' => 'enviaorder_id', 'description' => 'Envia Order'),
			array('form_type' => 'file', 'name' => 'document_upload', 'description' => 'Upload document'),
		);

		return $ret;
	}

	/**
	 * Overwrites the base method => we need to handle file uploads
	 * @author Patrick Reichel
	 */
	protected function store() {

		// check and handle uploaded documents
		// perform only if file is uploaded, otherwise let the model decide what to do
		if (\Input::hasFile('document_upload')) {
			$this->_handle_document_upload();
		}

		// finally: call base method
		return parent::store();
	}

	public function edit($id) {

		echo "TODO: write redirect to trigger envia action and later to uploaded file";
	}

	/**
	 * We don't use the generic handle_file_upload method – here we have some non-standard extras to implement…
	 *
	 * @author Patrick Reichel
	 */
	protected function _handle_document_upload() {

		// build path to store document in – this is the base path with subdir contract ID
		$enviaorder_id = \Input::get('enviaorder_id', -1);
		$contract_id = EnviaOrder::findOrFail($enviaorder_id)->contract->id;
		$document_path = $this->document_base_path.'/'.$contract_id;

		// build the filename: ISODate__contractID__documentType.ext
		$document_type = \Input::get('document_type');
		$original_filename = \Input::file('document_upload')->getClientOriginalName();

		// extract filename suffix (if existing)
		$parts = explode('.', $original_filename);
		if (count($parts) > 1) {
			$suffix = '.'.array_pop($parts);
		}
		else {
			$suffix = '';
		}

		echo "Generating filename";
		$new_filename = date('Y-m-d\tH-i-s').'__'.$contract_id.'__'.$document_type.$suffix;
		$new_filename = \Str::lower($new_filename);
		\Input::merge(array('filename' => $new_filename));


		// get MIME type and store for use in model
		$mime_type = \Input::file('document_upload')->getMimeType();
		\Input::merge(array('mime_type', $mime_type));

		// if MIME type is forbidden: return instantly (don't move uploaded file to destination)
		$allowed_mimetypes = EnviaOrderDocument::$allowed_mimetypes;
		if (!in_array($mime_type, $allowed_mimetypes)) {
			return;
		};




		// move uploaded file to document_path
		echo "Move uploaded file";


		// chmod to readonly => prevent later overwriting by accident
		echo "chmod file to readonly";


		/* dd($document_path); */
	}
}


/* protected function handle_file_upload($base_field, $dst_path) { */

/* 	$upload_field = $base_field."_upload"; */

/* 	if (Input::hasFile($upload_field)) { */

/* 		// get filename */
/* 		$filename = Input::file($upload_field)->getClientOriginalName(); */

/* 		// move file */
/* 		Input::file($upload_field)->move($dst_path, $filename); */

/* 		// place filename as chosen value in Input field */
/* 		Input::merge(array($base_field => $filename)); */
/* 	} */

/* } */


