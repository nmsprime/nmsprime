<?php

namespace Modules\BillingBase\Entities;

use Storage;
use ChannelLog;


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

	public static function view_icon()
	{
		return '<i class="fa fa-id-card-o"></i>';
	}

	public function view_index_label()
	{
		$type = $this->type == 'CDR' ? ' ('.trans('messages.Call Data Record').')' : '';

		return $this->year.' - '.str_pad($this->month, 2, 0, STR_PAD_LEFT). $type;
	}

	/**
	 * Relations
	 */
	public function contract()
	{
		return $this->belongsTo('Modules\ProvBase\Entities\Contract');
	}

	public function settlementrun()
	{
		return $this->belongsTo('Modules\BillingBase\Entities\SettlementRun');
	}

	/**
	 * Init Observer
	 */
	public static function boot()
	{
		parent::boot();

		Invoice::observe(new InvoiceObserver);
	}


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
	public $rel_storage_invoice_dir = 'data/billingbase/invoice/';

	// temporary variables for settlement run without .pdf extension
	private $filename_invoice 	= '';
	private $filename_cdr 		= '';

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

		// Company - NOTE: Set by Company->template_data()
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

		// Cancelation Dates - as prescribed by law from 2018-01-01
		'start_of_term' 	=> '', 				// contract start
		'maturity' 			=> '', 				// Tariflaufzeit
		'end_of_term' 		=> '', 				// Aktuelles Vertragsende
		'period_of_notice' 	=> '', 				// Kündigungsfrist
		'last_cancel_date' 	=> '', 				// letzter Kündigungszeitpunkt der aktuellen Laufzeit, if empty -> contract was already canceled!
	);


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
	 * @return String 	Date part of the invoice filename
	 *
	 * NOTE: This has to be adapted if we want support creating invoices for multiple months in the past
	 */
	private static function _get_invoice_filename_date_part()
	{
		return date('Y_m', strtotime('first day of last month'));
	}


	/**
	 * @param String
	 */
	public static function german_dateformat($date)
	{
		return $date ? date('d.m.Y', strtotime($date)) : $date;
	}


	public function add_contract_data($contract, $config, $invoice_nr)
	{
		$this->data['contract_id'] 			= $contract->id;
		$this->contract_id 		 			= $contract->id;
		$this->data['contract_nr'] 			= $contract->number;
		$this->data['contract_firstname'] 	= escape_latex_special_chars($contract->firstname);
		$this->data['contract_lastname'] 	= escape_latex_special_chars($contract->lastname);
		$this->data['contract_company'] 	= escape_latex_special_chars($contract->company);
		$this->data['contract_street'] 		= escape_latex_special_chars($contract->street.' '.$contract->house_number);
		$this->data['contract_zip'] 		= $contract->zip;
		$this->data['contract_city'] 		= escape_latex_special_chars($contract->city);
		$this->data['contract_address'] 	= ($contract->company ? $this->data['contract_company']."\\\\" : '') . ($contract->academic_degree ? escape_latex_special_chars($contract->academic_degree)." " : '') . $this->data['contract_firstname'].' '.$this->data['contract_lastname']."\\\\" . $this->data['contract_street'] . "\\\\$contract->zip ".$this->data['contract_city'];
		$this->data['start_of_term'] 		= \App::getLocale() == 'de' ? self::german_dateformat($contract->contract_start) : $contract->contract_start;

		$this->data['rcd'] 			= $config->rcd ? date($config->rcd.'.m.Y') : date('d.m.Y', strtotime('+5 days'));
		$this->data['invoice_nr'] 	= $invoice_nr ? $invoice_nr : $this->data['invoice_nr'];
		$this->data['date_invoice'] = date('d.m.Y', strtotime('last day of last month'));
		$this->filename_invoice 	= $this->filename_invoice ? : self::_get_invoice_filename_date_part().'_'.str_replace('/', '_', $invoice_nr);

		// Note: Add other currencies here
		$this->currency	= strtolower($config->currency) == 'eur' ? '€' : $config->currency;
		$this->tax		= $config->tax;

		/* Set:
			* actual end of term
			* period of notice
			* latest possible date of cancelation
		*/
		$txt_pon = $txt_m = '';

		// Contract already canceled
		if ($contract->get_end_time())
		{
			$ret = array(
				'end_of_term' => $contract->contract_end,
				'cancelation_day' => '',
				'tariff' => null,
				);
		}
		// Get next cancelation date
		else
			$ret = $contract->get_next_cancel_date();

		// e.g. customers that get tv amplifier refund, but dont have any tariff
		if (!isset($ret['tariff'])) {
			ChannelLog::debug('billing', "Customer has no tariff - dont set cancelation dates.", [$this->data['contract_id']]);
			return;
		}

		if ($ret['tariff'])
		{
			// Set period of notice and maturity string of last tariff
			$nr   = preg_replace( '/[^0-9]/', '', $ret['tariff']->product->period_of_notice ? : Product::$pon);
			$span = str_replace($nr, '', $ret['tariff']->product->period_of_notice ? : Product::$pon);
			$txt_pon = $nr .' '. trans_choice("messages.$span", $nr) .($ret['tariff']->product->maturity ? '' : ' '.trans('messages.eom'));

			$nr   = preg_replace( '/[^0-9]/', '', $ret['tariff']->product->maturity ? : Product::$maturity);
			$span = str_replace($nr, '', $ret['tariff']->product->maturity ? : Product::$maturity);
			$txt_m = $nr .' '. trans_choice("messages.$span", $nr);
		}

		$german = \App::getLocale() == 'de';

		$cancel_dates = [
			'end_of_term' => $german ? self::german_dateformat($ret['end_of_term']) : $ret['end_of_term'],
			'maturity' 		=> $txt_m,
			'period_of_notice' => $txt_pon,
			'last_cancel_date' => $german ? self::german_dateformat($ret['cancelation_day']) : $ret['cancelation_day'],
		];

		$this->data = array_merge($this->data, $cancel_dates);
	}



	public function add_item($item)
	{
		$count = $item->count ? $item->count : 1;
		$price = sprintf("%01.2f", round($item->charge/$item->count, 2));
		$sum   = sprintf("%01.2f", $item->charge);
		$this->data['item_table_positions'] .= $item->count.' & '.escape_latex_special_chars($item->invoice_description).' & '.$price.$this->currency.' & '.$sum.$this->currency.'\\\\';
	}


	public function set_mandate($mandate)
	{
		if (!$mandate)
			return;

		$this->data['contract_mandate_iban'] = $mandate->sepa_iban;
		$this->data['contract_mandate_ref']  = $mandate->reference;
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
		$this->data = array_merge($this->data, \Modules\BillingBase\Providers\CompanyData::get($account->id));

		$this->template_invoice_fname = $account->template_invoice;
		$this->template_cdr_fname 	  = $account->template_cdr;
	}


	/**
	 * Set total sum and invoice text for this invoice - TODO: Translate!!
	 */
	public function set_summary($net, $tax, $account)
	{
		$tax_percent = $tax ? $this->tax : 0;
		$tax_percent .= '\%';

		$net   = number_format($net, 2);
		$tax   = number_format($tax, 2);
		$total = number_format($net + $tax, 2);

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
		$this->data['invoice_msg'] = escape_latex_special_chars($template);
		$this->data['invoice_text'] = '\begin{tabular} {@{}ll} \multicolumn{2}{@{}L{\textwidth}} {'.$template.'}\\\\'.$text.' \end{tabular}';

	}


	/**
	 * @param 	cdrs 	Array		Call Data Record array designated for this Invoice formatted by parse_cdr_data in accountingCommand
	 * @param   conf   	model 		BillingBase
	 */
	public function add_cdr_data($cdrs, $conf)
	{
		$this->has_cdr = 1;
		// $this->time_cdr = $time_cdr = strtotime($cdrs[0][1]);
		$this->time_cdr = $time_cdr = $conf->cdr_offset ? strtotime('-'.($conf->cdr_offset+1).' month') : strtotime('first day of last month');
		$this->data['cdr_month'] = date('m/Y', $time_cdr);

		// TODO: customer can request to show his tel nrs cut by the 3 last nrs (TKG §99 (1))
		// TODO: dont show target nrs that have to stay anonym (church, mental consultation, ...) (TKG §99 (2))

		$sum = $count = 0;
		foreach ($cdrs as $entry)
		{
			$this->data['cdr_table_positions'] .= date('d.m.Y', strtotime($entry[1])).' '.$entry[2] .' & '. $entry[3] .' & '. $entry[0] .' & '. $entry[4] . ' & '.sprintf("%01.4f", $entry[5]).'\\\\';
			$sum += $entry[5];
			$count++;
		}

		$sum = sprintf("%01.2f", $sum);

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
			ChannelLog::warning('billing', "No Items for Invoice - only build CDR", [$this->data['contract_id']]);


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
		if ($this->error_flag) {
			ChannelLog::error('billing', "Missing Data from SepaAccount or Company to Create $type", [$this->data['contract_id']]);
			return -2;
		}

		if (!$template = file_get_contents($this->_get_abs_template_path($type))) {
			ChannelLog::error('billing', "Failed to Create Invoice: Could not read template ".$this->_get_abs_template_path($type), [$this->data['contract_id']]);
			return -3;
		}

		// Replace placeholder by value
		$template = $this->_replace_placeholder($template);

		// ChannelLog::debug('billing', 'Store '. $this->rel_storage_invoice_dir.$this->data['contract_id'].'/'.$this->{"filename_$type"});

		// Create tex file(s)
		Storage::put($this->rel_storage_invoice_dir.$this->data['contract_id'].'/'.$this->{"filename_$type"}, $template);
	}


	private function _replace_placeholder($template)
	{
		$template = str_replace('\\_', '_', $template);

		foreach ($this->data as $key => $string)
			$template = str_replace('{'.$key.'}', $string, $template);

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
						ChannelLog::error('billing', "PdfLatex: Syntax Error in filled tex template!");
						return null;
					case 127:
						ChannelLog::error('billing', "Illegal Command - PdfLatex not installed!");
						return null;
					default:
						ChannelLog::error('billing', "Error executing PdfLatex - Return Code: $ret");
						return null;
				}

				// echo "Successfully created $key in $file\n";
				ChannelLog::debug('billing', "Created $key for Contract ".$this->data['contract_nr'], [$this->data['contract_id'], $file.'.pdf']);
			}
		}

	}

	/**
	 * Removes the temporary latex files after all pdfs were created simultaniously by multiple threads
	 * Test if all Invoices were created successfully
	 *
	 * @throws Exception 	when pdflatex was not able to create PDF from tex document for an invoice
	 */
	public static function remove_templatex_files()
	{
		$invoices = Invoice::whereBetween('created_at', [date('Y-m-01 00:00:00'), date('Y-m-01 00:00:00', strtotime('next month'))])->get();

		foreach ($invoices as $invoice)
		{
			$fn = $invoice->get_invoice_dir_path().$invoice->filename;

			if (is_file($fn))
			{
				$fn = str_replace('.pdf', '', $fn);
				unlink($fn);
				unlink($fn.'.aux');
				unlink($fn.'.log');
			}
			else {
				// possible errors: syntax/filename/...
				ChannelLog::error('billing', "pdflatex: Error creating Invoice PDF ".$fn);
				throw new \Exception("pdflatex: Error creating Invoice PDF ".$fn);
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
		$query 	  = Invoice::whereBetween('created_at', [date('Y-m-01 00:00:00'), date('Y-m-01 00:00:00', strtotime('next month'))]);
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
		$conf = BillingBase::first();

		$period = $conf->cdr_retention_period; 				// retention period - total months CDRs should be kept
		$offset = $conf->cdr_offset;
		$target_time_o = \Carbon\Carbon::create()->subMonthsNoOverflow($offset + $period);

		\Log::info("Delete all CDRs older than $period Months");

		$query = Invoice::where('type', '=', 'CDR')
				->where('year', '<=', $target_time_o->__get('year'))
				->where('month', '<', $target_time_o->__get('month'));

		$cdrs = $query->get();

		foreach ($cdrs as $cdr)
		{
			$filepath = $cdr->get_invoice_dir_path().$cdr->filename;
			if (is_file($filepath))
				unlink($filepath);
		}

		$query->delete();

		// Delete all CDR CSVs older than $period months
		\App::setLocale($conf->userlang);

		$path = storage_path("app/data/billingbase/accounting/").$target_time_o->format('Y-m').'/';
		$target_time_o->subMonthNoOverflow($offset);
		$fn = \App\Http\Controllers\BaseViewController::translate_label('Call Data Record')."_".$target_time_o->format('Y_m').'.csv';

		if (is_file($path.$fn))
			unlink($path.$fn);
	}


}


class InvoiceObserver
{
	public function deleted($invoice)
	{
		// Delete PDF from Storage
		$ret = Storage::delete($invoice->rel_storage_invoice_dir.$invoice->contract_id.'/'.$invoice->filename);
		ChannelLog::info('billing', 'Removed Invoice from Storage', [$invoice->contract_id, $invoice->filename]);
	}
}
