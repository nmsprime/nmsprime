<?php

namespace Modules\BillingBase\Entities;

use Storage;
use Modules\BillingBase\Entities\BillingLogger;



/**
 * Contains Functions to collect Data for Invoice & create the corresponding PDFs
 *
 * TODO: Translate for multiple language support, improve functional structure
 *
 * @author Nino Ryschawy
 */

class Invoice extends \BaseModel{

	public $table = 'invoice';
	public $observer_enabled = false;

	private $currency;
	private $tax;


	/**
	 * View Stuff
	 */
	public static function view_headline()
	{
		return 'Invoices';
	}

	public function view_index_label()
	{
		$bsclass = 'info';

		$type = $this->type == 'Invoice' ? '' : ' ('.trans('messages.Call Data Record').')';

		return ['index' => [$this->type, $this->year, $this->month],
				'index_header' => ['Type', 'Year', 'Month'],
				'bsclass' => $bsclass,
				'header' => $this->year.' - '.$this->month.$type];
	}

	/**
	 * Relations
	 */
	public function contract()
	{
		return $this->belongsTo('Modules\ProvBase\Entities\Contract');
	}




	/**
	 * @var strings  - template file paths relativ to Storage app path
	 */
	private $template_invoice_path  = 'config/billingbase/template/';
	private $template_cdr_path 		= 'config/billingbase/template/';
	private $logo_path 				= 'config/billingbase/logo/';

	private $filename_invoice;
	private $filename_cdr;

	/**
	 * @var string - Directory to store Invoice pdf - relativ to storage path; completed in constructor by contract id
	 */
	private $rel_storage_invoice_dir = 'data/billingbase/invoice/';

	/**
	 * @var object - logger for Billing Module - instantiated in constructor
	 */
	private $logger;

	/**
	 * @var array 	Call Data Records
	 */
	public $cdrs;

	/**
	 * @var bool 	Error Flag - if set then invoice cant be created
	 */
	private $error_flag = false;

	/**
	 * @var array 	All the data used to fill the invoice template file
	 */
	public $data = array(

		// Company
		'company_name'			=> '',
		'company_street'		=> '',
		'company_zip'			=> '',	
		'company_city'			=> '',
		'company_phone'			=> '',
		'company_fax'			=> '',
		'company_mail'			=> '',
		'company_web'			=> '',
		'company_registration_court' => '', 	// all 3 fields together separated by tex newline ('\\\\')
		'company_registration_court_1' => '',
		'company_registration_court_2' => '',
		'company_registration_court_3' => '',
		'company_management' 	=> '',
		'company_directorate' 	=> '',
		'company_web'			=> '',
		'company_tax_id_nr' 	=> '',
		'company_tax_nr' 		=> '',
		'company_logo'			=> '',

		// SepaAccount
		'company_creditor_id' 	=> '',
		'company_account_institute' => '',
		'company_account_iban'  => '',
		'company_account_bic' 	=> '',

		// Contract
		'contract_id' 			=> '',
		'contract_nr' 			=> '',
		'contract_firstname' 	=> '',
		'contract_lastname' 	=> '',
		'contract_street' 		=> '',
		'contract_zip' 			=> '',
		'contract_city' 		=> '',

		'contract_mandate_iban'	=> '', 			// iban of the customer
		'contract_mandate_ref'	=> '', 			// mandate reference of the customer

		'date_invoice'			=> '',
		'invoice_nr' 			=> '',
		'invoice_text'			=> '',			// appropriate invoice text from company dependent of total charge & sepa mandate as table with sepa mandate info
		'invoice_msg' 			=> '', 			// invoice text without sepa mandate information
		'invoice_headline'		=> '',
		'rcd' 					=> '',			// Fälligkeitsdatum
		'cdr_month'				=> '', 			// Month of Call Data Records

		// Charges
		'item_table_positions'  => '', 			// tex table of all items to be charged for this invoice
		'cdr_table_positions'	=> '',			// tex table of all call data records
		'table_summary' 		=> '', 			// preformatted table - use following three keys to set table by yourself
		'table_sum_charge_net'  => '', 			// net charge - without tax
		'table_sum_tax_percent' => '', 			// The tax percentage with % character
		'table_sum_tax' 		=> '', 			// The tax
		'table_sum_charge_total' => '', 		// total charge - with tax

	);


	public function __construct($attributes = array())
	{
		$this->logger = new BillingLogger;
		
		parent::__construct($attributes);
	}


	private function _get_invoice_dir_path()
	{
		return storage_path('app/'.$this->rel_storage_invoice_dir.$this->data['contract_id'].'/');
	}


	public function get_rel_invoice_dir_path()
	{
		return $this->rel_storage_invoice_dir;
	}


