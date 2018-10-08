<?php

namespace Modules\BillingBase\Http\Controllers;

use Modules\BillingBase\Entities\Invoice;

class InvoiceController extends \BaseController
{
    protected $index_create_allowed = false;
    protected $index_delete_allowed = false;

    public function view_form_fields($model = null)
    {
        return [
            // array('form_type' => 'text', 'name' => 'rcd', 'description' => 'Day of Requested Collection Date'),
        ];
    }

    /**
     * Invoices are not editable - we (ab)use this function to Download the invoice
     */
    public function edit($id)
    {
        $invoice = Invoice::find($id);
        $file = $invoice->get_invoice_dir_path().$invoice->filename;

        return response()->download($file);
    }

    /**
     * Don't show all invoices on one page
     */
    public function index()
    {
        return \View::make('errors.generic');
    }
}
