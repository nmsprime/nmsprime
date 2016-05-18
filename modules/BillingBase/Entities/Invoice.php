<?php

namespace Modules\BillingBase\Entities;

use Storage;
use Modules\BillingBase\Entities\BillingLogger;


class Invoice {

	private $dir = 'data/invoice/';				// relativ to storage path - changed in constructor
	private $currency;
	private $tax;
	private $template = '/tftpboot/bill/template/';
	private $logo_dir = '/tftpboot/bill/logo/';
	private $file;

	private $logger;							// logger instance for Billing Module

	public $data = array(

		'company_name'			=> '',
		'company_street'		=> '',
		'company_zip'			=> '',
		'company_city'			=> '',
		'company_phone'			=> '',
		'company_fax'			=> '',
		'company_mail'			=> '',
		'company_web'			=> '',
		'company_registration_court' => '',
		'company_management' 	=> '',
		'company_directorate' 	=> '',
		'company_web'			=> '',

		'company_creditor_id' 	=> '',
		'company_account_institute' => '',
		'company_account_iban'  => '',
		'company_account_bic' 	=> '',
		'company_tax_id_nr' 	=> '',
		'company_tax_nr' 		=> '',

		'company_logo'			=> '',

		'contract_id' 			=> '',
		'contract_nr' 			=> '',
		'contract_firstname' 	=> '',
		'contract_lastname' 	=> '',
		'contract_street' 		=> '',
		'contract_zip' 			=> '',
		'contract_city' 		=> '',

		'contract_mandate_iban'	=> '', 			// iban of the customer
		'contract_mandate_ref'	=> '', 			// mandate reference of the customer

		// 'date'				=> '',
		'invoice_nr' 			=> '',
		'invoice_text'			=> '',			// appropriate invoice text from company dependent of total charge & sepa mandate
		'invoice_headline'		=> '',
		'rcd' 					=> '',			// Fälligkeitsdatum
		// 'tariffs'			=> '',			// (TODO: implement!)
		// 'start'				=> '',			// Leistungszeitraum start , TODO: implement!
		// 'end'				=> '',			// Leistungszeitraum ende , TODO: implement!

		'item_table_positions'  => '', 			// list of all items to be charged in this invoice
		'table_summary' 		=> '', 			// preformatted table - use following three keys to set table by yourself
		'table_sum_charge_net'  => '', 			// net charge - without tax
		'table_sum_tax_percent' => '', 			// The tax percentage with % character
		'table_sum_tax' 		=> '', 			// The tax
		'table_sum_charge_total' => '', 		// total charge - with tax

	);


	public function __construct($contract, $config, $invoice_nr)
	{
		$this->data['contract_id'] 			= $contract->id;
		$this->data['contract_nr'] 			= $contract->number;
		$this->data['contract_firstname'] 	= $contract->firstname;
		$this->data['contract_lastname'] 	= $contract->lastname;
		$this->data['contract_street'] 		= $contract->street;
		$this->data['contract_zip'] 		= $contract->zip;
		$this->data['contract_city'] 		= $contract->city;

		$this->data['rcd'] 			= $config->rcd ? $config->rcd : date('d.m.Y', strtotime('+6 days'));
		$this->data['invoice_nr'] 	= $invoice_nr;

		// TODO: Add other currencies here
		$this->currency	= strtolower($config->currency) == 'eur' ? '€' : $config->currency;
		$this->tax		= $config->tax;
		$this->dir 		.= $contract->number;

		$this->logger = new BillingLogger;
	}

	public function add_item($item) 
	{
		$count = $item->count ? $item->count : 1;
		$this->data['item_table_positions'] .= $count.' & '.$item->invoice_description.' & '.round($item->charge/$count, 2).$this->currency.' & '.($item->charge).$this->currency.'\\\\';
	}

	public function set_mandate($mandate)
	{
		if (!$mandate)
			return;

		$this->data['contract_mandate_iban'] = $mandate->sepa_iban;
		$this->data['contract_mandate_ref']  = $mandate->reference;
	}


	// Set total sum and invoice text for this invoice - TODO: Translate!!
	public function set_summary($net, $tax, $account)
	{
		$tax_percent = $tax ? $this->tax : 0;
		$tax_percent .= '\%';

		$this->data['table_summary'] = '~ & Gesamtsumme: & ~ & '.$net.$this->currency.'\\\\';
		$this->data['table_summary'] .= "~ & $tax_percent MwSt: & ~ & ".$tax.$this->currency.'\\\\';
		$this->data['table_summary'] .= '~ & Rechnungsbetrag: & ~ & '.($net + $tax).$this->currency.'\\\\';

		$this->data['table_sum_charge_net']  	= $net; 
		$this->data['table_sum_tax_percent'] 	= $tax_percent;
		$this->data['table_sum_tax'] 			= $tax;
		$this->data['table_sum_charge_total'] 	= $net + $tax; 


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
		$this->data['invoice_text'] = '\begin{tabular} {ll} \multicolumn{2}{L{\textwidth}} {'.$template.'}\\\\'.$text.' \end{tabular}';


	}

