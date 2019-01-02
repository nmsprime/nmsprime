<?php

namespace Modules\Ccc\Http\Controllers;

use Log;
use Auth;
use File;
use Modules\Ccc\Entities\Ccc;
use Modules\NmsMail\Entities\Email;
use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\Invoice;
use Modules\BillingBase\Entities\SettlementRun;

class CccUserController extends \BaseController
{
    public function __construct()
    {
        // TODO: take from contract->country_id when it has usable values
        \App::setLocale('de');
    }

    /**
     * @var array 	Data to fill placeholder in Connection Info Template
     */
    private $data = [

        'contract_address' 		=> '', 			// company, degree, name, street + nr, city
        'contract_nr' 			=> '',
        'contract_firstname' 	=> '',
        'contract_lastname' 	=> '',
        'contract_street' 		=> '',
        'contract_housenumber'	=> '',
        'contract_zip' 			=> '',
        'contract_district' 	=> '',
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
        'company_tax_id_nr' 	=> '',
        'company_tax_nr' 		=> '',
        'company_logo'			=> '',

        // SepaAcc
        'company_creditor_id' 	=> '',
        'company_account_institute' => '',
        'company_account_iban'  => '',
        'company_account_bic' 	=> '',
        ];

    /**
     * Create and Download Connection Information
     * @param int  	$id  	contract id
     * @return file response() - download box from browser
     *
     * @author Torsten Schmidt, Nino Ryschawy
     */
    public function connection_info_download($id, $return_pdf = true)
    {
        $c = Contract::find($id);
        $customer = $c->CccUser;

        // Get Login Data after updating or creating CccUser
        if ($customer) {
            $login_data = $customer->update();
        } else {
            $customer = new \Modules\Ccc\Entities\CccUser;
            $customer->contract_id = $c->id;
            $login_data = $customer->store();
        }
        if (! $login_data) {
            Log::error('CustomerConnectionInfo: Error Creating Login Data', [$c->id]);

            return \Redirect::back()->with('error_msg', trans('messages.conn_info_err_create'));
        }

        // get data to fill placeholders in tex template
        $ret = $this->fill_template_data($login_data, $c);

        //temporary solution to display a general message
        if (! empty($ret) && $ret < 0) {
            return \Redirect::back()->with('error_msg', trans('messages.conn_info_err_create'));
        }

        //writ empty values into log
        foreach ($this->data as $key => $value) {
            if (empty($value)) {
                Log::info('Value for '.$key.' not set or empty. Mayby cause an error on creating pdf-file.');
            }
        }

        // create pdf
        // TODO: try - catch exceptions that this function shall throw
        $ret = $this->make_conn_info_pdf($c);

        Log::info('Download Connection Information for CccUser: '.$customer->first_name.' '.$customer->last_name.' ('.$customer->id.')', [$c->id]);

        if (is_string($ret)) {
            return $return_pdf ? response()->download($ret) : $ret;
        }

        $err_msg = is_int($ret) ? trans('messages.conn_info_err_template') : trans('messages.conn_info_err_create');

        return \Redirect::back()->with('error_msg', $err_msg);
    }

    /**
     * Make Connection Info PDF File for Download
     *
     * @return string 	Absolute Path of PDF, Null on Error
     *
     * @author Nino Ryschawy
     */
    private function make_conn_info_pdf($contract)
    {
        // load template
        $template_dir = storage_path('app/config/ccc/template/');
        $template_filename = \Module::collections()->has('BillingBase') ? $contract->costcenter->sepaaccount->company->conn_info_template_fn : Ccc::first()->template_filename;

        if (! $template = file_get_contents($template_dir.$template_filename)) {
            Log::error('ConnectionInfo: Could not read template', [$template_dir.$template_filename]);

            return -1;
        }

        // make target tex file
        $dir_path = storage_path('app/tmp/');

        if (! is_dir($dir_path)) {
            mkdir($dir_path, 0733, true);
        }

        // $filename = str_replace(['.', ' ', '&', '|', ',', ';', '/', '"', "'", '>', '<'], '', $contract->number.'_'.$contract->firstname.'_'.$contract->lastname.'_info');
        $filename = sanitize_filename($contract->number.'_'.$contract->firstname.'_'.$contract->lastname.'_info');

        // echo "$filename\n";

        // Replace placeholder by value
        $template = str_replace('\\_', '_', $template);
        foreach ($this->data as $key => $string) {
            $template = str_replace('{'.$key.'}', $string, $template);
        }

        File::put($dir_path.$filename, $template);

        pdflatex($dir_path, $filename);

        // remove temporary files
        unlink($filename);
        unlink($filename.'.aux');
        unlink($filename.'.log');

        system("chown -R apache $dir_path");

        return $dir_path.$filename.'.pdf';
    }

