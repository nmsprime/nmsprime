<?php

namespace Modules\BillingBase\Entities;
use Modules\ProvBase\Entities\Contract;

class CostCenter extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'costcenter';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			// 'signature_date' 	=> 'required|date',
			// 'sepa_iban' 		=> 'required|iban',
			// 'sepa_bic' 			=> 'required|bic',
			// 'sepa_institute' 	=> ,
			// 'sepa_valid_from' 	=> 'required|date',
			// 'sepa_valid_to'		=> 'dateornull'
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function get_view_header()
	{
		return 'Cost Center';
	}

	// link title in index view
	public function get_view_link_title()
	{
		// return $this->sepa_valid_from.' - '.$this->sepa_valid_to;
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
		// CostCenter::observe(new SepaAccountObserver);
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
class CostCenterObserver
{
	public function creating($mandate)
	{
	}

	public function updating($mandate)
	{
	}

}