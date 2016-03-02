<?php

namespace Modules\ProvVoipEnvia\Entities;

// Model not found? execute composer dump-autoload in lara root dir
class TRCClass extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'trcclass';

	// Don't forget to fill this array
	protected $fillable = [
		'trc_id',
		'trc_short',
		'trc_description',
	];

	public function phonenumbermanagements() {
		return $this->hasMany('Modules\ProvVoip\Entities\Phonenumber');
	}
}
