<?php 
namespace Modules\Ccc\Http\Controllers;

use Modules\Ccc\Entities\Ccc;
use Modules\ProvBase\Entities\Contract;
use Log;
use File;
use Modules\BillingBase\Entities\SettlementRun;
use Modules\BillingBase\Entities\Invoice;
use Modules\Mail\Entities\Email;

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
		'contract_housenumber'	=> '',
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

		Log::info('Download Connection Information for CccAuthuser: '.$customer->first_name.' '.$customer->last_name.' ('.$customer->id.')');

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
		$this->data['contract_housenumber'] = $contract->house_number;
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
		$invoices = \Auth::guard('ccc')->user()->contract->invoices;
		$emails = \PPModule::is_active('mail') ? \Auth::guard('ccc')->user()->contract->emails : collect();

		return \View::make('ccc::index', compact('invoices','emails'));
	}



	/**
	 * Download an Invoice
	 *
	 * Note: This function is a bit redundant to InvoiceController@edit
	 * but here we need to make sure that no one is allowed to download invoices of strangers
	 */
	public function download($id)
	{
		$invoice = Invoice::find($id);
		$user 	 = \Auth::guard('ccc')->user();

		// check that only allowed files are downloadable - invoice must belong to customer and settlmentrun must be verified
		if (!$invoice || $invoice->contract_id != $user->contract_id || !SettlementRun::find($invoice->settlementrun_id)->verified)
			throw new \App\Exceptions\AuthExceptions('Permission Denied');

		Log::info($user->first_name.' '.$user->last_name.' downloaded invoice '.$invoice->filename.' - id: '.$invoice->id);

		return response()->download($invoice->get_invoice_dir_path().$invoice->filename);
	}


	public function psw_update()
	{
		if (\PPModule::is_active('mail') && \Input::has('email_id'))
		{
			$email = Email::findorFail(\Input::get('email_id'));
			// customer requested email object, which does not belong to him
			// (by manually changing the email_id in the url)
			if ($email->contract != \Auth::guard('ccc')->user()->contract)
				return abort(404);
		}

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

			if (isset($email))
				$email->psw_update(\Input::get('password'));
			else
			{
				$customer->password = \Hash::make(\Input::get('password'));
				$customer->save();
			}

			Log::info($customer->first_name.' '.$customer->last_name.' ['.$customer->id.']'.' changed his/her password');

			return $this->show();
		}

		return \View::make('ccc::psw_update', $this->compact_prep_view(compact('email')));
	}


}
