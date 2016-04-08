<?php

namespace Modules\BillingBase\Entities;
use File;

class Bill {

	private $dir = '/var/www/data/invoice/';
	private $currency;
	private $tax;
	private $template = '/tftpboot/bill/';

	public $data = array(

		'company_name'		=> '',
		'company_street'	=> '',
		'company_zip'		=> '',
		'company_city'		=> '',
		'company_phone'		=> '',
		'company_fax'		=> '',
		'company_mail'		=> '',
		'company_web'		=> '',
		'company_registration_court' => '',
		'company_management' => '',
		'company_directorate' => '',
		'company_web'		=> '',

		'company_creditor_id' => '',
		'company_account_institute' => '',
		'company_account_iban' => '',
		'company_account_bic' => '',
		'company_tax_id_nr' => '',
		'company_tax_nr' 	=> '',

		'company_logo'		=> '',

		'contract_id' 		=> '',
		'contract_firstname' => '',
		'contract_lastname' => '',
		'contract_street' 	=> '',
		'contract_zip' 		=> '',
		'contract_city' 	=> '',

		'contract_mandate_iban'	=> '',
		'contract_mandate_ref'	=> '',

		'date'				=> '',
		'invoice_nr' 		=> '',
		'rcd' 				=> '',			// Fälligkeitsdatum
		'tariffs'			=> '',			// TODO: implement!
		'start'				=> '',			// Leistungszeitraum start , TODO: implement!
		'end'				=> '',			// Leistungszeitraum ende , TODO: implement!

		'item_table_positions' => '',
		'table_summary' 	=> '',

	);


	public function __construct($contract, $config, $invoice_nr)
	{
		$this->data['contract_id'] 			= $contract->id;
		$this->data['contract_firstname'] 	= $contract->firstname;
		$this->data['contract_lastname'] 	= $contract->lastname;
		$this->data['contract_street'] 		= $contract->street;
		$this->data['contract_zip'] 		= $contract->zip;
		$this->data['contract_city'] 		= $contract->city;

		$this->data['rcd'] 			= $config->rcd ? $config->rcd : date('d.m.Y', strtotime('+6 days'));
		$this->data['invoice_nr'] 	= $invoice_nr;

		$this->currency	= strtolower($config->currency) == 'eur' ? '€' : $config->currency;
		$this->tax		= $config->tax;
		$this->dir 		.= $contract->id;
	}

	public function add_item($count, $price, $text)
	{
		$this->data['item_table_positions'] .= $count.' & '.$text.' & '.round($price/$count, 2).$this->currency.' & '.$price.$this->currency.'\\\\';
	}

	public function set_mandate($mandate)
	{
		if (!$mandate)
			return;

		$this->data['contract_mandate_iban'] = $mandate->sepa_iban;
		$this->data['contract_mandate_ref']  = $mandate->reference;
	}

	public function set_summary($gross, $tax)
	{
		$tax_percent = $tax ? $this->tax : 0;
		$tax_percent .= '\%';

		// TODO: Translate!
		$this->data['table_summary'] = '~ & Gesamtsumme: & ~ & '.($gross-$tax).$this->currency.'\\\\';
		$this->data['table_summary'] .= "~ & $tax_percent MwSt: & ~ & ".$tax.$this->currency.'\\\\';
		$this->data['table_summary'] .= '~ & Rechnungsbetrag: & ~ & '.$gross.$this->currency.'\\\\';
		// dd($gross, $tax, $this->data['table_summary']);
	}

	public function set_company_data($account)
	{
		// if ($this->data['contract_id'] == 500007)
		// 	dd($account);
		$this->data['company_account_institute'] = $account->institute;
		$this->data['company_account_iban'] = $account->iban;
		$this->data['company_account_bic']  = $account->bic;
		$this->data['company_creditor_id']  = $account->creditorid;

		if (!$account->company)
			return false;

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

		// $management = str_replace(',', '\\\\', $account->company->management);
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

		$this->data['company_logo'] = $this->template.$account->company->logo;
		$this->template .= $account->company->template;

		return true;
	}

	/*
	 * Read .tex or .odt file replace every \_ and all fields of data array that are set
	 */
	public function make_bill()
	{
		/* TODO: consider dependencies of (not) existent mandate
			* wird abgebucht | muss bis in 2 Wochen bezahlt werden
			* 2 tex files zur Verfügung stellen? -> mit selbem Name + 'sepa' angehängt ?
		 * TODO: consider template type - .tex or .odt
		 * Errors to solve:
		 	* logo/template not set
		 */

		if (!is_file($this->template) || !is_file($this->data['company_logo']))
			return -1;

		// dd($this->template, $this->data['company_logo']);

		if (!$template = file_get_contents($this->template))
			return -2;

		$template = str_replace('\\_', '_', $template);

		foreach ($this->data as $key => $value)
			$template = str_replace('{'.$key.'}', $value, $template);
			// $template = str_replace($key, $value, $template);

		if (!is_dir($this->dir))
			mkdir($this->dir, '0744', true);

		$file = $this->dir.'/'.date('Y_m');
		File::put($file, $template);

		// create pdf
		chdir($this->dir);
		system("pdflatex $file &>/dev/null");

		// add hash for security - files are not easily downloadable by name through script
		rename("$file.pdf", $file.'_'.hash('crc32b', $this->data['contract_id']).'.pdf');

		// remove temporary files
		unlink($file);
		unlink($file.'.aux');
		unlink($file.'.log');

		return 0;
	}

}
