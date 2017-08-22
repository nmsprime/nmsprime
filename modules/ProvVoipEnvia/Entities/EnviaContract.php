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


	// Name of View
	public static function view_headline()
	{
		return 'EnviaContract';
	}


	// link title in index view
	public function view_index_label()
	{

		$envia_contract_reference = is_null($this->envia_contract_reference) ? 'n/a' : $this->envia_contract_reference;
		$start_date = is_null($this->start_date) ? 'n/a' : $this->start_date;
		$end_date = is_null($this->end_date) ? 'n/a' : $this->end_date;

		$contract_id = is_null($this->contract_id) ? 'n/a' : $this->contract_id;
		$modem_id = is_null($this->modem_id) ? 'n/a' : $this->modem_id;

        return ['index' => [$envia_contract_reference, $start_date, $end_date, $contract_id, $modem_id],
                'index_header' => ['Envia contract reference', 'Start date', 'End date', 'Contract', 'Modem'],
                'bsclass' => 'success',
				'header' => $envia_contract_reference,
		];

	}


	/* // View Relation. */
	/* public function view_has_many() */
	/* { */
	/* 	$ret = array(); */
	/* 	$ret['Edit']['Contract'] = $this->contract; */
	/* 	/1* $ret['Edit']['Modem'] = $this->modem; *1/ */

	/* 	return $ret; */
	/* } */

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

	/**
	 * Gets all phonenumbers with:
	 *		- existing phoneunmbermanagement
	 *		- activation date less or equal than today
	 *		- deactivation date null or bigger than today
	 *
	 * @author Patrick Reichel
	 */
	public function phonenumbers_active_through_phonenumbermanagent() {

		$phonenumbers = $this->phonenumbers();

		$isodate = substr(date('c'), 0, 10);

		$ret = [];
		foreach ($phonenumbers as $phonenumber) {

			$mgmt = $phonenumber->phonenumbermanagement;

			// activation date not set
			if (is_null($mgmt->activation_date)) {
				continue;
			}

			// not yet activated
			if ($mgmt->activation_date > $isodate) {
				continue;
			}

			// deactivation date set and today or in the past
			if (
				(!is_null($mgmt->deactivation_date))
				&&
				($mgmt->deactivation_date <= $isodate)
			) {
				continue;
			}

			// number seems to be active
			array_push($ret, $phonenumber);
		}

		return $ret;
	}

}
