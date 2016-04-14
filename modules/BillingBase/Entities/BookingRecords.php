<?php

namespace Modules\BillingBase\Entities;
use File;

class BookingRecords {

	/**
	 * This class stores all Booking Records in one File for one ISP Sepa Account
	 * @author Nino Ryschawy
	 */


	private $dates;					// offen used dates - created only once (constructor) for better performance
	private $records_sepa 	 = [];
	private $records_no_sepa = [];

	private $file_sepa;
	private $file_no_sepa;

	public $data = array(

		'Contractnr'	=> '',
		'Invoicenr'		=> '',
		'Date' 			=> '',
		'RCD' 			=> '',		// Requested Collection Date (Zahlungsziel)
		'Cost Center' 	=> '',
		'Description' 	=> '',
		'Net' 			=> '',
		'Tax' 			=> '',
		'Gross' 		=> '',
		'Currency' 		=> '',
		'Firstname' 	=> '',
		'Lastname' 		=> '',
		'Street' 		=> '',
		'Zip'			=> '',
		'City' 			=> '',

		'Account Holder' => '',
		'IBAN' 			=> '',
		'BIC' 			=> '',
		'MandateID' 	=> '',
		'MandateDate' 	=> ''

	);


	public function __construct($acc_name)
	{
		$this->dates = array(
			'this_m_bill' 	=> date('m/Y'), 
			'today' 		=> date('Y-m-d'),
			'auto_rcd' 		=> date('Y-m-d', strtotime('+6 days')),
			);

		$this->file_sepa 	= storage_path("billing/booking_records_sepa_".$acc_name.".txt");
		$this->file_no_sepa = storage_path("billing/booking_records_no_sepa_".$acc_name.".txt");

	}


	public function add_record($contract, $mandate, $invoice_nr, /* $started_lastm,*/ $charge, $tax, $conf)
	{
		$rcd = $conf->rcd ? $conf->rcd : $this->dates['auto_rcd'];
		$cur = $conf->currency ? $conf->currency : 'EUR';

		// $this->data['Contractnr'] 	= $contract->id;
		$this->data['Contractnr'] 	= $contract->number;
		$this->data['Invoicenr'] 	= $invoice_nr;
		$this->data['Date'] 		= $this->dates['today'];
		$this->data['RCD'] 			= $rcd;
		$this->data['Cost Center']  = isset($contract->costcenter->name) ? $contract->costcenter->name : '';
		$this->data['Description']  = 'Month '.$this->dates['this_m_bill'];
		$this->data['Net'] 			= $charge;
		$this->data['Tax'] 			= $tax;
		$this->data['Gross'] 		= $charge + $tax;
		$this->data['Currency'] 	= $cur;
		$this->data['Firstname'] 	= $contract->firstname;
		$this->data['Lastname'] 	= $contract->lastname;
		$this->data['Street'] 		= $contract->street;
		$this->data['Zip'] 			= $contract->zip;
		$this->data['City'] 		= $contract->city;
		if ($mandate)
		{
			$this->data['Account Holder'] 	= $mandate->sepa_holder;
			$this->data['IBAN']				= $mandate->sepa_iban;
			$this->data['BIC'] 				= $mandate->sepa_bic;
			$this->data['MandateID'] 		= $mandate->reference;
			$this->data['MandateDate']		= $mandate->signature_date;
			
			$this->records_sepa[] = implode("\t", $this->data)."\n";
		}
		else
		{
			$this->data['Account Holder'] 	= '';
			$this->data['IBAN']				= '';
			$this->data['BIC'] 				= '';
			$this->data['MandateID'] 		= '';
			$this->data['MandateDate']		= '';

			$this->records_no_sepa[] = implode("\t", $this->data)."\n";
		}

	}


	public function make_booking_record_files()
	{
		if ($this->records_sepa)
		{
			// initialise record files with Column names as first line
			File::put($this->file_sepa, implode("\t", array_keys($this->data))."\n");
			File::append($this->file_sepa, implode($this->records_sepa));
			echo "stored booking sepa records in ".$this->file_sepa."\n";
		}

		if ($this->records_no_sepa)
		{
			File::put($this->file_no_sepa, implode("\t", array_keys($this->data))."\n");
			File::append($this->file_no_sepa, implode($this->records_no_sepa));
			echo "stored booking no sepa records in ".$this->file_no_sepa."\n";		
		}
	}

}