<?php

namespace Modules\BillingBase\Entities;
use Modules\ProvBase\Entities\Contract;

class SepaAccount extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'sepa_account';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			'name' 		=> 'required',
			'holder' 	=> 'required',
			'iban' 		=> 'required|iban',
			'bic' 		=> 'required|bic',
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function get_view_header()
	{
		return 'SEPA Account';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return $this->name;
	}

	// Return a pre-formated index list
	public function index_list ()
	{
		return $this->orderBy('id')->get();
	}

	// View Relation.
	public function view_has_many()
	{
		return array(
			'CostCenter' => $this->costcenter,
			);
	}


	/**
	 * Relationships:
	 */
	public function costcenter ()
	{
		return $this->hasMany('Modules\BillingBase\Entities\CostCenter');
	}

}