	public function add_contract_data($contract, $config, $invoice_nr)
	{
		$this->data['contract_id'] 			= $contract->id;
		$this->data['contract_nr'] 			= $contract->number;
		$this->data['contract_firstname'] 	= $contract->firstname;
		$this->data['contract_lastname'] 	= $contract->lastname;
		$this->data['contract_street'] 		= $contract->street.' '.$contract->house_number;
		$this->data['contract_zip'] 		= $contract->zip;
		$this->data['contract_city'] 		= $contract->city;

		$this->data['rcd'] 			= $config->rcd ? date($config->rcd.'.m.Y') : date('d.m.Y', strtotime('+5 days'));
		$this->data['invoice_nr'] 	= $invoice_nr ? $invoice_nr : $this->data['invoice_nr'];
		$this->data['date_invoice'] = date('d.m.Y', strtotime('last day of last month'));

		// TODO: Add other currencies here
		$this->currency	= strtolower($config->currency) == 'eur' ? '€' : $config->currency;
		$this->tax		= $config->tax;
	}


	public function add_item($item) 
	{
		// $count = $item->count ? $item->count : 1;
		$price  = sprintf("%01.2f", round($item->charge/$item->count, 2));
		$sum 	= sprintf("%01.2f", $item->charge);
		$this->data['item_table_positions'] .= $item->count.' & '.$item->invoice_description.' & '.$price.$this->currency.' & '.$sum.$this->currency.'\\\\';
	}


	public function set_mandate($mandate)
	{
		if (!$mandate)
			return;

		$this->data['contract_mandate_iban'] = $mandate->sepa_iban;
		$this->data['contract_mandate_ref']  = $mandate->reference;
	}


	/**
	 * Set total sum and invoice text for this invoice - TODO: Translate!!
	 */
	public function set_summary($net, $tax, $account)
	{
		$tax_percent = $tax ? $this->tax : 0;
		$tax_percent .= '\%';

		$total  = sprintf("%01.2f", $net + $tax);
		$net 	= sprintf("%01.2f", $net);
		$tax 	= sprintf("%01.2f", $tax);

		$this->data['table_summary'] = '~ & Gesamtsumme: & ~ & '.$net.$this->currency.'\\\\';
		$this->data['table_summary'] .= "~ & $tax_percent MwSt: & ~ & ".$tax.$this->currency.'\\\\';
		$this->data['table_summary'] .= '~ & \textbf{Rechnungsbetrag:} & ~ & \textbf{'.$total.$this->currency.'}\\\\';

		$this->data['table_sum_charge_net']  	= $net; 
		$this->data['table_sum_tax_percent'] 	= $tax_percent;
		$this->data['table_sum_tax'] 			= $tax;
		$this->data['table_sum_charge_total'] 	= $total; 


		// make transfer reason (Verwendungszweck)
		if ($transfer_reason = $account->company->transfer_reason)
		{
			preg_match_all('/(?<={)[^}]*(?=})/', $transfer_reason, $matches);
			foreach ($matches[0] as $value)
			{
				if (array_key_exists($value, $this->data))
					$transfer_reason = str_replace('{'.$value.'}', $this->data[$value], $transfer_reason);
			}
		}
		else
			$transfer_reason = $this->data['invoice_nr'].' '.$this->data['contract_nr'];		// default

		// prepare invoice text table and get appropriate template
		if ($net >= 0 && $this->data['contract_mandate_iban'])
		{
			$template = $account->invoice_text_sepa;
			// $text = 'IBAN:\>'.$this->data['contract_mandate_iban'].'\\\\Mandatsreferenz:\>'.$this->data['contract_mandate_ref'].'\\\\Gläubiger-ID:\>'.$this->data['company_creditor_id'];
			$text = 'IBAN: &'.$this->data['contract_mandate_iban'].'\\\\Mandatsreferenz: &'.$this->data['contract_mandate_ref'].'\\\\Gläubiger-ID: &'.$this->data['company_creditor_id'];
		}
		else if ($net < 0 && $this->data['contract_mandate_iban'])
		{
			$template = $account->invoice_text_sepa_negativ;
			$text = 'IBAN: &'.$this->data['contract_mandate_iban'].'\\\\Mandatsreferenz: &'.$this->data['contract_mandate_ref'];
		}
		else if ($net >= 0 && !$this->data['contract_mandate_iban'])
		{
			$template = $account->invoice_text;
			$text = 'IBAN: &'.$this->data['company_account_iban'].'\\\\BIC: &'.$this->data['company_account_bic'].'\\\\Verwendungszweck: &'.$transfer_reason;
		}
		else if ($net < 0 && !$this->data['contract_mandate_iban'])
		{
			$template = $account->invoice_text_negativ;
			$text = '';
		}

		// replace placeholder of invoice text
		preg_match_all('/(?<={)[^}]*(?=})/', $template, $matches);
		foreach ($matches[0] as $value)
		{
			if (array_key_exists($value, $this->data))
				$template = str_replace('{'.$value.'}', $this->data[$value], $template);
		}

		// set invoice text
		// $this->data['invoice_text'] = $template.'\\\\'.'\begin{tabbing} \hspace{9em}\=\kill '.$text.' \end{tabbing}';
		$this->data['invoice_msg'] = $template;
		$this->data['invoice_text'] = '\begin{tabular} {@{}ll} \multicolumn{2}{@{}L{\textwidth}} {'.$template.'}\\\\'.$text.' \end{tabular}';

	}

