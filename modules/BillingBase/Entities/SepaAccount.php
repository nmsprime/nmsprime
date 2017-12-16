<?php

namespace Modules\BillingBase\Entities;
use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\BillingBase;

use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Storage;
use IBAN;
use \App\Http\Controllers\BaseViewController;
use ChannelLog;

/**
 * Contains the functionality for Creating the SEPA-XML-Files of a SettlementRun
 *
 * TODO: implement translations with trans() instead of translate_label()-Function
 *
 * @author Nino Ryschawy
 */
class SepaAccount extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'sepaaccount';

    public $guarded = ['template_invoice_upload', 'template_cdr_upload'];

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			'name' 		=> 'required',
			'holder' 	=> 'required',
			'creditorid' => 'required|max:35|creditor_id',
			'iban' 		=> 'required|iban',
			'bic' 		=> 'bic',
		);
	}


	/**
	 * View related stuff
	 */

	// Name of View
	public static function view_headline()
	{
		return 'SEPA Account';
	}

	public static function view_icon()
	{
		return '<i class="fa fa-credit-card"></i>';
	}

	// link title in index view
	public function view_index_label()
	{
		return ['index' => [$this->name, $this->institute, $this->iban],
		        'index_header' => ['Name', 'Institute', 'IBAN'],
				'header' => $this->name];
	}

	// AJAX Index list function
	// generates datatable content and classes for model
	public function view_index_label_ajax()
	{
		return ['table' => $this->table,
				'index_header' => [$this->table.'.name', $this->table.'.institute', $this->table.'.iban'],
				'order_by' => ['0' => 'asc'],  // columnindex => direction
				'header' =>  $this->name];
	}

	// View Relation.
	public function view_has_many()
	{
		return array(
			'CostCenter' => $this->costcenters,
			);
	}

	public function view_belongs_to ()
	{
		return $this->company;
	}

	/**
	 * Relationships:
	 */
	public function costcenters ()
	{
		return $this->hasMany('Modules\BillingBase\Entities\CostCenter', 'sepaaccount_id');
	}

	public function company ()
	{
		return $this->belongsTo('Modules\BillingBase\Entities\Company');
	}



	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		$this->invoice_nr_prefix = date('Y', strtotime("first day of last month")).'/';
	}



	/**
	 * BILLING STUFF
	 */
	public $invoice_nr = 100000; 			// invoice number counter - default start nr is replaced by global config field
	private $invoice_nr_prefix;				// see constructor
	public $dir;							// directory to store billing files
	public $rcd; 							// requested collection date from global config


	/**
	 * get billing user language
	 * returns language letters selected in Billing Base Config
	 * @author Christian Schramm
	 */
	private function _get_billing_lang()
	{
		return \App::getLocale();
	}


	/**
	 * Accounting Records
		* resulting in 2 files for items and tariffs
	 	* Filestructure is defined in add_accounting_record()-function
	 * @var array
	 */
	protected $acc_recs = array('tariff' => [], 'item' => []);


	/**
	 * Booking Records
		* resulting in 2 files for records with sepa mandate or without
		* Filestructure is defined in add_booking_record()-function
	 * @var array
	 */
	protected $book_recs = array('sepa' => [], 'no_sepa' => []);


	/**
	 * Invoices for every Contract that contain only the products/items that have to be paid to this account
	 	* (related through costcenter)
		* each entry results in one invoice pdf file
	 * @var array
	 */
	public $invoices = [];


	/**
	 * Sepa XML
		* resulting in 2 possible files for direct debits or credits
	 * @var array
	 */
	protected $sepa_xml = array('debits' => [], 'credits' => []);




	/**
	 * Returns composed invoice nr string
	 *
	 * @return String
	 */
	private function _get_invoice_nr_formatted()
	{
		return $this->invoice_nr_prefix.$this->id.'/'.$this->invoice_nr;
	}


	/**
	 * Adds an accounting record for this account of an item to the corresponding acc_recs-Array (item/tariff)
	 *
	 * @param object 	$item
	 */
	public function add_accounting_record($item)
	{
		$time = strtotime('last day of last month');

		$data = array(
			'Contractnr' 	=> $item->contract->number,
			'Invoicenr' 	=> $this->_get_invoice_nr_formatted(),
			'Target Month'  => date('m', $time),
			'Date'			=> ($this->_get_billing_lang() == 'de') ? date('d.m.Y', $time) : date('Y-m-d', $time),
			'Cost Center'   => isset($item->contract->costcenter->name) ? $item->contract->costcenter->name : '',
			'Count'			=> $item->count,
			'Description' 	=> $item->invoice_description,
			'Price'			=> ($this->_get_billing_lang() == 'de') ? number_format($item->charge, 2 , ',' , '.' ) : number_format($item->charge, 2 , '.' , ',' ),
			'Firstname'		=> $item->contract->firstname,
			'Lastname' 		=> $item->contract->lastname,
			'Street' 		=> $item->contract->street,
			'Zip' 			=> $item->contract->zip,
			'City' 			=> $item->contract->city,
		);

		switch ($item->product->type)
		{
			case 'Internet':
			case 'TV':
			case 'Voip':
				$this->acc_recs['tariff'][] = $data;
				break;
			default:
				$this->acc_recs['item'][] = $data;
				break;
		}

		return;
	}


	/**
	 * Adds a booking record for this account with the charge of a contract to the corresponding book_recs-Array (sepa/no_sepa)
	 *
	 * @param object 	$contract, $mandate, $conf
	 * @param float 	$charge
	 */
	public function add_booking_record($contract, $mandate, $charge, $conf)
	{
		$data = array(
			'Contractnr'	=> $contract->number,
			'Invoicenr' 	=> $this->_get_invoice_nr_formatted(),
			'Date' 			=> ($this->_get_billing_lang() == 'de') ? date('d.m.Y', strtotime('last day of last month')) : date('Y-m-d', strtotime('last day of last month')),
			'RCD' 			=> $this->rcd,
			'Cost Center' 	=> isset($contract->costcenter->name) ? $contract->costcenter->name : '',
			'Description' 	=> '',
			'Net' 			=> ($this->_get_billing_lang() == 'de') ? number_format($charge['net'], 2 , ',' , '.' ) : number_format($charge['net'], 2 , '.' , ',' ),
			'Tax' 			=> $charge['tax'],
			'Gross' 		=> ($this->_get_billing_lang() == 'de') ? number_format($charge['net'] + $charge['tax'], 2 , ',' , '.' ) : number_format($charge['net'] + $charge['tax'], 2 , '.' , ',' ),
			'Currency' 		=> $conf->currency ? $conf->currency : 'EUR',
			'Firstname' 	=> $contract->firstname,
			'Lastname' 		=> $contract->lastname,
			'Street' 		=> $contract->street,
			'Zip'			=> $contract->zip,
			'City' 			=> $contract->city,
			);

		if ($mandate)
		{
			$data2 = array(
				'Account Holder' => $mandate->sepa_holder,
				'IBAN'			=> $mandate->sepa_iban,
				'BIC' 			=> $mandate->sepa_bic,
				'MandateID' 	=> $mandate->reference,
				'MandateDate'	=> $mandate->signature_date,
			);

			$data = array_merge($data, $data2);

			$this->book_recs['sepa'][] = $data;
		}
		else
			$this->book_recs['no_sepa'][] = $data;

		return;
	}


	public function add_cdr_accounting_record($contract, $charge, $count)
	{
		$this->acc_recs['tariff'][] = array(
			'Contractnr' 	=> $contract->number,
			'Invoicenr' 	=> $this->_get_invoice_nr_formatted(),
			'Target Month'  => date('m'),
			'Date' 			=> ($this->_get_billing_lang() == 'de') ? date('d.m.Y') : date('Y-m-d'),
			'Cost Center'   => isset($contract->costcenter->name) ? $contract->costcenter->name : '',
			'Count'			=> $count,
			'Description'   => 'Telephone Calls',
			'Price' 		=> $this->_get_billing_lang() == 'de' ? number_format($charge, 2, ',', '.') : $charge,
			'Firstname'		=> $contract->firstname,
			'Lastname' 		=> $contract->lastname,
			'Street' 		=> $contract->street,
			'Zip' 			=> $contract->zip,
			'City' 			=> $contract->city,
			);
	}


	public function add_invoice_item($item, $conf, $settlementrun_id)
	{
		if (!isset($this->invoices[$item->contract->id]))
		{
			$this->invoices[$item->contract->id] = new Invoice;
			$this->invoices[$item->contract->id]->settlementrun_id = $settlementrun_id;
			$this->invoices[$item->contract->id]->add_contract_data($item->contract, $conf, $this->_get_invoice_nr_formatted());
		}

		$this->invoices[$item->contract->id]->add_item($item);
	}

	public function add_invoice_cdr($contract, $cdrs, $conf, $settlementrun_id)
	{
		if (!isset($this->invoices[$contract->id]))
		{
			$this->invoices[$contract->id] = new Invoice;
			$this->invoices[$contract->id]->settlementrun_id = $settlementrun_id;
			$this->invoices[$contract->id]->add_contract_data($contract, $conf, $this->_get_invoice_nr_formatted());
		}

		$this->invoices[$contract->id]->add_cdr_data($cdrs, $conf);
	}


	/**
	 * Set Invoice Data (Mandate, Company, Amount to charge) for invoice (of contract) that belongs to this SepaAccount
	 */
	public function set_invoice_data($contract, $mandate, $value)
	{
		// Attention! the chronical order of these functions has to be kept until now because of dependencies for extracting the invoice text
		$this->invoices[$contract->id]->set_mandate($mandate);
		$this->invoices[$contract->id]->set_company_data($this);
		$this->invoices[$contract->id]->set_summary($value['net'], $value['tax'], $this);
	}

	/**
	 * Adds a sepa transfer for this account with the charge for a contract to the corresponding sepa_xml-Array (credit/debit)
	 *
	 * @param object 	$mandate
	 * @param float 	$charge
	 * @param array 	$dates 		last run info is important for transfer type
	 */
	public function add_sepa_transfer($mandate, $charge, $dates)
	{
		if ($charge == 0)
			return;

		$info = $this->company->name.' - ';
		$info .= trans('messages.month').' '.date('m/Y', strtotime('first day of last month'));

		// Credits
		if ($charge < 0)
		{
			$data = array(
				'amount'                => $charge * (-1),
				'creditorIban'          => $mandate->sepa_iban,
				'creditorBic'           => $mandate->sepa_bic,
				'creditorName'          => $mandate->sepa_holder,
				'remittanceInformation' => $info,
			);

			$this->sepa_xml['credits'][] = $data;

			return;
		}

		// Debits
		$data = array(
			'endToEndId'			=> 'RG '.$this->_get_invoice_nr_formatted(),
			'amount'                => $charge,
			'debtorIban'            => $mandate->sepa_iban,
			'debtorBic'             => $mandate->sepa_bic,
			'debtorName'            => $mandate->sepa_holder,
			'debtorMandate'         => $mandate->reference,
			'debtorMandateSignDate' => $mandate->signature_date,
			'remittanceInformation' => $info,
		);

		// determine transaction type: first/recurring/...
		$state = $mandate->state;
		$mandate->update_status();

		$this->sepa_xml['debits'][$state][] = $data;
	}



	/**
	 * Creates currently 4 files
	 	* the Accounting Record Files (Item/Tariff)
	 	* the Booking Record Files (Sepa/No Sepa)
	 *
	 * @author Nino Ryschavy, Christian Schramm
	 * edit: filenames are language specific
	 */
	private function make_billing_record_files()
	{
		$files['accounting'] = $this->acc_recs;
		$files['booking'] 	 = $this->book_recs;

		foreach ($files as $key1 => $file)
		{
			foreach ($file as $key => $records)
			{
				if (!$records)
					continue;

				$accounting = BaseViewController::translate_label($key1);
				$rec 		= $this->_get_billing_lang() == 'de' ? '' : '_records';

				$file = $this->dir.$this->name.'/'.$accounting.'_'.BaseViewController::translate_label($key).$rec.'.txt';
				$file = str_sanitize($file);

				// initialise record files with Column names as first line
				$keys = [];
				foreach (array_keys($records[0]) as $col)
					$keys[] = BaseViewController::translate_label($col);
				Storage::put($file, implode("\t", $keys));

				$data = [];
				foreach ($records as $value)
					array_push($data, implode("\t", $value)."\n");

				Storage::append($file, implode($data));

				$this->_log("$key1 $key records", $file);
			}
		}

		return;
	}


	/*
	 * Writes Paths of stored files to Logfiles and Console
	 */
	private function _log($name, $pathname)
	{
		$path = storage_path('app/');
		// echo "Stored $name in $path"."$pathname\n";
		ChannelLog::debug('billing', "Successfully stored $name in $path"."$pathname \n");
	}


	private function get_sepa_xml_msg_id()
	{
		return date('YmdHis').$this->id;		// km3 uses actual time
	}


	/**
	 * Create SEPA XML File for direct debits
	 */
	private function make_debit_file()
	{
		if (!$this->sepa_xml['debits'])
			return;

		$msg_id = $this->get_sepa_xml_msg_id();
		$conf   = BillingBase::first();

		if ($conf->split)
		{
			foreach ($this->sepa_xml['debits'] as $type => $records)
			{
				// Set the initial information for direct debits
				$directDebit = TransferFileFacadeFactory::createDirectDebit($msg_id.$type, $this->name, 'pain.008.003.02');

				// create a payment
				$directDebit->addPaymentInfo($msg_id.$type, array(
					'id'                    => $msg_id,
					'creditorName'          => $this->name,
					'creditorAccountIBAN'   => $this->iban,
					'creditorAgentBIC'      => $this->bic,
					'seqType'               => $type,
					'creditorId'            => $this->creditorid,
					'dueDate'				=> $this->rcd,
				));

				// Add Transactions to the named payment
				foreach($records as $r)
					$directDebit->addTransfer($msg_id.$type, $r);

				// Retrieve the resulting XML
				$file = str_sanitize($this->dir.$this->name.'/DD_'.$type.'.xml');
				// $data = str_replace('pain.008.002.02', 'pain.008.003.02', $directDebit->asXML());
				Storage::put($file, $directDebit->asXML());

				$this->_log("sepa direct debit $type xml", $file);
			}

			return;
		}

		// Set the initial information for direct debits
		$directDebit = TransferFileFacadeFactory::createDirectDebit($msg_id, $this->name, 'pain.008.003.02');

		foreach ($this->sepa_xml['debits'] as $type => $records)
		{
			// create a payment
			$directDebit->addPaymentInfo($msg_id.$type, array(
				'id'                    => $msg_id,
				'creditorName'          => $this->name,
				'creditorAccountIBAN'   => $this->iban,
				'creditorAgentBIC'      => $this->bic,
				'seqType'               => $type,
				'creditorId'            => $this->creditorid,
				'dueDate'				=> $this->rcd, // requested collection date (Fälligkeits-/Ausführungsdatum) - from global config
			));

			// Add Transactions to the named payment
			foreach($records as $r)
				$directDebit->addTransfer($msg_id.$type, $r);
		}

		// Retrieve the resulting XML
		$file = str_sanitize($this->dir.$this->name.'/'.BaseViewController::translate_label('DD').'.xml');
		Storage::put($file, $directDebit->asXML());

		$this->_log("sepa direct debit $type xml", $file);
	}


	/**
	 * Create SEPA XML File for direct credits
	 */
	private function make_credit_file()
	{
		if (!$this->sepa_xml['credits'])
			return;

		$msg_id = $this->get_sepa_xml_msg_id();

		// Set the initial information for direct credits
		$customerCredit = TransferFileFacadeFactory::createCustomerCredit($msg_id.'C', $this->name);

		$customerCredit->addPaymentInfo($msg_id.'C', array(
			'id'                      => $msg_id.'C',
			'debtorName'              => $this->name,
			'debtorAccountIBAN'       => $this->iban,
			'debtorAgentBIC'          => $this->bic,
		));

		// Add Transactions to the named payment
		foreach($this->sepa_xml['credits'] as $r)
			$customerCredit->addTransfer($msg_id.'C', $r);

		// Retrieve the resulting XML
		$file = str_sanitize($this->dir.$this->name.'/'.BaseViewController::translate_label('DC').'.xml');
		Storage::put($file, $customerCredit->asXML());

		$this->_log("sepa direct credit xml", $file);
	}



	/*
	 * Creates all the billing files for the assigned objects
	 */
	public function make_billing_files()
	{
		$this->make_billing_record_files();

		if ($this->sepa_xml['debits'])
			$this->make_debit_file();

		if ($this->sepa_xml['credits'])
			$this->make_credit_file();
	}




	/**
	 * Returns BIC from iban and parsed config/data-file
	 */
	public static function get_bic($iban)
	{
		$iban 	 = new IBAN(strtoupper($iban));
		$country = strtolower($iban->Country());
		$bank 	 = $iban->Bank();
		$csv 	 = 'config/billingbase/bic_'.$country.'.csv';

		if (!file_exists(storage_path('app/'.$csv)))
			return '';

		$data   = Storage::get($csv);
		$data_a = explode("\n", $data);

		foreach ($data_a as $key => $entry)
		{
			if (strpos($entry, $bank) !== false)
			{
				$entry = explode(',', $entry);
				return $entry[3];
			}
		}
	}

}
