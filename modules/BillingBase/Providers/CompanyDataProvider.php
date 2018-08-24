<?php

namespace Modules\BillingBase\Providers;

use Modules\BillingBase\Entities\SepaAccount;

/**
 * Preprocess all data from SepaAccount and it's related Company necessary to create an Invoice
 * and store in Laravels Service Container for better Performance while creating Invoices
 */
class CompanyDataProvider
{
    /**
     * All SepaAccounts indexed by it's ID
     *
     * @var array
     */
    protected $accounts;

    public function __construct()
    {
        $this->_set_data();
    }

    private function _set_data()
    {
        foreach (SepaAccount::with('company')->get() as $acc) {
            $err_msg = '';

            if (! $acc->template_invoice) {
                \ChannelLog::error('billing', "Missing templates for Invoices in SepaAccount $acc->name [$acc->id]");
            }

            $data['company_account_institute'] = escape_latex_special_chars($acc->institute);
            $data['company_account_iban'] = $acc->iban;
            $data['company_account_bic'] = $acc->bic;
            $data['company_creditor_id'] = $acc->creditorid;
            $data['invoice_headline'] = $acc->invoice_headline ? escape_latex_special_chars($acc->invoice_headline) : trans('messages.invoice');

            $company = $acc->company;

            if (! $company || ! $company->logo) {
                $err_msg = $company ? "Missing Company's Logo ($company->name)" : 'No Company assigned to Account '.$acc->name;
                \ChannelLog::error('billing', $err_msg);
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

    public function get($id)
    {
        return $this->accounts[$id];
    }
}
