<?php

namespace Modules\BillingBase\Providers;

use ChannelLog;
use Modules\BillingBase\Entities\CdrGetter;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaAccount;

/**
 * Preprocess all data from SepaAccount and it's related Company necessary to create an Invoice
 * and store in Laravels Service Container for better Performance while creating Invoices
 *
 * Call functions via SettlementRunData::getDate('Y')
 */
class SettlementRunProvider
{
    /**
     * All SepaAccounts indexed by it's ID
     *
     * @var array
     */
    protected $accounts;

    /**
     * Call Data Records of providers
     *
     * @var array
     */
    protected $cdrs;

    /**
     * The BillingBase model containing the global configuration entries for the billing module
     *
     * @var obj
     */
    protected $conf;

    /**
     * Contacts from PropertyManagement module
     *
     * @var Illuminate\Database\Eloquent\Collection
     */
    protected $contacts;

    /**
     * Often used dates in SettlementRun
     *
     * @var array
     */
    protected $dates;

    public function __construct()
    {
        $this->setCompanyData();
        $this->setBillingConf();
        $this->setDates();
    }

    ////////////////////////////////////////////////
    /////////////////// SETTERS ////////////////////
    ////////////////////////////////////////////////

    private function setBillingConf()
    {
        $this->conf = BillingBase::first();
    }

    private function setCompanyData()
    {
        foreach (SepaAccount::with('company')->get() as $acc) {
            $err_msg = '';

            if (! $acc->template_invoice) {
                ChannelLog::error('billing', "Missing templates for Invoices in SepaAccount $acc->name [$acc->id]");
            }

            $data['company_account_institute'] = escape_latex_special_chars($acc->institute);
            $data['company_account_iban'] = $acc->iban;
            $data['company_account_bic'] = $acc->bic;
            $data['company_creditor_id'] = $acc->creditorid;
            $data['invoice_headline'] = $acc->invoice_headline ? escape_latex_special_chars($acc->invoice_headline) : trans('messages.invoice');

            $company = $acc->company;

            if (! $company || ! $company->logo) {
                $err_msg = $company ? "Missing Company's Logo ($company->name)" : 'No Company assigned to Account '.$acc->name;
                ChannelLog::error('billing', $err_msg);

                continue;
            }

            $data = array_merge($data, $company->template_data());

            $data['company_registration_court'] = $data['company_registration_court_1'] ? $data['company_registration_court_1'].'\\\\' : '';
            $data['company_registration_court'] .= $data['company_registration_court_2'] ? $data['company_registration_court_2'].'\\\\' : '';
            $data['company_registration_court'] .= $data['company_registration_court_3'];

            $data['company_logo'] = storage_path('app/config/billingbase/logo/').$company->logo;

            $this->accounts[$acc->id] = $data;
        }
    }

    /**
     * Instantiates an Array of all necessary date formats needed during execution of this Command
     *
     * Also needed in Item::calculate_price_and_span and in DashboardController!!
     */
    private function setDates()
    {
        $this->dates = [
            'today'         => date('Y-m-d'),
            'm'             => date('m'),
            'Y'             => date('Y', strtotime('first day of last month')),

            'this_m'        => date('Y-m'),
            'thism_01'      => date('Y-m-01'),
            'thism_bill'    => date('m/Y'),

            'lastm'         => date('m', strtotime('first day of last month')),         // written this way because of known bug ("-1 month" or "last month" is erroneous)
            'lastm_01'      => date('Y-m-01', strtotime('first day of last month')),
            'lastm_bill'    => date('m/Y', strtotime('first day of last month')),
            'lastm_Y'       => date('Y-m', strtotime('first day of last month')),       // strtotime(first day of last month) is integer with actual timestamp!

            'nextm_01'      => date('Y-m-01', strtotime('+1 month')),

            'null'          => '0000-00-00',
            'm_in_sec'      => 60 * 60 * 24 * 30,                       // month in seconds
        ];
    }

    private function setCdrs()
    {
        $class = new CdrGetter;

        $class::get();
        $this->cdrs = $class->parse();
    }

    public function setContacts()
    {
        if (! \Module::collections()->has('PropertyManagement')) {
            return;
        }

        $this->contacts = \Modules\PropertyManagement\Entities\Contact::get();
    }

    ////////////////////////////////////////////////
    /////////////////// GETTERS ////////////////////
    ////////////////////////////////////////////////

    public function getCompanyData($id)
    {
        return $this->accounts[$id];
    }

    /**
     * Get global Billing config (from BillingBase model)
     *
     * @return mixed
     */
    public function getConf($key = '')
    {
        if (! $key) {
            return $this->conf;
        }

        return $this->conf->{$key};
    }

    public function getDate($key = '')
    {
        if (! $key) {
            return $this->dates;
        }

        return $this->dates[$key];
    }

    public function getCdrs($number = 0)
    {
        if (! $this->cdrs) {
            $this->setCdrs();
        }

        if (! $number) {
            return $this->cdrs;
        }

        return $this->cdrs[$number] ?? [];
    }

    public function getContact($id)
    {
        if (! $this->contacts) {
            $this->setContacts();
        }

        $contact = $this->contacts->find($id);

        return $contact ? $contact->label() : '';
    }
}
