<?php

namespace Modules\ProvVoipEnvia\Entities;

// Model not found? execute composer dump-autoload in lara root dir
class EnviaOrderDocument extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'enviaorderdocument';

	// Add your validation rules here
	public static function rules($id=null) {

		return array(
		);
	}

	// Don't forget to fill this array
	protected $fillable = [
		'type',
		'filename',
	];

	// Name of View
	public static function get_view_header()
	{
		return 'EnviaOrderDocuments';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return $this->type;
	}

	// belongs to a modem - see BaseModel for explanation
	public function view_belongs_to ()
	{
		return $this->enviaorder;
	}

	public function enviaorder() {

		return $this->belongsTo('Modules\ProvVoipEnvia\Entities\EnviaOrder');
	}

}

