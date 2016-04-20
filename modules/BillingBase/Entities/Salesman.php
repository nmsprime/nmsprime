<?php

namespace Modules\BillingBase\Entities;
use Modules\ProvBase\Entities\Contract;

class Salesman extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'salesman';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			'firstname' 	=> 'required',
			'lastname' 		=> 'required',
			'provision'		=> 'required|numeric|between:0,100',
			'products' 		=> 'product',
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function get_view_header()
	{
		return 'Salesman';
	}

	// link title in index view
	public function get_view_link_title()
	{
		return $this->firstname.' '.$this->lastname;
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
			'Contract' => $this->contracts,
			);
	}



	/**
	 * Relationships:
	 */
	public function contracts ()
	{
		return $this->hasMany('Modules\ProvBase\Entities\Contract');
	}

	public function view_belongs_to()
	{
		// return $this->belongsTo('Modules\ProvBase\Entities\Contract');
	}


	/**
	 * BILLING STUFF
	 */

	/*
	 * The following functions target at adding single entries for the files and create the files finally (names are self-explaining)
	 */
	public function add_accounting_record($item, $price, $text)
	{
		// write to accounting records of account
		if (!isset($this->acc_recs))
			$this->acc_recs = new AccountingRecords($this->name);

		$invoice_nr = $this->invoice_nr_template.$this->id.'/'.$this->invoice_nr;

		$this->acc_recs->add_item($item, $price, $text, $invoice_nr);
	}



}