    /**
     * Fills Data Structure so that placeholder from template can be replaced
     */
    private function fill_template_data($login_data, $contract)
    {
        $this->data['contract_nr'] = escape_latex_special_chars($contract->number);
        $this->data['contract_firstname'] = escape_latex_special_chars($contract->firstname);
        $this->data['contract_lastname'] = escape_latex_special_chars($contract->lastname);
        $this->data['contract_street'] = escape_latex_special_chars($contract->street);
        $this->data['contract_housenumber'] = $contract->house_number;
        $this->data['contract_zip'] = $contract->zip;
        $this->data['contract_city'] = escape_latex_special_chars($contract->city);
        $this->data['contract_district'] = escape_latex_special_chars($contract->district);
        $this->data['contract_address'] = ($contract->company ? escape_latex_special_chars($contract->company).'\\\\' : '').($contract->academic_degree ? "$contract->academic_degree " : '').($this->data['contract_firstname'].' '.$this->data['contract_lastname'].'\\\\').$this->data['contract_street'].' '.$this->data['contract_housenumber']."\\\\$contract->zip ".$this->data['contract_city'];
        $this->data['contract_address'] .= $this->data['contract_district'] ? ' OT '.$this->data['contract_district'] : '';
        $this->data['login_name'] = $login_data['login_name'];
        $this->data['psw'] = $login_data['password'];

        if (! \Module::collections()->has('BillingBase')) {
            return -1;
        }

        $costcenter = $contract->costcenter;

        if (! is_object($costcenter)) {
            Log::error('ConnectionInfoTemplate: Cannot use Billing specific data (SepaAccount/Company) to fill template - no CostCenter assigned', [$contract->id]);

            return -1;
        }

        $sepa_account = $costcenter->sepaaccount;

        if (! is_object($sepa_account)) {
            //todo: msg should be display in admin
            Log::error('ConnectionInfoTemplate: Cannot use Billing specific data (SepaAccount/Company) to fill template - CostCenter has no SepaAccount assigned', ['Costcenter' => $costcenter->name]);

            return -1;
        }

        $this->data['company_creditor_id'] = $sepa_account->creditorid;
        $this->data['company_account_institute'] = escape_latex_special_chars($sepa_account->institute);
        $this->data['company_account_iban'] = $sepa_account->iban;
        $this->data['company_account_bic'] = $sepa_account->bic;

        $company = $sepa_account->company;

        if (! is_object($company)) {
            //todo: msg should be display in admin
            Log::error('ConnectionInfoTemplate: Cannot use Billing specific data (Company) to fill template - SepaAccount has no Company assigned', ['SepaAccount' => $sepa_account->name]);

            return -1;
        }

        $this->data = array_merge($this->data, $company->template_data());

        if (empty($this->data['company_logo'])) {
            //todo: msg should be display in admin
            Log::error('Company Logo not set');

            return -1;
        }

        $this->data['company_logo'] = storage_path('app/config/billingbase/logo/'.$this->data['company_logo']);

        if (! file_exists($this->data['company_logo'])) {
            //todo: should tbe display in admin
            Log::error('File Company Log not found');

            return -1;
        }

        return 0;
    }

    /**
     * Stuff for the CCC on Customer side
     */
    private static $rel_dir_path_invoices = 'data/billingbase/invoice/';

    /**
     * Shows the invoice history for the Customer
     *
     * @return View
     */
    public function show()
    {
        $invoices = Auth::guard('ccc')->user()->contract->invoices()->with('settlementrun')->get();
        $invoice_links = [];

        $bsclass = ['info', 'active'];
        $start = $year = 0;
        foreach ($invoices as $key => $invoice) {
            // dont show unverified invoices
            if (! $invoice->settlementrun->verified) {
                continue;
            }

            if ($invoice->year != $year) {
                $start = ($start + 1) % 2;
            }

            $year = $invoice->year;

            $invoice_links[] = [
                'link' => \HTML::linkRoute('Customer.Download', str_pad($invoice->month, 2, 0, STR_PAD_LEFT).'/'.$invoice->year.($invoice->type == 'CDR' ? '-'.trans('messages.cdr') : ''), ['invoice' => $invoice->id]),
                'bsclass' => $bsclass[$start],
            ];
        }

        $emails = \Module::collections()->has('Mail') ? Auth::guard('ccc')->user()->contract->emails : collect();

        return \View::make('ccc::index', compact('invoice_links', 'emails'));
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
        $user = Auth::guard('ccc')->user();

        // check that only allowed files are downloadable - invoice must belong to customer and settlmentrun must be verified
        if (! $invoice || $invoice->contract_id != $user->contract_id || ! $invoice->settlementrun->verified) {
            throw new \App\Exceptions\AuthException('Permission Denied');
        }
        Log::info($user->first_name.' '.$user->last_name.' downloaded invoice '.$invoice->filename.' - id: '.$invoice->id);

        return response()->download($invoice->get_invoice_dir_path().$invoice->filename);
    }

    public function psw_update()
    {
        if (\Module::collections()->has('Mail') && \Input::has('email_id')) {
            $email = Email::findorFail(\Input::get('email_id'));
            // customer requested email object, which does not belong to him
            // (by manually changing the email_id in the url)
            if ($email->contract != Auth::guard('ccc')->user()->contract) {
                return abort(404);
            }
        }

        // dd(\Input::get(), \Input::get('password'));
        if (\Input::has('password')) {
            // update psw
            $customer = Auth::guard('ccc')->user();
            $rules = ['password' => 'required|confirmed|min:6'];
            $data = \Input::get();

            $validator = \Validator::make($data, $rules);

            if ($validator->fails()) {
                return \Redirect::back()->withErrors($validator)->withInput()->with('message', 'please correct the following errors')->with('message_color', 'danger');
            }

            if (isset($email)) {
                $email->psw_update(\Input::get('password'));
            } else {
                $customer->password = \Hash::make(\Input::get('password'));
                $customer->save();
            }

            Log::info($customer->first_name.' '.$customer->last_name.' ['.$customer->id.']'.' changed his/her password');

            return $this->show();
        }

        return \View::make('ccc::psw_update', compact('email'));
    }
}
