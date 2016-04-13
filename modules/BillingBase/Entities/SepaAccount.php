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
			'creditorid' => 'required|max:18',
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
			'CostCenter' => $this->costcenters,
			);
	}


	/**
	 * Relationships:
	 */
	public function costcenters ()
	{
		return $this->hasMany('Modules\BillingBase\Entities\CostCenter');
	}

	public function company ()
	{
		return $this->belongsTo('Modules\BillingBase\Entities\Company');
	}


	/**
	 * BILLING STUFF
	 *
	 * Every Account has following Objects assigned
	 * Following functions target at adding single entries for the files and create the files finally
	 */

	public $logger;			// logging instance of billing module
	public $invoice_nr; 	// invoice number counter

	// separate Accounting Records Object - resulting in 2 files for items and tv
	protected $acc_recs;

	// Booking Records Object - resulting in 2 files for records with sepa mandate or without
	protected $book_recs;

	// several Bills for every Contract that contain only the products/items that are related through the costcenter
	protected $bills = [];

	// Sepa XML Object - resulting in 2 possible files for direct debits or credits
	protected $sepa_xml;


	public function add_accounting_record($item, $price, $text)
	{
		// write to accounting records of account
		if (!isset($this->acc_recs))
			$this->acc_recs = new AccountingRecords($this->name);

		$invoice_nr = $this->id.'/'.$this->invoice_nr;

		$this->acc_recs->add_item($item, $price, $text, $invoice_nr);
	}


	public function add_booking_record($c, $mandate, $value, $conf)
	{
		if (!isset($this->book_recs))
			$this->book_recs = new BookingRecords($this->name);

		$invoice_nr = $this->id.'/'.$this->invoice_nr;

		$this->book_recs->add_record($c, $mandate, $invoice_nr, $value['gross'], $value['tax'], $conf);
	}


	public function add_bill_item($c, $conf, $count, $price, $text)
	{
		if (!isset($this->bills[$c->id]))
			$this->bills[$c->id] = new Bill($c, $conf, $this->id.'/'.$this->invoice_nr, $this->logger);
		$this->bills[$c->id]->add_item($count, $price, $text);
	}


	public function add_bill_data($c, $mandate, $value, $logger)
	{
		$this->bills[$c->id]->set_mandate($mandate);
		$this->bills[$c->id]->set_summary($value['gross'], $value['tax']);
		if (!$this->bills[$c->id]->set_company_data($this))
			$logger->addError('No Company assigned to Account '.$this->name);
	}


	public function add_sepa_transfer($mandate, $value, $dates)
	{
		if (!isset($this->sepa_xml))
			$this->sepa_xml = new Sepaxml($this);

		$invoice_nr = $this->id.'/'.$this->invoice_nr;

		$this->sepa_xml->add_entry($mandate, $value, $dates, $invoice_nr);
	}


	public function make_billing_files()
	{
		if (isset($this->acc_recs))
			$this->acc_recs->make_accounting_record_files();
		if (isset($this->book_recs))
			$this->book_recs->make_booking_record_files();
		if (isset($this->sepa_xml))
			$this->sepa_xml->make_sepa_xml();
	}


}