	/**
	 * Maps appropriate Company and SepaAccount data to current Invoice
	 	* address
	 	* creditor bank account data
	 	* invoice footer data
	 	* invoice template path
	 */
	public function set_company_data($account)
	{
		if (!$account)
		{
			$this->logger->addError('Missing account data for Invoice', [$this->data['contract_id']]);
			$this->error_flag = true;
			return false;
		}

		$this->data['company_account_institute'] = $account->institute;
		$this->data['company_account_iban'] = $account->iban;
		$this->data['company_account_bic']  = $account->bic;
		$this->data['company_creditor_id']  = $account->creditorid;
		$this->data['invoice_headline'] 	= $account->invoice_headline ? $account->invoice_headline : trans('messages.invoice');

		$company = $account->company;

		if (!$company)
		{
			$this->logger->addError('No Company assigned to Account '.$account->name);
			$this->error_flag = true;
			return false;
		}

		$this->data = array_merge($this->data, $company->template_data());

		$this->data['company_registration_court'] .= $this->data['company_registration_court_1'] ? $this->data['company_registration_court_1'].'\\\\' : '';
		$this->data['company_registration_court'] .= $this->data['company_registration_court_2'] ? $this->data['company_registration_court_2'].'\\\\' : '';
		$this->data['company_registration_court'] .= $this->data['company_registration_court_3'];

		$this->data['company_logo']  = storage_path('app/'.$this->logo_path.$account->company->logo);
		$this->template_invoice_path = storage_path('app/'.$this->template_invoice_path.$account->template_invoice);
		$this->template_cdr_path 	 = storage_path('app/'.$this->template_cdr_path.$account->template_cdr);

		return true;
	}


	/**
	 * Create Invoice files and Database Entries
	 *
	 * TODO: consider template type - .tex or .odt
	 */
	public function make_invoice()
	{
		$dir = $this->_get_invoice_dir_path();

		if (!is_dir($dir))
			mkdir($dir, 0700, true);

		// if ($this->data['contract_id'] == 500026)
		// 	var_dump($this->data['invoice_nr']);

		// Keep this order -> another invoice item is build in this function - TODO: move to separate function
		if ($this->cdrs)
		{
			$this->_make_cdr_tex();
			$this->_create_db_entry(0);
		}

		if ($this->data['item_table_positions'])
		{
			$this->_make_invoice_tex();
			$this->_create_db_entry();
		}
		else
			$this->logger->addError("No Items for Invoice - only build CDR", [$this->data['contract_id']]);


		// Store as pdf
		$this->_create_pdfs();

		system('chown -R apache '.$dir);
		
	}


	/**
	 * Create Database Entry for an Invoice or a Call Data Record
	 *
	 * @param 	int 	$type 	[1] Invoice, [0] Call Data Record
	 */
	private function _create_db_entry($type = 1)
	{
		// TODO: implement time of cdr as generic, variable way
		$time = $type ? strtotime('first day of last month') : strtotime('-2 month');

		$data = array(
			'contract_id' 	=> $this->data['contract_id'],
			'year' 			=> date('Y', $time),
			'month' 		=> date('m', $time),
			'filename' 		=> $type ? $this->filename_invoice.'.pdf' :  $this->filename_cdr.'.pdf',
			'type'  		=> $type ? 'Invoice' : 'CDR',
			'number' 		=> $this->data['invoice_nr'],
			// TODO: calculate cdr costs in add_cdr_data function - write to variable and get data from it
			'charge' 		=> $type ? $this->data['table_sum_charge_net'] : 0
		);

		self::create($data);
	}


	/**
	 * Creates Tex File of Invoice - replaces all '\_' and all fields of data array that are set
	 */
	private function _make_invoice_tex()
	{
		if ($this->error_flag)
		{
			$this->logger->addError("Missing Data from SepaAccount or Company to Create Invoice", [$this->data['contract_id']]);
			return -2;			
		}

		if (!$template = file_get_contents($this->template_invoice_path))
		{
			$this->logger->addError("Failed to Create Invoice: Could not read template ".$this->template_invoice_path, [$this->data['contract_id']]);
			return -3;
		}

		// Replace placeholder by value
		$template = $this->_replace_placeholder($template);


		// Create tex file(s)
		$this->filename_invoice = date('Y_m', strtotime('first day of last month'));
		Storage::put($this->rel_storage_invoice_dir.$this->data['contract_id'].'/'.$this->filename_invoice, $template);
		// echo 'Stored tex file in '.storage_path('app/'.$this->rel_storage_invoice_dir.$this->filename_invoice)."\n";
	}


