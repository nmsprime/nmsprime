<?php

namespace Modules\BillingBase\Entities;
use File;

class AccountingRecords {

	private $item_records = [];
	private $tariff_records = [];

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

	public function add_item($item, $price, $text, $invoice_nr)
	{

		$this->data['Contractnr'] 	= $item->contract->id;
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

		switch ($item->product->type)
		{
			case 'Internet':
			case 'TV':
			case 'Voip':
				$this->tariff_records[] = $this->data;
				break;
			default:
				$this->item_records[] = $this->data;
				break;
		}

	}


	public function make_accounting_record_files($acc)
	{
				// 		$file = storage_path("billing/$type"."_records_".$sepa_accs->find($acc_id)->name.".txt");
				// // initialise record files with Column names as first line
				// File::put($file, implode("\t", array_keys($this->records_arr[$type]))."\n");
				// File::append($file, implode($entries));
				// echo "stored $type records in $file\n";
	}

}