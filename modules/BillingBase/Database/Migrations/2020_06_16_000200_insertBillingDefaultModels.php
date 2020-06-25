<?php

use Modules\BillingBase\Entities\Company;
use Modules\BillingBase\Entities\CostCenter;
use Modules\BillingBase\Entities\SepaAccount;

class InsertBillingDefaultModels extends BaseMigration
{
    /**
     * Add default models to be quickly able to start settlement run after installation
     *
     * @return void
     */
    public function up()
    {
        if (Company::count() != 0 || SepaAccount::count() != 0 || CostCenter::count() != 0) {
            return;
        }

        Company::create([
            'name' => 'NMS Prime',
            'street' => 'Dörfelstraße 7',
            'zip' => '09496',
            'city' => 'Marienberg',
            'web' => 'www.nmsprime.com',
            'mail' => 'support@nmsprime.com',
            'logo' => 'nmsprime.pdf',
            'conn_info_template_fn' => 'default_coninfo.tex',
        ]);

        SepaAccount::create([
            'name' => 'NMS Prime',
            'creditorid' => '0123456789',
            'iban' => '0123456789',
            'company_id' => 1,
            'invoice_headline' => trans('messages.Invoice'),
            'invoice_text' => 'Please transfer the total amount with the following transfer reason within 14 days to the noted bank account:',
            'template_invoice' => 'default-invoice-template.tex',
            'template_cdr' => 'default-cdr-template.tex',
            'description' => trans('billingbase::messages.installationDefaultModel'),
        ]);

        CostCenter::create([
            'name' => 'NMS Prime',
            'sepaaccount_id' => 1,
            'billing_month' => 6,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Company::where('id', 1)
            ->where('name', 'NMS Prime')
            ->where('street', 'Dörfelstraße 7')
            ->where('zip', '09496')
            ->where('city', 'Marienberg')
            ->where('web', 'www.nmsprime.com')
            ->where('mail', 'support@nmsprime.com')
            ->where('logo', 'nmsprime.pdf')
            ->where('conn_info_template_fn', 'default_coninfo.tex')
            ->delete();

        SepaAccount::where('id', 1)
            ->where('name', 'NMS Prime')
            ->where('creditorid', '0123456789')
            ->where('iban', '0123456789')
            ->where('company_id', 1)
            ->where('invoice_headline', trans('messages.Invoice'))
            ->where('invoice_text', 'Please transfer the total amount with the following transfer reason within 14 days to the noted bank account:')
            ->where('template_invoice', 'default-invoice-template.tex')
            ->where('template_cdr', 'default-cdr-template.tex')
            ->delete();

        CostCenter::where('id', 1)
            ->where('name', 'NMS Prime')
            ->where('sepaaccount_id', 1)
            ->where('billing_month', 6)
            ->delete();
    }
}
