<?php 
namespace Modules\Ccc\Http\Controllers;

use Modules\Ccc\Entities\Ccc;
use Modules\ProvBase\Entities\Contract;
use Log;
use File;
use Modules\BillingBase\Entities\SettlementRun;
use Modules\BillingBase\Entities\Invoice;

class CccAuthuserController extends \BaseController {

	public function __construct()
	{
		// TODO: take from contract->country_id when it has usable values
		\App::setLocale('de');
	}


	/**
	 * @var Array 	Data to fill placeholder in Connection Info Template
	 */
	private $data = array(

		'contract_nr' 			=> '',
		'contract_firstname' 	=> '',
		'contract_lastname' 	=> '',
		'contract_street' 		=> '',
		'contract_zip' 			=> '',
		'contract_city' 		=> '',
		'login_name'  			=> '',
		'psw' 		  			=> '',

		// Only if Billing is enabled !
		// Company
		'company_name'			=> '',
		'company_street'		=> '',
		'company_zip'			=> '',	
		'company_city'			=> '',
		'company_phone'			=> '',
		'company_fax'			=> '',
		'company_mail'			=> '',
		'company_web'			=> '',
		'company_registration_court_1' => '',
		'company_registration_court_2' => '',
		'company_registration_court_3' => '',
		'company_management' 	=> '',
		'company_directorate' 	=> '',
		'company_web'			=> '',
		'company_tax_id_nr' 	=> '',
		'company_tax_nr' 		=> '',
		'company_logo'			=> '',

		// SepaAcc
		'company_creditor_id' 	=> '',
		'company_account_institute' => '',
		'company_account_iban'  => '',
		'company_account_bic' 	=> '',
		);



	/**
	 * Create and Download Connection Information
	 * @param integer  	$id  	contract id
	 * @return file response() - download box from browser
	 *
	 * @author Torsten Schmidt, Nino Ryschawy
	 */
	public function connection_info_download ($id)
	{
		$c = Contract::find($id);
		$customer = $c->cccauthuser;

		// Get Login Data after updating or creating CccAuthuser
		if ($customer)
			$login_data = $customer->update();
		else
		{
			$customer = new \Modules\Ccc\Entities\CccAuthuser;
			$customer->contract_id = $c->id;
			$login_data = $customer->store();
		}

		if (!$login_data)
		{
			Log::error('CustomerConnectionInfo: Error Creating Login Data', [$c->id]);
			return \Redirect::back()->with('error_msg', 'Error Creating Login Data - See Logfiles or ask Admin!');
		}

		// get data to fill placeholders in tex template
		$this->fill_template_data($login_data, $c);

		// dd($login_data);

		// create pdf
		// TODO: try - catch exceptions that this function shall throw
		$ret = $this->make_conn_info_pdf();

		if ($ret)
			return response()->download($ret);

		return \Redirect::back()->with('error_msg', 'Error Creating PDF - See Logfiles or ask Admin!');
	}


	/**
	 * Make Connection Info PDF File for Download
	 *
	 * @return String 	Absolute Path of PDF, Null on Error
	 *
	 * @author Nino Ryschawy
	 */
	private function make_conn_info_pdf()
	{
		// load template
		$template_dir = storage_path('app/config/ccc/template/');
		$template_filename = Ccc::first()->template_filename;

		if (!$template = file_get_contents($template_dir.$template_filename))
		{
			Log::error("ConnectionInfo: Could not read template", [$template_dir.$template_filename]);
			return null;
		}


		// make target tex file
		$dir_path = storage_path('app/tmp/');

		if (!is_dir($dir_path))
			mkdir($dir_path, 0733, true);

		$filename = 'conn_info';

		// Replace placeholder by value
		$template = str_replace('\\_', '_', $template);
		foreach ($this->data as $key => $value)
			$template = str_replace('{'.$key.'}', $value, $template);

		File::put($dir_path.$filename, $template);


		// create pdf from tex
		chdir($dir_path);

		system("pdflatex $filename &>/dev/null", $ret);			// returns 0 on success, 127 if pdflatex is not installed  - $ret as second argument

		// TODO: use exception handling to handle errors
		switch ($ret)
		{
			case 0: break;
			case 1: 
				Log::error("PdfLatex - Syntax error in tex template (misspelled placeholder?)", [$template_dir.$template_filename, $dir_path.$filename]);
				return null;
			case 127:
				Log::error("Illegal Command - PdfLatex not installed!");
				return null;
			default:
				Log::error("Error executing PdfLatex - Return Code: $ret");
				return null;
		}

		// remove temporary files
		unlink($filename);
		unlink($filename.'.aux');
		unlink($filename.'.log');

		return $dir_path.$filename.'.pdf';
	}