	/**
	 * Creates Tex File of Call Data Records - replaces all '\_' and all fields of data array that are set
	 *
	 * TODO: add add_cdr_data-Function and merge make_tex-Functions together
	 */
	private function _make_cdr_tex()
	{
		$month = date('m', strtotime($this->cdrs[0][1]));
		$this->data['cdr_month'] = date("$month/Y");

		// Create tex table
		$sum = $count = 0;
		foreach ($this->cdrs as $entry)
		{
			$this->data['cdr_table_positions'] .= date('d.m.Y', strtotime($entry[1])).' '.$entry[2] .' & '. $entry[3] .' & '. $entry[0] .' & '. $entry[4] . ' & '. $entry[5].'\\\\';
			$sum += $entry[5];
			$count++;
		}
		$this->data['cdr_table_positions'] .= '\\hline ~ & ~ & ~ & \textbf{Summe} & \textbf{'. $sum . '}\\\\';
		$plural = $count > 1 ? 'en' : '';
		$this->data['item_table_positions'] .= "1 & $count Telefonverbindung".$plural." & ".round($sum, 2).$this->currency.' & '.round($sum, 2).$this->currency.'\\\\';

		if (!$template = file_get_contents($this->template_cdr_path))
		{
			$this->logger->addError("Failed to Create Call Data Record: Could not read template ".$this->template_cdr_path, [$this->data['contract_id']]);
			return -3;
		}

		// Replace placeholder by value
		$template = $this->_replace_placeholder($template);

		$this->filename_cdr = date("Y_$month").'_cdr';
		Storage::put($this->rel_storage_invoice_dir.$this->data['contract_id'].'/'.$this->filename_cdr, $template);
	}


	private function _replace_placeholder($template)
	{
		// var_dump($this->data['invoice_nr']);
		$template = str_replace('\\_', '_', $template);

		foreach ($this->data as $key => $value)
		{
			// escape underscores for pdflatex to work
			if (strpos($value, 'logo') === false)
				$value = str_replace('_', '\\_', $value);
			
			$template = str_replace('{'.$key.'}', $value, $template);		
		}

		return $template;
	}


	/**
	 * Creates the pdfs out of the prepared tex files - Note: this function is very time consuming
	 */
	private function _create_pdfs()
	{
		chdir($this->_get_invoice_dir_path());

		$file_paths['Invoice']  = $this->_get_invoice_dir_path().$this->filename_invoice;
		$file_paths['CDR'] 		= $this->_get_invoice_dir_path().$this->filename_cdr;

		// if ($this->data['contract_id'] == 500027)
		// dd($file_paths);

		// TODO: execute in background to speed this up by multiprocessing - but what is with the temporary files then?
		foreach ($file_paths as $key => $file)
		{
			if (is_file($file))
			{
				system("pdflatex $file &>/dev/null", $ret);			// returns 0 on success, 127 if pdflatex is not installed  - $ret as second argument

				switch ($ret)
				{
					case 0: break;
					case 1: 
						$this->logger->addError("PdfLatex: Syntax Error in filled tex template!");
						return null;
					case 127:
						$this->logger->addError("Illegal Command - PdfLatex not installed!");
						return null;
					default:
						$this->logger->addError("Error executing PdfLatex - Return Code: $ret");
						return null;
				}

				echo "Successfully created $key in $file\n";
				$this->logger->addDebug("Successfully created $key for Contract ".$this->data['contract_nr'], [$this->data['contract_id'], $file.'.pdf']);

				// remove temporary files
				unlink($file);
				unlink($file.'.aux');
				unlink($file.'.log');
			}
		}

		// add hash for security  (files are not downloadable through script that easy)
		// rename("$filename.pdf", $filename.'_'.hash('crc32b', $this->data['contract_id'].time()).'.pdf');

	}


	/**
	 * Used to delete invoices created by previous settlement run in current month - executed in accountingCommand
	 */
	public static function delete_current_invoices()
	{
		$time = strtotime('first day of last month');

		Invoice::where('month', '=', (int) date('m', $time))->where('year', '=', (int) date('Y', $time))->forceDelete();
	}


	/**
	 * Remove all old Invoice & CDR DB-Entries & Files as it's prescribe by law
	 *
	 * TODO: implement - NOTE: This can be different from country to country
	 */
	public static function clean_up()
	{

	}


}