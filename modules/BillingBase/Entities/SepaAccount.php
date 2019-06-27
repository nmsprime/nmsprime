<?php

namespace Modules\BillingBase\Entities;

use IBAN;
use Storage;
use ChannelLog;
use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Providers\Currency;
use App\Http\Controllers\BaseViewController;
use Modules\BillingBase\Providers\SettlementRunData;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;

/**
 * Contains the functionality for Creating the SEPA-XML-Files of a SettlementRun
 *
 * TODO: implement translations with trans() instead of translate_label()-Function
 *
 * @author Nino Ryschawy
 */
class SepaAccount extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'sepaaccount';

    public $guarded = ['template_invoice_upload', 'template_cdr_upload'];

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            'name' 		=> 'required',
            'holder' 	=> 'required',
            'creditorid' => 'required|max:35|creditor_id',
            'iban' 		=> 'required|iban',
            'bic' 		=> 'bic',
            'template_invoice_upload' => 'mimetypes:text/x-tex,application/x-tex',
            'template_cdr_upload'     => 'mimetypes:text/x-tex,application/x-tex',
            ];
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'SEPA Account';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-credit-card"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        return ['table' => $this->table,
                'index_header' => [$this->table.'.name', $this->table.'.institute', $this->table.'.iban'],
                'order_by' => ['0' => 'asc'],  // columnindex => direction
                'header' =>  $this->name, ];
    }

    // View Relation.
    public function view_has_many()
    {
        $ret['Edit']['CostCenter']['class'] = 'CostCenter';
        $ret['Edit']['CostCenter']['relation'] = $this->costcenters;

        return $ret;
    }

    public function view_belongs_to()
    {
        return $this->company;
    }

    /**
     * Relationships:
     */
    public function costcenters()
    {
        return $this->hasMany('Modules\BillingBase\Entities\CostCenter', 'sepaaccount_id');
    }

    public function company()
    {
        return $this->belongsTo('Modules\BillingBase\Entities\Company');
    }

    /**
     * Observers
     */
    public static function boot()
    {
        self::observe(new SepaAccountObserver);
        parent::boot();
    }

    /**
     * BILLING STUFF
     */
    public $invoice_nr = 100000; 			// invoice number counter - default start nr is replaced by global config field
    private $invoice_nr_prefix;				// see constructor
    public $rcd; 							// requested collection date from global config

    /**
     * get billing user language
     * returns language letters selected in Billing Base Config
     * @author Christian Schramm
     */
    private function _get_billing_lang()
    {
        return \App::getLocale();
    }

    /**
     * Accounting Records
     * resulting in 2 files for items and tariffs
     * Filestructure is defined in add_accounting_record()-function
     * @var array
     */
    protected $acc_recs = ['tariff' => [], 'item' => []];

    /**
     * Booking Records
     * resulting in 2 files for records with sepa mandate or without
     * Filestructure is defined in add_booking_record()-function
     * @var array
     */
    protected $book_recs = ['sepa' => [], 'no_sepa' => []];

    /**
     * Invoices for every Contract that contain only the products/items that have to be paid to this account
     * (related through costcenter)
     * each entry results in one invoice pdf file
     * @var array
     */
    public $invoices = [];

    /**
     * Sepa XML
     * resulting in 2 possible files for direct debits or credits
     * @var array
     */
    protected $sepa_xml = ['debits' => [], 'credits' => []];

    /**
     * Returns composed invoice nr string
     *
     * @return string
     */
    private function _get_invoice_nr_formatted()
    {
        return $this->invoice_nr_prefix.$this->id.'/'.$this->invoice_nr;
    }

    /**
     * Initialises the SepaAccount for the current settlementrun
     *
     * @param int 	Requested Collection Date (day of month)
     */
    public function settlementrun_init()
    {
        $this->invoice_nr_prefix = date('Y', strtotime('first day of last month')).'/';

        $rcd = SettlementRunData::getConf('rcd');
        $this->rcd = $rcd ? date('Y-m-'.$rcd) : date('Y-m-d', strtotime('+1 day'));

        $this->_set_invoice_nr_counters();
    }

    /**
     * Set invoice number counter of SEPA-account
     *
     * @param array 	SepaAccount objects
     */
    private function _set_invoice_nr_counters()
    {
        // restart counter every year
        if (date('m', strtotime('first day of last month')) == '01') {
            if ($this->invoice_nr_start) {
                $this->invoice_nr = $this->invoice_nr_start - 1;
            }

            return;
        }

        $nr = AccountingRecord::where('sepaaccount_id', '=', $this->id)->orderBy('invoice_nr', 'desc')->select('invoice_nr')->first();

        $this->invoice_nr = is_object($nr) ? $nr->invoice_nr : $this->invoice_nr_start - 1;
    }

    /**
     * Adds an accounting record for this account of an item to the corresponding acc_recs-Array (item/tariff)
     *
     * @param object 	$item
     */
    public function add_accounting_record($item)
    {
        $time = strtotime('last day of last month');

        $data = [
            'Contractnr' 	=> $item->contract->number,
            'Invoicenr' 	=> $this->_get_invoice_nr_formatted(),
            'Target Month'  => date('m', $time),
            'Date'			=> ($this->_get_billing_lang() == 'de') ? date('d.m.Y', $time) : date('Y-m-d', $time),
            'Cost Center'   => isset($item->contract->costcenter->name) ? $item->contract->costcenter->name : '',
            'Count'			=> $item->count,
            'Description' 	=> $item->invoice_description,
            'Price'			=> ($this->_get_billing_lang() == 'de') ? number_format($item->charge, 2, ',', '.') : number_format($item->charge, 2, '.', ','),
            'Firstname'		=> $item->contract->firstname,
            'Lastname' 		=> $item->contract->lastname,
            'Street' 		=> $item->contract->street,
            'Housenr' 		=> $item->contract->house_number,
            'Zip' 			=> $item->contract->zip,
            'City' 			=> $item->contract->city,
        ];

        switch ($item->product->type) {
            case 'Internet':
            case 'TV':
            case 'Voip':
                $this->acc_recs['tariff'][] = $data;
                break;
            default:
                $this->acc_recs['item'][] = $data;
                break;
        }

        if ((count($this->acc_recs['tariff']) + count($this->acc_recs['item'])) >= 1000) {
            $this->write_billing_record_files();
        }
    }

    /**
     * Adds a booking record for this account with the charge of a contract to the corresponding book_recs-Array (sepa/no_sepa)
     *
     * @param object 	$contract, $mandate
     * @param float 	$charge
     */
    public function add_booking_record($contract, $mandate, $charge, $rcd)
    {
        $german = \App::getLocale() == 'de';

        $net = round($charge['net'], 2);
        $tax = round($charge['tax'], 2);

        $data = [
            'Contractnr'	=> $contract->number,
            'Invoicenr' 	=> $this->_get_invoice_nr_formatted(),
            'Date' 			=> $german ? date('d.m.Y', strtotime('last day of last month')) : date('Y-m-d', strtotime('last day of last month')),
            'RCD' 			=> $rcd,
            'Cost Center' 	=> isset($contract->costcenter->name) ? $contract->costcenter->name : '',
            'Description' 	=> '',
            'Net' 			=> $german ? number_format($net, 2, ',', '.') : number_format($net, 2, '.', ','),
            'Tax' 			=> $german ? number_format($tax, 2, ',', '.') : number_format($tax, 2, '.', ','),
            'Gross' 		=> $german ? number_format($net + $tax, 2, ',', '.') : number_format($net + $tax, 2, '.', ','),
            'Currency' 		=> Currency::get(),
            'Firstname' 	=> $contract->firstname,
            'Lastname' 		=> $contract->lastname,
            'Street' 		=> $contract->street,
            'Housenr' 		=> $contract->house_number,
            'Zip'			=> $contract->zip,
            'City' 			=> $contract->city,
            'District'      => $contract->district,
            ];

        // Set AG contact if present - cache file exists query for better performance
        if (! isset($this->setContact)) {
            $this->setContact = Storage::exists('config/billingbase/ags.php');
        }

        if ($this->setContact) {
            if (! isset($this->agContacts)) {
                $this->agContacts = require storage_path('app/config/billingbase/ags.php');
            }

            $data['AG'] = isset($this->agContacts[$contract->contact]) ? $this->agContacts[$contract->contact] : '';
        }

        if ($mandate) {
            $data2 = [
                'Account Holder' => $mandate->holder,
                'IBAN'			=> $mandate->iban,
                'BIC' 			=> $mandate->bic,
                'MandateID' 	=> $mandate->reference,
                'MandateDate'	=> $mandate->signature_date,
            ];

            $data = array_merge($data, $data2);

            $this->book_recs['sepa'][] = $data;
        } else {
            $this->book_recs['no_sepa'][] = $data;
        }
    }

    public function add_cdr_accounting_record($contract, $charge, $count)
    {
        $this->acc_recs['tariff'][] = [
            'Contractnr' 	=> $contract->number,
            'Invoicenr' 	=> $this->_get_invoice_nr_formatted(),
            'Target Month'  => date('m'),
            'Date' 			=> ($this->_get_billing_lang() == 'de') ? date('d.m.Y') : date('Y-m-d'),
            'Cost Center'   => isset($contract->costcenter->name) ? $contract->costcenter->name : '',
            'Count'			=> $count,
            'Description'   => 'Telephone Calls',
            'Price' 		=> $this->_get_billing_lang() == 'de' ? number_format($charge, 2, ',', '.') : number_format($charge, 2, '.', ','),
            'Firstname'		=> $contract->firstname,
            'Lastname' 		=> $contract->lastname,
            'Street' 		=> $contract->street,
            'Zip' 			=> $contract->zip,
            'City' 			=> $contract->city,
            ];
    }

    public function add_invoice_item($item, $settlementrun_id)
    {
        if (! isset($this->invoices[$item->contract->id])) {
            $this->invoices[$item->contract->id] = new Invoice;
            $this->invoices[$item->contract->id]->settlementrun_id = $settlementrun_id;
            $this->invoices[$item->contract->id]->add_contract_data($item->contract, $this->_get_invoice_nr_formatted());
        }

        $this->invoices[$item->contract->id]->add_item($item);
    }

    public function add_invoice_cdr($contract, $cdrs, $settlementrun_id)
    {
        if (! isset($this->invoices[$contract->id])) {
            $this->invoices[$contract->id] = new Invoice;
            $this->invoices[$contract->id]->settlementrun_id = $settlementrun_id;
            $this->invoices[$contract->id]->add_contract_data($contract, $this->_get_invoice_nr_formatted());
        }

        $this->invoices[$contract->id]->add_cdr_data($cdrs);
    }

    /**
     * Set Invoice Data (Mandate, Company, Amount to charge) for invoice (of contract) that belongs to this SepaAccount
     */
    public function set_invoice_data($contract, $mandate, $value, $rcd)
    {
        // Attention! the chronical order of these functions has to be kept until now because of dependencies for extracting the invoice text
        $this->invoices[$contract->id]->set_mandate($mandate);
        $this->invoices[$contract->id]->set_company_data($this);
        $this->invoices[$contract->id]->set_summary($value['net'], $value['tax'], $this);
        $this->invoices[$contract->id]->setRcd($rcd);
    }

    /**
     * Adds a sepa transfer for this account with the charge for a contract to the corresponding sepa_xml-Array (credit/debit)
     *
     * @param object 	$mandate
     * @param float 	$charge 	Must already be rounded to 2 decimals! (as it actually is we dont do it again here)
     * @param array 	$dates 		last run info is important for transfer type
     */
    public function add_sepa_transfer($mandate, $charge, $rcd)
    {
        if ($charge == 0) {
            return;
        }

        $info = $this->invoice_headline.' - ';
        $info .= $this->invoice_headline == 'Kostenumlage' ? date('Y').' (abzüglich Verstärker)' : trans('messages.month').' '.date('m/Y', strtotime('first day of last month'));
        $info .= ' - '.$mandate->contract->lastname.', '.$mandate->contract->firstname;
        $info .= ' - '.$this->company->name;

        // max length of SFirm
        if (strlen($info) > 140) {
            $info = substr($info, 0, 140);
        }

        // Credits
        if ($charge < 0) {
            $data = [
                'amount'                => $charge * (-1),
                'creditorIban'          => $mandate->iban,
                'creditorBic'           => $mandate->bic,
                'creditorName'          => $mandate->holder,
                'endToEndId'            => 'RG '.$this->_get_invoice_nr_formatted(),
                'remittanceInformation' => substr("$info - $mandate->reference", 0, 140),
            ];

            $this->sepa_xml['credits'][] = $data;

            return;
        }

        // Debits
        $data = [
            'endToEndId'			=> 'RG '.$this->_get_invoice_nr_formatted(),
            'amount'                => $charge,
            'debtorIban'            => $mandate->iban,
            'debtorBic'             => $mandate->bic,
            'debtorName'            => $mandate->holder,
            'debtorMandate'         => $mandate->reference,
            'debtorMandateSignDate' => $mandate->signature_date,
            'remittanceInformation' => $info,
        ];

        // determine transaction type: first/recurring/...
        $state = $mandate->state;
        $mandate->update_status();

        $this->sepa_xml['debits'][$state][$rcd][] = $data;
    }

    /**
     * Creates currently 4 files
     * the Accounting Record Files (Item/Tariff)
     * the Booking Record Files (Sepa/No Sepa)
     *
     * @author Nino Ryschawy, Christian Schramm
     * edit: filenames are language specific
     */
    private function write_billing_record_files()
    {
        $files['accounting'] = $this->acc_recs;
        $files['booking'] = $this->book_recs;

        foreach ($files as $key1 => $file) {
            foreach ($file as $key => $records) {
                if (! $records) {
                    continue;
                }

                $accounting = BaseViewController::translate_label($key1);
                $rec = $this->_get_billing_lang() == 'de' ? '' : '_records';

                $fn = self::get_relative_accounting_dir_path();
                $fn .= "/$accounting".'_'.BaseViewController::translate_label($key).$rec.'.txt';

                // echo "write ".count($records)." [".count($this->{($key1 == 'accounting' ? 'acc_recs' : 'book_recs')}[$key]) ."] to file $fn\n";

                if (! Storage::exists($fn)) {
                    // initialise record files with Column names as first line
                    $keys = [];
                    foreach (array_keys($records[0]) as $col) {
                        $keys[] = BaseViewController::translate_label($col);
                    }
                    Storage::put($fn, implode("\t", $keys));
                }

                $data = [];
                foreach ($records as $value) {
                    array_push($data, implode("\t", $value));
                }

                Storage::append($fn, implode("\n", $data));

                // free memory
                $this->{($key1 == 'accounting' ? 'acc_recs' : 'book_recs')}[$key] = null;

                $this->_log("$key1 $key records", $fn);
            }
        }
    }

    /*
     * Writes Paths of stored files to Logfiles and Console
     */
    private function _log($name, $pathname)
    {
        $path = storage_path('app');
        // echo "Stored $name in $path"."$pathname\n";
        ChannelLog::debug('billing', "New file $path/$pathname");
    }

    private function get_sepa_xml_msg_id()
    {
        return date('YmdHis').$this->id;		// km3 uses actual time
    }

    public function get_relative_accounting_dir_path()
    {
        return SettlementRun::get_relative_accounting_dir_path().'/'.sanitize_filename($this->name);
    }

    /**
     * Create SEPA XML File(s) for direct debits
     */
    private function make_debit_file()
    {
        if (! $this->sepa_xml['debits']) {
            return;
        }

        $msg_id = $this->get_sepa_xml_msg_id();
        $splitTransferTypes = SettlementRunData::getConf('split');

        // Set the initial information for direct debits
        $directDebit = TransferFileFacadeFactory::createDirectDebit($msg_id, $this->name, 'pain.008.003.02');

        foreach ($this->sepa_xml['debits'] as $type => $rcds) {
            if ($splitTransferTypes) {
                $directDebit = TransferFileFacadeFactory::createDirectDebit($msg_id.$type, $this->name, 'pain.008.003.02');
            }

            foreach ($rcds as $rcd => $records) {
                // create a payment
                $directDebit->addPaymentInfo($msg_id.$type.$rcd, [
                    'id'                    => $msg_id,
                    'creditorName'          => $this->name,
                    'creditorAccountIBAN'   => $this->iban,
                    'creditorAgentBIC'      => $this->bic,
                    'seqType'               => $type,
                    'creditorId'            => $this->creditorid,
                    'dueDate'               => $rcd,
                ]);

                // Add Transactions to the named payment
                foreach ($records as $r) {
                    $directDebit->addTransfer($msg_id.$type.$rcd, $r);
                }
            }

            // Retrieve the resulting XML
            $file = self::get_relative_accounting_dir_path();
            $file .= '/'.BaseViewController::translate_label('DD');
            $file .= $splitTransferTypes ? "_$type" : '';
            $file .= '.xml';

            if ($splitTransferTypes) {
                Storage::put($file, $directDebit->asXML());
                ChannelLog::debug('billing', "New file $file");
            }
        }

        if (! Storage::exists($file)) {
            Storage::put($file, $directDebit->asXML());
            ChannelLog::debug('billing', "New file $file");
        }
    }

    /**
     * Create SEPA XML File for direct credits
     */
    private function make_credit_file()
    {
        if (! $this->sepa_xml['credits']) {
            return;
        }

        $msg_id = $this->get_sepa_xml_msg_id();

        // Set the initial information for direct credits
        $customerCredit = TransferFileFacadeFactory::createCustomerCredit($msg_id.'C', $this->name);

        $customerCredit->addPaymentInfo($msg_id.'C', [
            'id'                      => $msg_id.'C',
            'debtorName'              => $this->name,
            'debtorAccountIBAN'       => $this->iban,
            'debtorAgentBIC'          => $this->bic,
        ]);

        // Add Transactions to the named payment
        foreach ($this->sepa_xml['credits'] as $r) {
            $customerCredit->addTransfer($msg_id.'C', $r);
        }

        // Retrieve the resulting XML
        $file = self::get_relative_accounting_dir_path();
        $file .= '/'.BaseViewController::translate_label('DC').'.xml';
        Storage::put($file, $customerCredit->asXML());

        $this->_log('sepa direct credit xml', $file);
    }

    /*
     * Creates all the billing files for the assigned objects
     */
    public function make_billing_files()
    {
        $this->write_billing_record_files();

        if ($this->sepa_xml['debits']) {
            $this->make_debit_file();
        }

        if ($this->sepa_xml['credits']) {
            $this->make_credit_file();
        }
    }

    /**
     * Returns BIC from iban and parsed config/data-file
     *
     * @TODO: store csv as php array for faster access!?
     *
     * @return string
     */
    public static function get_bic($iban)
    {
        $iban = new IBAN(strtoupper($iban));
        $country = strtolower($iban->Country());
        $bank = $iban->Bank();
        $csv = 'config/billingbase/bic_'.$country.'.csv';

        if (! file_exists(storage_path('app/'.$csv))) {
            return '';
        }

        $data = Storage::get($csv);
        $data_a = explode("\n", $data);

        foreach ($data_a as $key => $entry) {
            if (strpos($entry, $bank) !== false) {
                $entry = explode(';', $entry);

                return $entry[3];
            }
        }
    }
}

class SepaAccountObserver
{
    public function updated($sepaaccount)
    {
        \Artisan::call('queue:restart');
    }
}
