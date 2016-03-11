<?php

namespace Modules\BillingBase\Entities;
use Modules\ProvBase\Entities\Contract;

class SepaMandate extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'sepamandate';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			'signature_date' => 'required',
			'sepa_iban' => 'required',
			'sepa_bic' => 'required',
			'sepa_institute' => 'required',
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function get_view_header()
	{
		return 'SEPA Mandate';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return $this->sepa_valid_from.' - '.$this->sepa_valid_to;
		// return $this->reference.' | '.$this->sepa_valid_from.' - '.$this->sepa_valid_to;
	}

	// Return a pre-formated index list
	public function index_list ()
	{
		return $this->orderBy('id')->get();
	}

	public function view_belongs_to ()
	{
		return $this->contract;
	}


	/**
	 * Relationships:
	 */
	public function contract ()
	{
		return $this->belongsTo('Modules\ProvBase\Entities\Contract', 'contract_id');
	}

	/*
	 * Init Observers
	 */
	public static function boot()
	{
		SepaMandate::observe(new SepaMandateObserver);
		parent::boot();
	}

}


/**
 * Observer Class
 *
 * can handle   'creating', 'created', 'updating', 'updated',
 *              'deleting', 'deleted', 'saving', 'saved',
 *              'restoring', 'restored',
 */
class SepaMandateObserver
{
	public function creating($mandate)
	{
		$mandate->reference = '002-'.Contract::find($mandate->contract_id)->id.'-001';
	}

	public function updating($mandate)
	{
		$mandate->reference = '002-'.Contract::find($mandate->contract_id)->id.'-001';
	}

}