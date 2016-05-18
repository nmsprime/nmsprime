<?php

namespace Modules\BillingBase\Entities;
use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\BillingLogger;

use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;
use Storage;

class SepaAccount extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'sepaaccount';

    public $guarded = ['template_upload'];

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
	 * Returns all available and template files (via directory listing)
	 * @author Nino Ryschawy
	 */
	public function templates()
	{
		$files_raw  = glob("/tftpboot/bill/template/*");
		$templates 	= array(null => "None");

		// extract filename
		foreach ($files_raw as $file) 
		{
			if (is_file($file))
			{
				$parts = explode("/", $file);
				$filename = array_pop($parts);
				$templates[$filename] = $filename;
			}
		}

		return $templates;
	}



	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		$this->invoice_nr_prefix = date('Y').'/';
		$this->logger = new BillingLogger;
	}



	/**
	 * BILLING STUFF
	 */
	public $invoice_nr = 100000; 			// invoice number counter - default start nr is replaced by global config field
	private $invoice_nr_prefix;				// see constructor
	public $dir;							// directory to store billing files
	protected $logger;


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
	protected $invoices = [];


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
	private function get_invoice_nr_formatted()
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
		// if ($item->contract_id = 500006 && $item->product->type == 'Device')
		// 	dd($item->count, $count, $item->charge);

		$data = array(
			
			// 'Contractnr' => $item->contract->id,
			'Contractnr' 	=> $item->contract->number,
			'Invoicenr' 	=> $this->get_invoice_nr_formatted(),
			'Target Month' 	=> date('m'),
			'Date' 			=> date('Y-m-d'),
			'Cost Center'  	=> isset($item->contract->costcenter->name) ? $item->contract->costcenter->name : '',
			'Count'			=> $item->count ? $item->count : '1',
			'Description'  	=> $item->invoice_description,
			'Price' 		=> $item->charge,
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

			'Contractnr' 	=> $contract->number,
			'Invoicenr' 	=> $this->get_invoice_nr_formatted(),
			'Date' 			=> date('Y-m-d'),
			'RCD' 			=> $conf->rcd ? $conf->rcd : date('Y-m-d', strtotime('+6 days')),
			'Cost Center'	=> isset($contract->costcenter->name) ? $contract->costcenter->name : '',
			'Description' 	=> '',
			'Net' 			=> $charge['net'],
			'Tax' 			=> $charge['tax'],
			'Gross' 		=> $charge['net'] + $charge['tax'],
			'Currency' 		=> $conf->currency ? $conf->currency : 'EUR',
			'Firstname' 	=> $contract->firstname,
			'Lastname' 		=> $contract->lastname,
			'Street' 		=> $contract->street,
			'Zip' 			=> $contract->zip,
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


	public function add_invoice_item($item, $conf)
	{
		if (!isset($this->invoices[$item->contract->id]))
			$this->invoices[$item->contract->id] = new Invoice($item->contract, $conf, $this->get_invoice_nr_formatted());
		$this->invoices[$item->contract->id]->add_item($item);
	}


	public function add_invoice_data($contract, $mandate, $value)
	{
		// Attention! the chronical order of these functions has to be kept until now because of dependencies for extracting the invoice text
		$this->invoices[$contract->id]->set_mandate($mandate);
		$this->invoices[$contract->id]->set_company_data($this);
		$this->invoices[$contract->id]->set_summary($value['net'], $value['tax'], $this);
	}

	/**
	 * Adds a sepa transfer for this account with the charge of a contract to the corresponding sepa_xml-Array (credit/debit)
	 *
	 * @param object 	$mandate
	 * @param float 	$charge
	 * @param array 	$dates 		last run info is important for transfer type
	 */
	public function add_sepa_transfer($mandate, $charge, $dates)
	{
		$info = 'Month '.date('m/Y');

		// Note: Charge == 0 is automatically excluded
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

		// determine transaction type: first/recurring/final
		$type  = PaymentInformation::S_RECURRING;
		$start = strtotime($mandate->sepa_valid_from);
		$end   = strtotime($mandate->sepa_valid_to);

		// new mandate - after last run
		if ($start > strtotime($dates['last_run']) && !$mandate->recurring)
			$type = PaymentInformation::S_FIRST;

		// when mandate ends next month but before billing run
		else if ($mandate->contract->expires || $end < strtotime('+1 month'))
			$type = PaymentInformation::S_FINAL;


		// NOTE: also possible with state field of mandate table - dis~/advantage: more complex code / no last run timestamp needed
		// switch ($mandate->state)
		// {
		// 	case null:

		// 		if (!$mandate->recurring)
		// 		{
		// 			$type = PaymentInformation::S_FIRST;
		// 			$mandate->state = 'FIRST';
		// 		}
		// 		else
		// 		{
		// 			$type = PaymentInformation::S_RECURRING;
		// 			$mandate->state = 'RECUR';
		// 		}

		// 		$mandate->save();
		// 		break;

		// 	case 'FIRST':
		// 		$mandate->state = 'RECUR';
		// 		$mandate->save();

		// 	case 'RECUR':
		// 		$type = PaymentInformation::S_RECURRING;

		// 	default: break;
		// }

		// if ($mandate->contract->expires || $end < strtotime('+1 month'))
		// {
		// 	$type = PaymentInformation::S_FINAL;
		// 	$mandate->state = 'FINAL';
		// 	$mandate->save();
		// }


		$data = array(
			'endToEndId'			=> 'RG '.$this->get_invoice_nr_formatted(),
			'amount'                => $charge,
			'debtorIban'            => $mandate->sepa_iban,
			'debtorBic'             => $mandate->sepa_bic,
			'debtorName'            => $mandate->sepa_holder,
			'debtorMandate'         => $mandate->reference,
			'debtorMandateSignDate' => $mandate->signature_date,
			'remittanceInformation' => $info,
		);

		$this->sepa_xml['debits'][$type][] = $data;
	}



	/**
	 * Creates the Accounting Record Files (Item/Tariff)
	 */
	private function make_accounting_record_files()
	{
		foreach ($this->acc_recs as $key => $records)
		{
			if (!$records)
				continue;

			$file = $this->dir.$this->name.'/accounting_'.$key.'_records.txt';
			$file = SepaAccount::str_sanitize($file);

			// initialise record files with Column names as first line
			Storage::put($file, implode("\t", array_keys($records[0]))."\n");

			$data = [];
			foreach ($records as $value)
				array_push($data, implode("\t", $value)."\n");

			Storage::append($file, implode($data));

			echo "stored accounting ".$key." records in $file\n";
			$this->logger->addInfo("Successfully stored accounting ".$key." records in $file \n");
		}

		return;
	}



	/**
	 * Creates the Booking Record Files (Sepa/No Sepa)
	 */
	private function make_booking_record_files()
	{
		foreach ($this->book_recs as $key => $records)
		{
			if (!$records)
				continue;

			$file = $this->dir.$this->name.'/booking_'.$key.'_records.txt';
			$file = SepaAccount::str_sanitize($file);

			// initialise record files with Column names as first line
			Storage::put($file, implode("\t", array_keys($records[0]))."\n");

			$data = [];
			foreach ($records as $value)
				array_push($data, implode("\t", $value)."\n");

			Storage::append($file, implode($data));

			echo "stored booking ".$key." records in $file\n";
			$this->logger->addInfo("Successfully stored booking ".$key." records in $file \n");
		}

		return;
	}


	private function get_sepa_xml_msg_id()
	{
		return date('YmdHis').$this->id;		// km3 uses actual time
	}


	/**
	 * Create SEPA XML Files
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
				$directDebit = TransferFileFacadeFactory::createDirectDebit($msg_id.$type, $this->name);

				// create a payment
				$directDebit->addPaymentInfo($msg_id.$type, array(
					'id'                    => $msg_id,
					'creditorName'          => $this->name,
					'creditorAccountIBAN'   => $this->iban,
					'creditorAgentBIC'      => $this->bic,
					'seqType'               => $type,
					'creditorId'            => $this->creditorid,
					// 'dueDate'				=> // requested collection date (Fälligkeits-/Ausführungsdatum) - from global config
				));

				// Add Transactions to the named payment
				foreach($records as $r)
					$directDebit->addTransfer($msg_id.$type, $r);

				// Retrieve the resulting XML
				$file = SepaAccount::str_sanitize($this->dir.$this->name.'/DD_'.$type.'.xml');
				$data = str_replace('pain.008.002.02', 'pain.008.003.02', $directDebit->asXML());
				STORAGE::put($file, $data);

				echo "stored sepa direct debit $type xml in $file \n";
				$this->logger->addInfo("Successfully stored sepa direct debit type $type xml in $file \n");
			}

			return;
		}

		// Set the initial information for direct debits
		$directDebit = TransferFileFacadeFactory::createDirectDebit($msg_id, $this->name);

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
				// 'dueDate'				=> // requested collection date (Fälligkeits-/Ausführungsdatum) - from global config
			));

			// Add Transactions to the named payment
			foreach($records as $r)
				$directDebit->addTransfer($msg_id.$type, $r);

		}

		// Retrieve the resulting XML
		$file = SepaAccount::str_sanitize($this->dir.$this->name.'/DD.xml');
		$data = str_replace('pain.008.002.02', 'pain.008.003.02', $directDebit->asXML());
		STORAGE::put($file, $data);

		echo "stored sepa direct debit xml in $file \n";
		$this->logger->addInfo("Successfully stored sepa direct debit xml in $file \n");
	}


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
		$file = SepaAccount::str_sanitize($this->dir.$this->name.'/DC.xml');
		$data = str_replace('pain.008.002.02', 'pain.008.003.02', $directDebit->asXML());
		STORAGE::put($file, $data);

		echo "stored sepa direct credit xml in $file\n";
		$this->logger->addInfo("Successfully stored sepa direct credit xml in $file \n");

	}



	/*
	 * Creates all the billing files for the assigned objects
	 */
	public function make_billing_files()
	{
		if ($this->acc_recs['tariff'] || $this->acc_recs['item'])
			$this->make_accounting_record_files();

		if ($this->book_recs['sepa'] || $this->book_recs['no_sepa'])
			$this->make_booking_record_files();

		if ($this->sepa_xml['debits'])
			$this->make_debit_file();

		if ($this->sepa_xml['credits'])
			$this->make_credit_file();
	}


	/**
	 * Simplify string for Filenames
	 * TODO: use as global helper function in other context
	 */
	public static function str_sanitize($string)
	{
		$string = str_replace(' ', '_', $string);
		return preg_replace("/[^a-zA-Z0-9.\/_]/", "", $string);
	}

}