	/**
	 * Fills Data Structure so that placeholder from template can be replaced
	 */
	private function fill_template_data($login_data, $contract)
	{
		$this->data['contract_nr'] 		  = $contract->number;
		$this->data['contract_firstname'] = $contract->firstname;
		$this->data['contract_lastname']  = $contract->lastname;
		$this->data['contract_street'] 	  = $contract->street;
		$this->data['contract_zip'] 	  = $contract->zip;
		$this->data['contract_city'] 	  = $contract->city;
		$this->data['login_name'] 		  = $login_data['login_name'];
		$this->data['psw'] 				  = $login_data['password'];

		if (!\PPModule::is_active('billingbase'))
			return;

		$costcenter = $contract->costcenter;

		if (!is_object($costcenter))
		{
			Log::error('ConnectionInfoTemplate: Cannot use Billing specific data (SepaAccount/Company) to fill template - no CostCenter assigned', [$contract->id]);
			return;
		}

		$sepa_account = $costcenter->sepa_account;

		if (!is_object($sepa_account))
		{
			Log::error('ConnectionInfoTemplate: Cannot use Billing specific data (SepaAccount/Company) to fill template - CostCenter has no SepaAccount assigned', ['Costcenter' => $costcenter->name]);
			return;
		}

		$this->data['company_creditor_id']  = $sepa_account->creditorid;
		$this->data['company_account_institute'] = $sepa_account->institute;
		$this->data['company_account_iban'] = $sepa_account->iban;
		$this->data['company_account_bic']  = $sepa_account->bic;


		$company = $sepa_account->company;

		if (!is_object($company))
		{
			Log::error('ConnectionInfoTemplate: Cannot use Billing specific data (Company) to fill template - SepaAccount has no Company assigned', ['SepaAccount' => $sepa_account->name]);
			return;			
		}

		$this->data = array_merge($this->data, $company->template_data());

		$this->data['company_logo'] = storage_path('app/config/billingbase/logo/'.$this->data['company_logo']);

	}



	/**
	 * Stuff for the CCC on Customer side
	 */
	private static $rel_dir_path_invoices = 'data/billingbase/invoice/';


	/**
	 * Shows the invoice history for the Customer
	 */
	public function show()
	{
		$contract_id = \Auth::guard('ccc')->user()['contract_id'];
		$invoices 	 = self::get_customer_invoices($contract_id);

		return \View::make('ccc::index', compact('invoices', 'contract_id'));
	}

	/**
	 * Get the invoice Files for a specific contract id
	 * ATTENTION: Handle with care !!! - invoices must not be available to strangers
	 * This function is used also in Contract
	 *
	 * @return Array of FileObjects
	 *
	 * @author Nino Ryschawy
	 *
	 * TODO: uncomment already prepared eloquent way and remove the one with files after this version is deployed on old versions (hettstedt 24.10.16)
	 */
	public static function get_customer_invoices($contract_id)
	{
		// $dir 		 = storage_path('app/'.self::$rel_dir_path_invoices.$contract_id);
		// $invoices 	 = is_dir($dir) ? \File::allFiles($dir) : [];		// returns file objects

		// // hide invoices from unverified Settlementruns
		// $hide = SettlementRun::unverified_files();

		// if ($hide)
		// {
		// 	foreach ($invoices as $key => $invoice)
		// 	{
		// 		if (in_array($invoice->getBasename(), $hide))
		// 			unset($invoices[$key]);
		// 	}
		// }

		// return $invoices;

		// foreach ($srs as $sr)
		// 	$hide[] = $sr->id;

		$pdfs 	= [];
		$srs 	= SettlementRun::where('verified', '=', '0')->get(['id'])->pluck('id')->all();
		$hide 	= $srs ? : 0;
		$invoices = Invoice::where('contract_id', '=', $contract_id)->where('settlementrun_id', '!=', [$hide])->get();

		foreach ($invoices as $invoice)
			$pdfs[] = new \SplFileInfo($invoice->get_invoice_dir_path().$invoice->filename);

		return $pdfs;

	}


	/**
	 * Download an Invoice
	 */
	public function download($contract_id, $filename)
	{
		$dir = storage_path('app/'.self::$rel_dir_path_invoices.$contract_id.'/');

		return response()->download($dir.$filename);
	}


	public function psw_update()
	{
		// dd(\Input::get(), \Input::get('password'));
		if (\Input::has('password'))
		{
			// update psw
			$customer = \Auth::guard('ccc')->user();
			$rules = array('password' => 'required|confirmed|min:6');
			$data = \Input::get();

			$validator = \Validator::make($data, $rules);

			if ($validator->fails())
			{
				return \Redirect::back()->withErrors($validator)->withInput()->with('message', 'please correct the following errors')->with('message_color', 'red');
			}

			$customer->password = \Hash::make(\Input::get('password'));
			$customer->save();

			return $this->show();
		}

		return \View::make('ccc::psw_update');		
	}


}