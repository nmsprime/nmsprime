<?php

namespace Modules\BillingBase\Entities;

use Modules\BillingBase\Entities\Price;

class Item extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'item';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			// 'hostname' => 'required|unique:cmts,hostname,'.$id.',id,deleted_at,NULL'  	// unique: table, column, exception , (where clause)
			// 'payment_from' => 'date',
			// 'payment_to' => 'date' //'dateFormat:yyyy-mm-dd'
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function get_view_header()
	{
		return 'Item';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return Price::find($this->price_id)->name;
		return Price::find($this->price_id)->name.' | '.$this->payment_from.' - '.$this->payment_to;
	}

	public function view_belongs_to ()
	{
		return $this->contract;
	}

	/**
	 * Relationships:
	 */

	public function price ()
	{
		return $this->hasOne('Modules\BillingBase\Entities\Price', 'price_id');
	}

	public function contract ()
	{
		return $this->belongsTo('Modules\ProvBase\Entities\Contract');
	}

}