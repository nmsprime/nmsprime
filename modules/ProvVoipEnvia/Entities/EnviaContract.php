<?php

namespace Modules\ProvVoipEnvia\Entities;

use Modules\ProvBase\Entities\Contract;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvVoip\Entities\Phonenumber;
use Modules\ProvVoip\Entities\PhonenumberManagement;
use Modules\ProvVoipEnvia\Entities\EnviaOrder;

class EnviaContract extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'enviacontract';

    protected $fillable = [];


	// the relations

	/**
	 * Link to contract
	 */
	public function contract() {
		return $this->belongsTo('Modules\ProvBase\Entities\Contract', 'contract_id');
	}

	/**
	 * Link to modem
	 */
	public function modem() {
		return $this->belongsTo('Modules\ProvBase\Entities\Modem', 'modem_id');
	}

	/**
	 * Link to enviaorders
	 */
	public function enviaorders() {
		return $this->hasMany('Modules\ProvVoipEnvia\Entities\EnviaOrder');
	}

	/**
	 * Link to phonenumbermanagements
	 */
	public function phonenumbermanagements() {
		return $this->hasMany('Modules\ProvVoip\Entities\PhonenumberManagement');
	}

	/**
	 * Link to phonenumbers
	 */
	public function phonenumbers() {
		return $this->hasManyThrough('Modules\ProvVoip\Entities\Phonenumber', 'Modules\ProvVoip\Entities\PhonenumberManagement');
	}

}
