<?php

namespace Modules\BillingBase\Entities;

class CostCenter extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'costcenter';

	// Add your validation rules here
	public static function rules($id = null)
	{
		// this is to avoid missing customer payments when changing the billing month during the year
		$m = date('m');

		return array(
			'name' 			=> 'required',
			'billing_month' => 'Numeric|Min:'.$m,
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function view_headline()
	{
		return 'Cost Center';
	}

	// link title in index view
	public function view_index_label()
	{
		return $this->name;
	}

	// Return a pre-formated index list
	public function index_list ()
	{
		return $this->orderBy('id')->get();
	}

	// public function view_has_many()
	// {
	// 	return array(
	// 		);
	// }



	/**
	 * Relationships:
	 */
	public function sepa_account ()
	{
		return $this->belongsTo('Modules\BillingBase\Entities\SepaAccount', 'sepa_account_id');
	}

	public function items()
	{
		return $this->hasMany('Modules\BillingBase\Entities\Item');
	}



	/**
	 * Returns billing month with leading zero - Note: if not set June is set as default
	 */
	public function get_billing_month()
	{
		return $this->billing_month ? ($this->billing_month > 9 ? $this->billing_month : '0'.$this->billing_month) : '06';
	}


}