	public function set_company_data($account)
	{
		$this->data['company_account_institute'] = $account->institute;
		$this->data['company_account_iban'] = $account->iban;
		$this->data['company_account_bic']  = $account->bic;
		$this->data['company_creditor_id']  = $account->creditorid;
		$this->data['invoice_headline'] 	= $account->invoice_headline ? $account->invoice_headline : trans('messages.invoice');

		if (!$account->company)
		{
			$this->logger->addError('No Company assigned to Account '.$account->name);
			return false;
		}

		$this->data['company_name']		= $account->company->name;
		$this->data['company_street']	= $account->company->street;
		$this->data['company_zip']		= $account->company->zip;
		$this->data['company_city']		= $account->company->city;
		$this->data['company_phone']	= $account->company->phone;
		$this->data['company_fax']		= $account->company->fax;
		$this->data['company_mail']		= $account->company->mail;
		$this->data['company_web']		= $account->company->web;

		$this->data['company_registration_court'] .= $account->company->registration_court_1 ? $account->company->registration_court_1.'\\\\' : '';
		$this->data['company_registration_court'] .= $account->company->registration_court_2 ? $account->company->registration_court_2.'\\\\' : '';
		$this->data['company_registration_court'] .= $account->company->registration_court_3 ? $account->company->registration_court_3.'\\\\' : '';

		if ($account->company->management)
		{
			$management = explode(',', $account->company->management);
			foreach ($management as $key => $value) 
				$management[$key] = trim($value);
			$this->data['company_management'] = implode('\\\\', $management);
		}

		if ($account->company->directorate)
		{
			$directorate = explode(',', $account->company->directorate);
			foreach ($directorate as $key => $value) 
				$directorate[$key] = trim($value);
			$this->data['company_directorate'] = implode('\\\\', $directorate);
		}

		$this->data['company_tax_id_nr'] 	= $account->company->tax_id_nr;
		$this->data['company_tax_nr'] 		= $account->company->tax_nr;

		$this->data['company_logo'] = $this->logo_dir.$account->company->logo;
		$this->template .= $account->template;

		return true;
	}

	/*
	 * Read .tex or .odt file replace every \_ and all fields of data array that are set
	 */
	public function make_invoice()
	{
		/*
		 * TODO: consider template type - .tex or .odt
		 */
		// if ($this->data['invoice_nr'] == '2/100002')
		// 	dd($this->data);

		if (!is_file($this->template) || !is_file($this->data['company_logo']))
		{
			$this->logger->addError("Failed to Create Invoice: Template or Logo of Company ".$this->data['company_name']." not set!", [$this->data['contract_id']]);
			return -1;
		}

		if (!$template = file_get_contents($this->template))
		{
			$this->logger->addError("Failed to Create Invoice: Could not read template ".$this->template, [$this->data['contract_id']]);
			return -2;
		}

		// replace placeholder by value
		$template = str_replace('\\_', '_', $template);

		foreach ($this->data as $key => $value)
			$template = str_replace('{'.$key.'}', $value, $template);

		// create tex file
		$this->file = $this->dir.'/'.date('m').'_'.str_replace(['/', ' '], '_', $this->data['invoice_nr']);
		echo 'Stored tex file in '.$this->file."\n";
		Storage::put($this->file, $template);

		$this->create_pdf();

		return 0;
	}


	/**
	 * Creates the pdf out of the prepared tex file - this function is very time consuming
	 */
	public function create_pdf()
	{
		chdir(storage_path().'/app/'.$this->dir);


		$file = storage_path().'/app/'.$this->file;

		// TODO: execute in background to speed this up by multiprocessing - but what is with the temporary files then?
		system("pdflatex $file &>/dev/null");			// returns 0 on success - $ret as second argument

		$this->logger->addDebug('Successfully created Invoice for Contract '.$this->data['contract_nr'], [$this->data['contract_id'], $file]);

		// add hash for security  (files are not downloadable through script that easy)
		// rename("$file.pdf", $file.'_'.hash('crc32b', $this->data['contract_id'].time()).'.pdf');

		// remove temporary files
		unlink($file);
		unlink($file.'.aux');
		unlink($file.'.log');
	}

}
