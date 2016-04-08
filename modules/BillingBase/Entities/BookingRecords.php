<?php

namespace Modules\BillingBase\Entities;
use File;

class BookingRecords {

	private $dates;					// offen used dates - created only once (constructor) for better performance
	private $records_sepa 	 = [];
	private $records_no_sepa = [];

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


	public function __construct()
	{
		$this->dates = array(
			'this_m_bill' 	=> date('m/Y'), 
			'today' 		=> date('Y-m-d'),
			'auto_rcd' 		=> date('Y-m-d', strtotime('+6 days')),
			);
	}


	public function add_record($contract, $mandate, $invoice_nr, /* $started_lastm,*/ $charge, $tax, $conf)
	{
		$rcd = $conf->rcd ? $conf->rcd : $this->dates['auto_rcd'];
		$cur = $conf->currency ? $conf->currency : 'EUR';

		// $tax = $conf->tax ? $conf->tax / 100 : 0.19;
		// $txt = '';
		// if ($started_lastm)
		// 	$txt = date('m', strtotime('-1 month')).'+';

		$this->data['Contractnr'] 	= $contract->id;
		$this->data['Invoicenr'] 	= $invoice_nr;
		$this->data['Date'] 		= $this->dates['today'];
		$this->data['RCD'] 			= $rcd;
		$this->data['Cost Center']  = isset($contract->costcenter->name) ? $contract->costcenter->name : '';
		$this->data['Description']  = 'Month '.$this->dates['this_m_bill'];
		$this->data['Net'] 			= $charge - $tax;
		$this->data['Tax'] 			= $tax;
		$this->data['Gross'] 		= $charge;
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
			
			$records_sepa[] = $this->data;
		}
		else
		{
			$this->data['Account Holder'] 	= '';
			$this->data['IBAN']				= '';
			$this->data['BIC'] 				= '';
			$this->data['MandateID'] 		= '';
			$this->data['MandateDate']		= '';

			$records_no_sepa[] = $this->data;
		}

	}


	public function make_booking_record_file($acc)
	{
		// return implode("\t", $arr)."\n";
				// $file = storage_path("billing/$type"."_records_".$sepa_accs->find($acc_id)->name.".txt");
				// // initialise record files with Column names as first line
				// File::put($file, implode("\t", array_keys($this->records_arr[$type]))."\n");
				// File::append($file, implode($entries));
				// echo "stored $type records in $file\n";
	}

}