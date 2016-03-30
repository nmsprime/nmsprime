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
			'rcd' 	=> 'numeric|between:1,31',
			'tax' 	=> 'numeric|between:0:100'
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function get_view_header()
	{
		return 'Billing Config';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return $this->get_view_header();
	}

}
