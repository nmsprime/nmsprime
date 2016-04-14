<?php

namespace Modules\BillingBase\Entities;
use File;

class AccountingRecords {

	/**
	 * This class stores all Accounting Records in one File for one ISP Sepa Account
	 * @author Nino Ryschawy
	 */

	private $item_records = [];
	private $tariff_records = [];

	private $file_items;
	private $file_tariffs;

	public $data = array(

		'Contractnr' 	=> '',
		'Invoicenr' 	=> '',
		'Target Month'  => '',
		'Date' 			=> '',
		'Cost Center' 	=> '',
		'Count' 		=> '',
		'Description' 	=> '',
		'Price' 		=> '',
		'Firstname' 	=> '',
		'Lastname' 		=> '',
		'Street' 		=> '',
		'Zip' 			=> '',
		'City' 			=> '',

	);


	public function __construct($acc_name)
	{
		$this->file_items 	= storage_path("billing/accounting_item_records_".$acc_name.".txt");
		$this->file_tariffs = storage_path("billing/accounting_tariff_records_".$acc_name.".txt");
	}


	public function add_item($item, $price, $text, $invoice_nr)
	{
		// $this->data['Contractnr'] 	= $item->contract->id;
		$this->data['Contractnr'] 	= $item->contract->number;
		$this->data['Invoicenr'] 	= $invoice_nr;
		$this->data['Target Month'] = date('m');
		$this->data['Date'] 		= date('Y-m-d');
		$this->data['Cost Center']  = isset($item->contract->costcenter->name) ? $item->contract->costcenter->name : '';
		$this->data['Count'] 		= $item->count ? $item_count : '1';
		$this->data['Description']  = $text;
		$this->data['Price'] 		= $price;
		$this->data['Firstname'] 	= $item->contract->firstname;
		$this->data['Lastname'] 	= $item->contract->lastname;
		$this->data['Street'] 		= $item->contract->street;
		$this->data['Zip'] 			= $item->contract->zip;
		$this->data['City'] 		= $item->contract->city;

		$data = implode("\t", $this->data)."\n";

		switch ($item->product->type)
		{
			case 'Internet':
			case 'TV':
			case 'Voip':
				$this->tariff_records[] = $data;
				break;
			default:
				$this->item_records[] = $data;
				break;
		}

	}


	public function make_accounting_record_files()
	{
		if ($this->item_records)
		{
			// initialise record files with Column names as first line
			File::put($this->file_items, implode("\t", array_keys($this->data))."\n");
			File::append($this->file_items, implode($this->item_records));
			echo "stored accounting item records in ".$this->file_items."\n";			
		}

		if ($this->tariff_records)
		{
			File::put($this->file_tariffs, implode("\t", array_keys($this->data))."\n");
			File::append($this->file_tariffs, implode($this->tariff_records));
			echo "stored accounting tariff records in ".$this->file_tariffs."\n";
		}

	}

}