<?php

namespace Modules\BillingBase\Entities;

use File;
use Modules\BillingBase\Entities\BillingLogger;

class AccountingRecords {

	/**
	 * This class stores all Accounting Records in one File for one ISP Sepa Account
	 * @author Nino Ryschawy
	 */

	private $item_records = [];
	private $tariff_records = [];

	private $file_items;
	private $file_tariffs;

	protected $logger;

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

	public function __construct()
	{
		$this->logger = new BillingLogger;
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


	public function make_accounting_record_files($dir, $acc_name)
	{
		if ($this->item_records)
		{
			$file = $dir.'accounting_item_records_'.$acc_name.'.txt';

			// initialise record files with Column names as first line
			File::put($file, implode("\t", array_keys($this->data))."\n");
			File::append($file, implode($this->item_records));

			echo "stored accounting item records in ".$file."\n";
			$this->logger->addInfo("Successfully stored accounting item records in $file \n");
		}


		if ($this->tariff_records)
		{
			$file = $dir.'accounting_item_records_'.$acc_name.'.txt';

			File::put($file, implode("\t", array_keys($this->data))."\n");
			File::append($file, implode($this->tariff_records));

			echo "stored accounting tariff records in ".$file."\n";
			$this->logger->addInfo("Successfully stored accounting tariff records in $file \n");
		}
	}


}