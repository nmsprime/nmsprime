<?php

namespace Modules\ProvVoipEnvia\Entities;

// Model not found? execute composer dump-autoload in lara root dir
class EnviaOrder extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'enviaorder';

	// Don't forget to fill this array
	protected $fillable = [
		'orderid',
		'ordertype_id',
		'ordertype',
		'orderstatus_id',
		'orderstatus',
		'orderdate',
		'ordercomment',
		'customerreference',
		'contractreference',
		'contract_id',
		'phonenumber_id',
	];

	public function contract() {
		return $this->hasOne('Modules\ProvBase\Entities\Contract');
	}

	public function phonenumber() {
		return $this->hasOne('Modules\ProvVoip\Entities\Phonenumber');
	}
}
