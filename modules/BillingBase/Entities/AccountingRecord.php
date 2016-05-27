<?php

namespace Modules\BillingBase\Entities;

use File;
use Modules\BillingBase\Entities\BillingLogger;

class AccountingRecord extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'accounting_record';


	/**
	 * Stores a Record in the database - TODO: move to controller!
	 */
	public function store($item, $acc)
	{
		$count = $item->count ? $item->count : 1;

		$data = array(
			'contract_id' 	=> $item->contract->id,
			'name'			=> $item->product->name,
			'product_id'	=> $item->product->id,
			'ratio'			=> $item->ratio,
			'count'			=> $count,
			'charge'		=> $item->charge,
			'invoice_nr'	=> $acc->invoice_nr,
			'sepa_account_id' => $acc->id,
			);

		$this->create($data);
	}


}