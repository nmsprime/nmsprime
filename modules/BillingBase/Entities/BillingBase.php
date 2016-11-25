<?php

namespace Modules\BillingBase\Entities;
use Modules\ProvBase\Entities\Contract;

class BillingBase extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'billingbase';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			// 'rcd' 	=> 'numeric|between:1,28',
			'cdr_offset'						=> 'numeric|between:0,11',
			'tax' 								=> 'numeric|between:0,100',
			'voip_extracharge_default' 			=> 'numeric',
			'voip_extracharge_mobile_national' 	=> 'numeric',
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function view_headline()
	{
		return 'Billing Config';
	}

	// link title in index view
	public function view_index_label()
	{
		return $this->view_headline();
	}

}
