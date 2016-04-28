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
			'name' 	=> 'required',
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
	public function get_view_link_title()
	{
		return $this->name;
	}

	// Return a pre-formated index list
	public function index_list ()
	{
		return $this->orderBy('id')->get();
	}

	public function view_has_many()
	{
		return array(
			'Product' => $this->products(),
			);
	}



	/**
	 * Relationships:
	 */
	public function sepa_account ()
	{
		return $this->belongsTo('Modules\BillingBase\Entities\SepaAccount', 'sepa_account_id');
	}

	public function products()
	{
		return Product::where('costcenter_id', '=', $this->id)->get();
	}


}
