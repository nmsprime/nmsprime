<?php

namespace Modules\ProvVoipEnvia\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

use Modules\ProvVoipEnvia\Entities\EnviaOrder;
use Modules\ProvVoipEnvia\Entities\EnviaOrderDocument;

/* use Modules\ProvVoipEnvia\Entities\EnviaOrders; */

class EnviaOrderDocumentController extends \BaseModuleController {

	protected $index_create_allowed = false;

	/**
	 * Constructor.
	 * Used to set some helper variables.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $attributes pass through to Eloquent contstructor.
	 */
	public function __construct($attributes = array()) {

		// call Eloquent constructor
		// $attributes are needed! (or e.g. seeding and creating will not work)
		parent::__construct($attributes);

		$this->document_base_path = EnviaOrderDocument::$document_base_path;
	}


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
		if ($enviaorder_id < 0) {
			throw new ValueError('No enviaorder_id given');
		}
		\Input::merge(array('enviaorder_id' => $enviaorder_id));

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

		$new_filename = date('Y-m-d\tH-i-s').'__'.$contract_id.'__'.$document_type.$suffix;
		$new_filename = \Str::lower($new_filename);
		\Input::merge(array('filename' => $new_filename));
		$new_filename_complete = $document_path.'/'.$new_filename;


		// get MIME type and store for use in model
		$mime_type = \Input::file('document_upload')->getMimeType();
		\Input::merge(array('mime_type', $mime_type));

		// if MIME type is forbidden: return instantly (don't move uploaded file to destination)
		$allowed_mimetypes = EnviaOrderDocument::$allowed_mimetypes;
		if (!in_array($mime_type, $allowed_mimetypes)) {
			return;
		};

		// move uploaded file to document_path (after making directories)
		\Storage::makeDirectory($document_path);
		\Storage::put($new_filename_complete, \File::get(\Input::file('document_upload')));

		// TODO: should we chmod the file to readonly??

	}

	/**
	 * Register observer
	 */
	public static function boot()
	{
		parent::boot();

		EnviaOrderDocument::observe(new EnviaOrderDocumentObserver);
	}

}


/**
 * EnviaOrderDocument Observer Class
 * Handles changes on EnviaOrderDocument
 *
 * can handle   'creating', 'created', 'updating', 'updated',
 *              'deleting', 'deleted', 'saving', 'saved',
 *              'restoring', 'restored',
 *
 * @author Patrick Reichel
 */
class EnviaOrderDocumentObserver
{
	/**
	 * After creation: send document to EnviaTEL
	 *
	 * @author Patrick Reichel
	 */
	public function created($envia_order_document)
	{
		$order_id = $envia_order_document->enviaorder->orderid;
		dd($order_id);
		// call Envia API and send file

	}

}


