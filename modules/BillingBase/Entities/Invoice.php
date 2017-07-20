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
	 * Init Observer
	 */
	// public static function boot()
	// {
	// 	parent::boot();

	// 	Invoice::observe(new InvoiceObserver);
	// }


	/**
	 * @var strings  - template directory paths relativ to Storage app path and temporary filename variables
	 */
	private $rel_template_dir_path 	= 'config/billingbase/template/';
	private $template_invoice_fname = '';
	private $template_cdr_fname		= '';
	private $rel_logo_dir_path 		= 'config/billingbase/logo/';


	/**
	 * @var string - invoice directory path relativ to Storage app path and temporary filename variables
	 */
	private $rel_storage_invoice_dir = 'data/billingbase/invoice/';

	// temporary variables for settlement run without .pdf extension
	private $filename_invoice 	= '';
	private $filename_cdr 		= '';


	/**
	 * @var object - logger for Billing Module - instantiated in constructor
	 */
	private $logger;

	/**
	 * Temporary CDR Variables
	 *
	 * @var Bool 		$has_cdr 	1 - Invoice has Call Data Records, 0 - Only Invoice
	 * @var Integer 	$time_cdr 	Unix Timestamp of month of the telefone calls - set in add_cdr_data()
	 */
	public $has_cdr = 0;
	private $time_cdr;

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
		'contract_company' 		=> '',
		'contract_street' 		=> '',
		'contract_zip' 			=> '',
		'contract_city' 		=> '',
		'contract_address' 		=> '', 			// concatenated address for begin of letter

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
		'cdr_charge' 			=> '', 			// Integer with costs resulted from telephone calls
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
		$this->filename_invoice = self::_get_invoice_filename();
		
		parent::__construct($attributes);
	}


	public function get_invoice_dir_path()
	{
		return storage_path('app/'.$this->rel_storage_invoice_dir.$this->contract_id.'/');
	}


	/**
	 * @param 	String 		$type 		invoice or cdr
	 * @return 	String 					absolute Path & Filename of Template File
	 */
	private function _get_abs_template_path($type = 'invoice')
	{
		return storage_path('app/'.$this->rel_template_dir_path.$this->{'template_'.$type.'_fname'});
	}

	/**
	 * @return String 	Invoice Filename without extension (like .pdf)
	 */
	private static function _get_invoice_filename()
	{
		return date('Y_m', strtotime('first day of last month'));
	}

	/**
	 * @return String 	CDR Filename without extension (like .pdf)
	 */
	private static function _get_cdr_filename()
	{
		$offset = BillingBase::first()->cdr_offset;

		return $offset ? date('Y_m', strtotime('-'.($offset+1).' month')).'_cdr' : date('Y_m', strtotime('first day of last month')).'_cdr';
	}


	public function add_contract_data($contract, $config, $invoice_nr)
	{
		$this->data['contract_id'] 			= $contract->id;
		$this->contract_id 		 			= $contract->id;
		$this->data['contract_nr'] 			= $contract->number;
		$this->data['contract_firstname'] 	= $contract->firstname;
		$this->data['contract_lastname'] 	= $contract->lastname;
		$this->data['contract_company'] 	= $contract->company;
		$this->data['contract_street'] 		= $contract->street.' '.$contract->house_number;
		$this->data['contract_zip'] 		= $contract->zip;
		$this->data['contract_city'] 		= $contract->city;
		$this->data['contract_address'] 	= $contract->company ? "$contract->firstname $contract->lastname\\\\$contract->company\\\\".$this->data['contract_street']."\\\\$contract->zip $contract->city" : "$contract->firstname $contract->lastname\\\\".$this->data['contract_street']."\\\\$contract->zip $contract->city";

		$this->data['rcd'] 			= $config->rcd ? date($config->rcd.'.m.Y') : date('d.m.Y', strtotime('+5 days'));
		$this->data['invoice_nr'] 	= $invoice_nr ? $invoice_nr : $this->data['invoice_nr'];
		$this->data['date_invoice'] = date('d.m.Y', strtotime('last day of last month'));

		// Note: Add other currencies here
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
	 *
	 * @param 	Obj 	SepaAccount
	 * @return 	Bool 	false - error (missing required data), true - success
	 */
	public function set_company_data($account)
	{
		$err_msg = '';

		if (!$account)
			$err_msg .= 'Missing account data for Invoice ('.$this->data['contract_id'].')';

		if (!$account->template_invoice || !$account->template_cdr)
		{
			$err_msg .= $err_msg ? '; ' : '';
			$err_msg .= 'Missing SepaAccount specific templates for Invoice or CDR';
		}

		$company = $account->company;

		if (!$company || !$company->logo)
		{
			$err_msg .= $err_msg ? '; ' : '';
			$err_msg = $company ? "Missing Company's Logo ($company->name)" : 'No Company assigned to Account '.$account->name;
		}

		if ($err_msg)
		{
			$this->logger->addError($err_mgs);
			$this->error_flag = true;
			return false;
		}

		$this->data['company_account_institute'] = $account->institute;
		$this->data['company_account_iban'] = $account->iban;
		$this->data['company_account_bic']  = $account->bic;
		$this->data['company_creditor_id']  = $account->creditorid;
		$this->data['invoice_headline'] 	= $account->invoice_headline ? $account->invoice_headline : trans('messages.invoice');

		$this->data = array_merge($this->data, $company->template_data());

		$this->data['company_registration_court'] .= $this->data['company_registration_court_1'] ? $this->data['company_registration_court_1'].'\\\\' : '';
		$this->data['company_registration_court'] .= $this->data['company_registration_court_2'] ? $this->data['company_registration_court_2'].'\\\\' : '';
		$this->data['company_registration_court'] .= $this->data['company_registration_court_3'];

		$this->data['company_logo']   = storage_path('app/'.$this->rel_logo_dir_path.$account->company->logo);
		$this->template_invoice_fname = $account->template_invoice;
		$this->template_cdr_fname 	  = $account->template_cdr;

		return true;
	}


	/**
	 * @param 	Array 	$cdrs 		Call Data Record array designated for this Invoice formatted by parse_cdr_data in accountingCommand
	 */
	public function add_cdr_data($cdrs)
	{
		$this->has_cdr = 1;
		$this->time_cdr = $time_cdr = strtotime($cdrs[0][1]);
		$this->data['cdr_month'] = date('m/Y', $time_cdr);

		$sum = $count = 0;
		foreach ($cdrs as $entry)
		{
			$this->data['cdr_table_positions'] .= date('d.m.Y', strtotime($entry[1])).' '.$entry[2] .' & '. $entry[3] .' & '. $entry[0] .' & '. $entry[4] . ' & '.sprintf("%01.4f", $entry[5]).'\\\\';
			$sum += $entry[5];
			$count++;
		}

		$sum = sprintf("%01.2f", $sum); 	// round($sum, 2)

		$this->data['cdr_charge'] = $sum;
		$this->data['cdr_table_positions'] .= '\\hline ~ & ~ & ~ & \textbf{Summe} & \textbf{'. $sum . '}\\\\';
		$plural = $count > 1 ? 'en' : '';
		$this->data['item_table_positions'] .= "1 & $count Telefonverbindung".$plural." & ".$sum.$this->currency.' & '.$sum.$this->currency.'\\\\';

		$this->filename_cdr = date('Y_m', $time_cdr).'_cdr';

	}



	/**
	 * Create Invoice files and Database Entries
	 *
	 * TODO: consider template type - .tex or .odt
	 */
	public function make_invoice()
	{
		$dir = $this->get_invoice_dir_path();

		if (!is_dir($dir))
			mkdir($dir, 0700, true);

		if ($this->has_cdr)
		{
			$this->_make_tex('cdr');
			$this->_create_db_entry(0);
		}

		if ($this->data['item_table_positions'])
		{
			$this->_make_tex('invoice');
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
		$time = $type ? strtotime('first day of last month') : $this->time_cdr; // strtotime('-2 month');

		$data = array(
			'contract_id' 	=> $this->data['contract_id'],
			'settlementrun_id' 	=> $this->settlementrun_id,
			'year' 			=> date('Y', $time),
			'month' 		=> date('m', $time),
			'filename' 		=> $type ? $this->filename_invoice.'.pdf' :  $this->filename_cdr.'.pdf',
			'type'  		=> $type ? 'Invoice' : 'CDR',
			'number' 		=> $this->data['invoice_nr'],
			'charge' 		=> $type ? $this->data['table_sum_charge_net'] : $this->data['cdr_charge']
		);

		self::create($data);
	}


	/**
	 * Creates Tex File of Invoice or CDR
	 * replaces all '\_' and all fields of data array that are set by it's value
	 *
	 * @param String	$type 	'invoice'/'cdr'
	 */
	private function _make_tex($type = 'invoice')
	{
		if ($this->error_flag)
		{
			$this->logger->addError("Missing Data from SepaAccount or Company to Create $type", [$this->data['contract_id']]);
			return -2;			
		}

		if (!$template = file_get_contents($this->_get_abs_template_path($type)))
		{
			$this->logger->addError("Failed to Create Invoice: Could not read template ".$this->_get_abs_template_path($type), [$this->data['contract_id']]);
			return -3;
		}

		// Replace placeholder by value
		$template = $this->_replace_placeholder($template);


		// Create tex file(s)
		Storage::put($this->rel_storage_invoice_dir.$this->data['contract_id'].'/'.$this->{"filename_$type"}, $template);
		// echo 'Stored tex file in '.storage_path('app/'.$this->rel_storage_invoice_dir.$this->filename_invoice)."\n";
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
		chdir($this->get_invoice_dir_path());

		$file_paths['Invoice']  = $this->get_invoice_dir_path().$this->filename_invoice;
		$file_paths['CDR'] 		= $this->get_invoice_dir_path().$this->filename_cdr;

		// if ($this->data['contract_id'] == 500027)
		// dd($file_paths);

		foreach ($file_paths as $key => $file)
		{
			if (is_file($file))
			{
				// take care - when we start process in background we don't get the return value anymore
				system("pdflatex \"$file\" &>/dev/null &", $ret);			// returns 0 on success, 127 if pdflatex is not installed  - $ret as second argument

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

				// echo "Successfully created $key in $file\n";
				$this->logger->addDebug("Successfully created $key for Contract ".$this->data['contract_nr'], [$this->data['contract_id'], $file.'.pdf']);

				// Deprecated: remove temporary files - This is done by remove_templatex_files() now after all pdfs were created simultaniously by multiple threads
				// unlink($file);
				// unlink($file.'.aux');
				// unlink($file.'.log');
			}
		}

	}

	/**
	 * Removes the temporary latex files after all pdfs were created simultaniously by multiple threads
	 */
	public static function remove_templatex_files()
	{
		$invoices = Invoice::whereBetween('created_at', [date('Y-m-01 00:00:00'), date('Y-m-01 00:00:00', strtotime('next month'))])->get();

		foreach ($invoices as $invoice)
		{
			$file = $invoice->get_invoice_dir_path().str_replace('.pdf', '', $invoice->filename);
			if (is_file($file))
			{
				unlink($file);
				unlink($file.'.aux');
				unlink($file.'.log');
			}
		}
	}


	/**
	 * Deletes currently created invoices (created in actual month)
	 * Used to delete invoices created by previous settlement run in current month - executed in accountingCommand
	 * is used to remove files before settlement run is repeatedly created (accountingCommand executed again)
	 * NOTE: Use Carefully!!
	 */
	public static function delete_current_invoices()
	{
		$invoice_fname  = self::_get_invoice_filename().'.pdf';
		$cdr_fname 		= self::_get_cdr_filename().'.pdf';

		$query = Invoice::where('filename', '=', $invoice_fname)->orWhere('filename', '=', $cdr_fname)->whereBetween('created_at', [date('Y-m-01 00:00:00'), date('Y-m-01 00:00:00', strtotime('next month'))]);
		
		$invoices = $query->get();

		// Delete PDFs
		foreach ($invoices as $invoice)
		{
			$filepath = $invoice->get_invoice_dir_path().$invoice->filename;
			if (is_file($filepath))
				unlink($filepath);
		}

		// Delete DB Entries - Note: keep this order
		$query->forceDelete();
	}


	/**
	 * Remove all old Invoice & CDR DB-Entries & Files as it's prescribed by law
	 	* Germany: CDRs 6 Months (§97 TKG) - Invoices ?? - 
	 *
	 * NOTE: This can be different from country to country
	 * TODO: Remove old Invoices
	 */
	public static function cleanup()
	{
		if (\Config::get('database.default') == 'mysql')
			$query = Invoice::where('type', '=', 'CDR')->whereRaw("CONCAT_WS('', year, '-', LPAD(month, 2 ,0), '-', '01') < '".date('Y-m-01', strtotime('-6 month'))."'");
		else
		{
			\Log::error('Missing Query in Modules\BillingBase\Entities\Invoice@cleanup for Database '.\Config::get('database.default'));
			return;
		}

		$cdrs = $query->get();

		\Log::info('Delete all CDRs older than 6 Months');

		foreach ($cdrs as $cdr)
		{
			$filepath = $cdr->get_invoice_dir_path().$cdr->filename;
			if (is_file($filepath))
				unlink($filepath);
		}

		$query->delete();
	}


}


class InvoiceObserver
{
	public function deleted($invoice)
	{
		// Delete PDF from Storage
		// Storage::delete($invoice->rel_storage_invoice_dir.$invoice->contract_id.'/'.$invoice->filename);
	}
}