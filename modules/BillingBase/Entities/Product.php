<?php

namespace Modules\BillingBase\Entities;
use Modules\BillingBase\Entities\SepaAccount;

class Product extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'product';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			'name' 	=> 'required|unique:product,name,'.$id.',id,deleted_at,NULL',
			'type' 	=> "required|not_null",
			// 'type' => "required|not_null|unique:product,type,$id,id,type,Credit,deleted_at,NULL",	// if credit shall exist only once
			'voip_tariff' => 'required_if:type,Voip',
			'qos_id' => 'required_if:type,Internet',
			'price'  => 'required_if:type,Internet,Voip,TV,Other,Device,Mixed',
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function get_view_header()
	{
		return 'Product Entry';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return $this->type.' - '.$this->name.' | '.$this->price.' €';
	}

	// Return a pre-formated index list
	public function index_list ()
	{
		return $this->orderBy('type')->get();
	}


	/**
	 * Relationships:
	 */
	public function quality ()
	{
		return $this->belongsTo('Modules\ProvBase\Entities\Qos', 'qos_id');
	}

	public function item ()
	{
		return $this->hasMany('Modules\BillingBase\Entities\Item');
	}

	public function costcenter ()
	{
		return $this->belongsTo('Modules\BillingBase\Entities\CostCenter', 'costcenter_id');
	}

}


