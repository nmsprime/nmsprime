<?php

namespace Modules\BillingBase\Entities;

use Storage;
use ChannelLog;
use Modules\BillingBase\Providers\Currency;
use Modules\BillingBase\Providers\SettlementRunData;

/**
 * Contains Functions to collect Data for Invoice & create the corresponding PDFs
 *
 * TODO: Translate for multiple language support, improve functional structure
 *
 * @author Nino Ryschawy
 */
class Invoice extends \BaseModel
{
    public $table = 'invoice';
    public $observer_enabled = false;

    private $tax;

    protected $sepaaccount_id;

    /**
     * View Stuff
     */
    public static function view_headline()
    {
        return 'Invoices';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-id-card-o"></i>';
    }

    public function view_index_label()
    {
        $type = $this->type == 'CDR' ? ' ('.trans('messages.Call Data Record').')' : '';

        return ['table' => $this->table,
                'header' =>  $this->year.' - '.str_pad($this->month, 2, 0, STR_PAD_LEFT).$type,
                'bsclass' => $this->get_bsclass(),
            ];
    }

    public function get_bsclass()
    {
        if ($this->charge < 0) {
            return 'info';
        }

        if ($this->charge == 0) {
            return 'active';
        }

        return '';
    }

    /**
     * Relations
     */
    public function contract()
    {
        return $this->belongsTo('Modules\ProvBase\Entities\Contract');
    }

    public function settlementrun()
    {
        return $this->belongsTo('Modules\BillingBase\Entities\SettlementRun');
    }

    public function debts()
    {
        if (\Module::collections()->has('Dunning')) {
            return $this->hasMany('Modules\Dunning\Entities\Debt');
        }
    }

    /**
     * Init Observer
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new InvoiceObserver);
    }

    /**
     * @var strings  - template directory paths relativ to Storage app path and temporary filename variables
     */
    private $rel_template_dir_path = 'config/billingbase/template/';
    private $template_invoice_fname = '';
    private $template_cdr_fname = '';
    private $rel_logo_dir_path = 'config/billingbase/logo/';

    /**
     * @var string - invoice directory path relativ to Storage app path and temporary filename variables
     */
    public static $rel_storage_invoice_dir = 'data/billingbase/invoice/';

    // temporary variables for settlement run without .pdf extension
    private $filename_invoice = '';
    private $filename_cdr = '';

    /**
     * Temporary CDR Variables
     *
     * @var bool 		$has_cdr 	1 - Invoice has Call Data Records, 0 - Only Invoice
     * @var int 	$time_cdr 	Unix Timestamp of month of the telefone calls - set in add_cdr_data()
     */
    public $has_cdr = 0;
    private $time_cdr;

    /**
     * @var bool 	Error Flag - if set then invoice cant be created
     */
    private $error_flag = false;

    /**
     * @var array 	All the data used to fill the invoice template file
     */
    public $data = [

        // Company - NOTE: Set by Company->template_data()
        'company_name'			=> '',
        'company_street'		=> '',
        'company_zip'			=> '',
        'company_city'			=> '',
        'company_phone'			=> '',
        'company_fax'			=> '',
        'company_mail'			=> '',
        'company_web'			=> '',
        'company_registration_court' => '', 	// all 3 fields together separated by tex newline ('\\\\')
        'company_registration_court_1' => '',
        'company_registration_court_2' => '',
        'company_registration_court_3' => '',
        'company_management' 	=> '',
        'company_directorate' 	=> '',
        'company_tax_id_nr' 	=> '',
        'company_tax_nr' 		=> '',
        'company_logo'			=> '',

        // SepaAccount
        'company_creditor_id' 	=> '',
        'company_account_institute' => '',
        'company_account_iban'  => '',
        'company_account_bic' 	=> '',

        // Contract
        'contract_id' 			=> '',
        'contract_nr' 			=> '',
        'contract_firstname' 	=> '',
        'contract_lastname' 	=> '',
        'contract_company' 		=> '',
        'contract_department'	=> '',
        'contract_district'		=> '',
        'contract_street' 		=> '',
        'contract_housenumber'	=> '',
        'contract_zip' 			=> '',
        'contract_city' 		=> '',
        'contract_address' 		=> '', 			// concatenated address for begin of letter

        'contract_mandate_iban'	=> '', 			// iban of the customer
        'contract_mandate_ref'	=> '', 			// mandate reference of the customer

        'date_invoice'			=> '',
        'invoice_nr' 			=> '',
        'invoice_text'			=> '',			// appropriate invoice text from company dependent of total charge & sepa mandate as table with sepa mandate info
        'invoice_msg' 			=> '', 			// invoice text without sepa mandate information
        'invoice_headline'		=> '',
        'rcd' 					=> '',			// Fälligkeitsdatum / Buchungsdatum
        'cdr_month'				=> '', 			// Month of Call Data Records
        'payment_method'        => '',          // for conditional texts in PDF [directdebit|banktransfer|none]

        // Charges
        'item_table_positions'  => '', 			// tex table of all items to be charged for this invoice
        'cdr_charge' 			=> '', 			// Float with costs resulted from telephone calls
        'cdr_table_positions'	=> '',			// tex table of all call data records
        'table_summary' 		=> '', 			// preformatted table - use following three keys to set table by yourself
        'table_sum_tax_percent' => '', 			// The tax percentage with % character
        'table_sum_charge_net'  => '', 			// net charge - without tax
        'table_sum_tax' 		=> '', 			// The tax
        'table_sum_charge_total' => '', 		// total charge - with tax
        'table_sum_charge_net_formatted' => '', // net charge formatted for billing language (e.g. for german: comma as decimal separator and point as thousands separator)
        'table_sum_tax_formatted' => '',
        'table_sum_charge_total_formatted' => '',

        // Cancelation Dates - as prescribed by law from 2018-01-01
        'start_of_term' 	=> '', 				// contract start
        'maturity' 			=> '', 				// Tariflaufzeit
        'end_of_term' 		=> '', 				// Aktuelles Vertragsende
        'period_of_notice' 	=> '', 				// Kündigungsfrist
        'last_cancel_date' 	=> '', 				// letzter Kündigungszeitpunkt der aktuellen Laufzeit, if empty -> contract was already canceled!
        'canceled_to'       => '',              // Contract was already canceled
    ];

    public function get_invoice_dir_path()
    {
        return storage_path('app/'.self::$rel_storage_invoice_dir.$this->contract_id.'/');
    }

    /**
     * Get absolute filepath for invoice from json object
     *  Has better performance when invoice object must not be instantiated
     *
     * @param  json obj
     * @return string
     */
    public static function getFilePathFromData(&$invoice)
    {
        return storage_path('app/'.self::$rel_storage_invoice_dir.$invoice->contract_id.'/'.$invoice->filename);
    }

    /**
     * @param 	string 		$type 		invoice or cdr
     * @return 	string 					absolute Path & Filename of Template File
     */
    private function _get_abs_template_path($type = 'invoice')
    {
        return storage_path('app/'.$this->rel_template_dir_path.$this->{'template_'.$type.'_fname'});
    }

    /**
     * @return string 	Date part of the invoice filename
     *
     * NOTE: This has to be adapted if we want support creating invoices for multiple months in the past
     */
    private static function _get_invoice_filename_date_part()
    {
        return date('Y_m', strtotime('first day of last month'));
    }

    /**
     * Format date string dependent of set locale/billing language
     *
     * @param string/integer
     * @return string
     * @deprecated use app/heplers function with the same name
     */
    public static function langDateFormat($date)
    {
        if (! $date) {
            return $date;
        }

        $date = is_int($date) ? $date : strtotime($date);

        switch (\App::getLocale()) {
            case 'de':
                return date('d.m.Y', $date);

            case 'es':
                return date('d/m/Y', $date);

            default:
                return date('Y-m-d', $date);
        }
    }

    public function add_contract_data($contract, $invoice_nr)
    {
        $this->data['contract_id'] = $contract->id;
        $this->contract_id = $contract->id;
        $this->data['contract_nr'] = $contract->number;
        $this->data['contract_firstname'] = escape_latex_special_chars($contract->firstname);
        $this->data['contract_lastname'] = escape_latex_special_chars($contract->lastname);
        $this->data['contract_company'] = escape_latex_special_chars($contract->company);
        $this->data['contract_department'] = escape_latex_special_chars($contract->department);
        $this->data['contract_street'] = escape_latex_special_chars($contract->street);
        $this->data['contract_housenumber'] = $contract->house_number;
        $this->data['contract_zip'] = $contract->zip;
        $this->data['contract_city'] = escape_latex_special_chars($contract->city);
        $this->data['contract_district'] = escape_latex_special_chars($contract->district);
        $this->data['contract_address'] = '';
        if ($contract->company) {
            $this->data['contract_address'] .= escape_latex_special_chars($contract->company).'\\\\';
            if ($contract->department) {
                $this->data['contract_address'] .= escape_latex_special_chars($contract->department).'\\\\';
            }
        }
        $this->data['contract_address'] .= ($contract->academic_degree ? "$contract->academic_degree " : '').
            (($this->data['contract_firstname'] || $this->data['contract_lastname']) ? ($this->data['contract_firstname'].' '.$this->data['contract_lastname'].'\\\\') : '');
        $this->data['contract_address'] .= $this->data['contract_district'] ? $this->data['contract_district'].'\\\\' : '';
        $this->data['contract_address'] .= $this->data['contract_street'].' '.$this->data['contract_housenumber']."\\\\$contract->zip ".$this->data['contract_city'];
        $this->data['contract_address'] = trim($this->data['contract_address']);
        $this->data['start_of_term'] = self::langDateFormat($contract->contract_start);
        $this->data['invoice_nr'] = $invoice_nr ? $invoice_nr : $this->data['invoice_nr'];
        $this->data['date_invoice'] = date('d.m.Y', strtotime('last day of last month'));
        $this->filename_invoice = $this->filename_invoice ?: self::_get_invoice_filename_date_part().'_'.str_replace('/', '_', $invoice_nr);
        $this->tax = SettlementRunData::getConf('tax');

        $this->setCancelationDates($contract);
    }

    /**
     * Set:
     *  actual end of term
     *  period of notice
     *  latest possible date of cancelation
     */
    private function setCancelationDates($contract)
    {
        $ret = $contract->getCancelationDates(date('Y-m-d', strtotime('last day of last month')));

        // Canceled contract or tariff
        // e.g. customers that get tv amplifier refund, but dont have any tariff
        if ($ret['canceled_to'] || ! $ret['tariff']) {
            ChannelLog::debug('billing', "Contract $contract->number is already canceled", [$this->data['contract_id']]);

            // Set cancelation date contracts valid_to
            if ($ret['canceled_to']) {
                $this->data['canceled_to'] = self::langDateFormat($ret['canceled_to']);

                return;
            }

            // Get end of term of canceled tariff
            $tariff = $contract->items()
                ->join('product as p', 'item.product_id', '=', 'p.id')
                ->whereIn('type', ['Internet', 'Voip'])
                ->orderBy('item.valid_to', 'desc')
                ->first();

            $this->data['canceled_to'] = $tariff ? self::langDateFormat($tariff->valid_to) : '';

            return;
        }

        $txt_pon = $txt_m = '';

        if ($ret['tariff']) {
            // Set period of notice and maturity string of last tariff
            $nr = preg_replace('/[^0-9]/', '', $ret['tariff']->product->period_of_notice ?: Product::$pon);
            $span = str_replace($nr, '', $ret['tariff']->product->period_of_notice ?: Product::$pon);
            $txt_pon = $nr.' '.trans_choice("messages.$span", $nr).($ret['tariff']->product->maturity ? '' : ' '.trans('messages.eom'));

            $nr = preg_replace('/[^0-9]/', '', $ret['maturity']);
            $span = str_replace($nr, '', $ret['maturity']);
            $txt_m = $nr.' '.trans_choice("messages.$span", $nr);
        }

        if (! $ret['cancelation_day']) {
            ChannelLog::info('billing', "Contract $contract->number was canceled with target ".$ret['end_of_term']);
        }

        $cancel_dates = [
            'end_of_term' => self::langDateFormat($ret['end_of_term']),
            'maturity' 		=> $txt_m,
            'period_of_notice' => $txt_pon,
            'last_cancel_date' => self::langDateFormat($ret['cancelation_day']),
        ];

        $this->data = array_merge($this->data, $cancel_dates);
    }

    public function add_item($item)
    {
        $count = $item->count ?: 1;
        $price = $item->charge / $item->count;
        $price = \App::getLocale() == 'de' ? number_format($price, 2, ',', '.') : number_format($price, 2);
        $sum = \App::getLocale() == 'de' ? number_format($item->charge, 2, ',', '.') : number_format($item->charge, 2);

        $this->data['item_table_positions'] .= $item->count.' & '.escape_latex_special_chars($item->invoice_description).' & '.$price.Currency::get().' & '.$sum.Currency::get().'\\\\';
    }

    public function set_mandate($mandate)
    {
        if (! $mandate) {
            return;
        }

        $this->data['contract_mandate_iban'] = $mandate->iban;
        $this->data['contract_mandate_ref'] = $mandate->reference;
    }

    /**
     * Maps appropriate Company and SepaAccount data to current Invoice
     * address
     * creditor bank account data
     * invoice footer data
     * invoice template path
     *
     * @param 	Obj 	SepaAccount
     * @return 	bool 	false - error (missing required data), true - success
     */
    public function set_company_data($account)
    {
        $this->data = array_merge($this->data, SettlementRunData::getCompanyData($account->id));

        $this->template_invoice_fname = $account->template_invoice;
        $this->template_cdr_fname = $account->template_cdr;
    }

    /**
     * Set total sum and invoice text for this invoice - TODO: Translate!!
     */
    public function set_summary($net, $tax, $account)
    {
        $this->sepaaccount_id = $account->id;

        $tax_percent = $tax ? $this->tax : 0;
        $tax_percent .= '\%';

        $total = $net + $tax;

        $this->data['table_sum_tax_percent'] = $tax_percent;
        // dont use thousands separator for numbers that are compared or used for further calculations as it's hard to compare in latex
        $this->data['table_sum_charge_net'] = number_format($net, 2, '.', '');
        $this->data['table_sum_tax'] = number_format($tax, 2, '.', '');
        $this->data['table_sum_charge_total'] = number_format($total, 2, '.', '');
        $this->data['table_sum_charge_net_formatted'] = \App::getLocale() == 'de' ? number_format($net, 2, ',', '.') : number_format($net, 2);
        $this->data['table_sum_tax_formatted'] = \App::getLocale() == 'de' ? number_format($tax, 2, ',', '.') : number_format($tax, 2);
        $this->data['table_sum_charge_total_formatted'] = \App::getLocale() == 'de' ? number_format($total, 2, ',', '.') : number_format($total, 2);

        $this->data['table_summary'] = '~ & Gesamtsumme: & ~ & '.$this->data['table_sum_charge_net_formatted'].Currency::get().'\\\\';
        $this->data['table_summary'] .= "~ & $tax_percent MwSt: & ~ & ".$this->data['table_sum_tax_formatted'].Currency::get().'\\\\';
        $this->data['table_summary'] .= '~ & \textbf{Rechnungsbetrag:} & ~ & \textbf{'.$this->data['table_sum_charge_total_formatted'].Currency::get().'}\\\\';

        // make transfer reason (Verwendungszweck)
        if ($transfer_reason = $account->company->transfer_reason) {
            preg_match_all('/(?<={)[^}]*(?=})/', $transfer_reason, $matches);
            foreach ($matches[0] as $value) {
                if (array_key_exists($value, $this->data)) {
                    $transfer_reason = str_replace('{'.$value.'}', $this->data[$value], $transfer_reason);
                }
            }
        } else {
            $transfer_reason = $this->data['invoice_nr'].' '.$this->data['contract_nr'];
        }		// default

        // prepare invoice text table and get appropriate template
        if ($net >= 0 && $this->data['contract_mandate_iban']) {
            $template = $account->invoice_text_sepa;
            // $text = 'IBAN:\>'.$this->data['contract_mandate_iban'].'\\\\Mandatsreferenz:\>'.$this->data['contract_mandate_ref'].'\\\\Gläubiger-ID:\>'.$this->data['company_creditor_id'];
            $text = 'IBAN: &'.$this->data['contract_mandate_iban'].'\\\\Mandatsreferenz: &'.$this->data['contract_mandate_ref'].'\\\\Gläubiger-ID: &'.$this->data['company_creditor_id'];
            $this->data['payment_method'] = 'directdebit';
        } elseif ($net < 0 && $this->data['contract_mandate_iban']) {
            $template = $account->invoice_text_sepa_negativ;
            $text = 'IBAN: &'.$this->data['contract_mandate_iban'].'\\\\Mandatsreferenz: &'.$this->data['contract_mandate_ref'];
            $this->data['payment_method'] = 'none';
        } elseif ($net >= 0 && ! $this->data['contract_mandate_iban']) {
            $template = $account->invoice_text;
            $text = 'IBAN: &'.$this->data['company_account_iban'].'\\\\BIC: &'.$this->data['company_account_bic'].'\\\\Verwendungszweck: &'.$transfer_reason;
            $this->data['payment_method'] = 'banktransfer';
        } elseif ($net < 0 && ! $this->data['contract_mandate_iban']) {
            $template = $account->invoice_text_negativ;
            $text = '';
            $this->data['payment_method'] = 'none';
        }

        // replace placeholder of invoice text
        preg_match_all('/(?<={)[^}]*(?=})/', $template, $matches);
        foreach ($matches[0] as $value) {
            if (array_key_exists($value, $this->data)) {
                $template = str_replace('{'.$value.'}', $this->data[$value], $template);
            }
        }

        // set invoice text
        // $this->data['invoice_text'] = $template.'\\\\'.'\begin{tabbing} \hspace{9em}\=\kill '.$text.' \end{tabbing}';
        $this->data['invoice_msg'] = escape_latex_special_chars($template);
        $this->data['invoice_text'] = '\begin{tabular} {@{}ll} \multicolumn{2}{@{}L{\textwidth}} {'.$template.'}\\\\'.$text.' \end{tabular}';
    }

    public function setRcd($rcd)
    {
        $this->data['rcd'] = $rcd;
    }

    /**
     * @param 	cdrs 	Array		Call Data Record array designated for this Invoice formatted by parse_cdr_data in SettlementRunCommand
     * @param   conf   	model 		BillingBase
     */
    public function add_cdr_data($cdrs)
    {
        $this->has_cdr = 1;
        $offset = SettlementRunData::getConf('cdr_offset');
        // $this->time_cdr = $time_cdr = strtotime($cdrs[0][1]);
        $this->time_cdr = $time_cdr = $offset ? strtotime('-'.($offset + 1).' month') : strtotime('first day of last month');
        $this->data['cdr_month'] = date('m/Y', $time_cdr);

        // TODO: customer can request to show his tel nrs cut by the 3 last nrs (TKG §99 (1))
        // TODO: dont show target nrs that have to stay anonym (church, mental consultation, ...) (TKG §99 (2))

        $sum = $count = 0;
        foreach ($cdrs as $entry) {
            $line = date('d.m.Y', strtotime($entry['date'])).' '.$entry['starttime'].' & '.$entry['duration'];
            if (is_string($entry['called_nr'])) {
                $called_number = $entry['called_nr'];
            } elseif (is_array($entry['called_nr'])) {
                if ($entry['called_nr'][0] == 'enviaCDR') {
                    $_ = $entry['called_nr'];
                    $called_number = iconv('CP1252', 'UTF-8', '\\emph{anderer Anbieter:}\\newline- \textbf{'.$_[1].'}\\newline- '.$_[2].'\\newline- '.$_[3].'\\newline- '.$_[4]);
                } else {
                    // throw Exception instead of just logging the problem: logic error in code creating the CDR data
                    throw new \UnexpectedValueException('Invalid first value in array provided for CDR called number: '.$entry['called_nr'][0]);
                }
            } else {
                // throw Exception instead of just logging the problem: logic error in code creating the CDR data
                throw new \TypeError('Expected string or array, '.gettype($entry['called_nr']).' given');
            }
            $line .= ' & '.$entry['calling_nr'].' & '.$called_number;
            // $line .= ' & '.sprintf("%01.4f", $entry['price']).'\\\\';
            $line .= ' & '.(\App::getLocale() == 'de' ? number_format($entry['price'], 4, ',', '.') : number_format($entry['price'], 4)).'\\\\';

            $this->data['cdr_table_positions'] .= $line;
            $sum += $entry['price'];
            $count++;
        }

        $this->data['cdr_charge'] = $sum;

        $sum = \App::getLocale() == 'de' ? number_format($sum, 2, ',', '.') : number_format($sum, 2);
        $this->data['cdr_table_positions'] .= '\\hline ~ & ~ & ~ & \textbf{Summe} & \textbf{'.$sum.'}\\\\';
        $plural = $count > 1 ? 'en' : '';
        $this->data['item_table_positions'] .= "1 & $count Telefonverbindung".$plural.' & '.$sum.Currency::get().' & '.$sum.Currency::get().'\\\\';

        $this->filename_cdr = date('Y_m', $time_cdr).'_cdr';
    }

    /**
     * Create Invoice files and Database Entries
     *
     * TODO: consider template type - .tex or .odt
     */
    public function make_invoice()
    {
        $dir = $this->get_invoice_dir_path();

        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        if ($this->has_cdr) {
            $this->_make_tex('cdr');
            $this->_create_db_entry(0);
        }

        if ($this->data['item_table_positions']) {
            $this->_make_tex('invoice');
            $this->_create_db_entry();
        } else {
            ChannelLog::warning('billing', 'No Items for Invoice - only build CDR', [$this->data['contract_id']]);
        }

        // Store as pdf
        $this->_create_pdfs();

        system('chown -R apache '.$dir);
    }

    /**
     * Create Database Entry for an Invoice or a Call Data Record
     *
     * @param 	int 	$type 	[1] Invoice, [0] Call Data Record
     */
    private function _create_db_entry($type = 1)
    {
        // TODO: implement time of cdr as generic, variable way
        $time = $type ? strtotime('first day of last month') : $this->time_cdr; // strtotime('-2 month');

        $data = [
            'contract_id' 	=> $this->data['contract_id'],
            'settlementrun_id' 	=> $this->settlementrun_id,
            'sepaaccount_id' => $this->sepaaccount_id,
            'year' 			=> date('Y', $time),
            'month' 		=> date('m', $time),
            'filename' 		=> $type ? $this->filename_invoice.'.pdf' : $this->filename_cdr.'.pdf',
            'type'  		=> $type ? 'Invoice' : 'CDR',
            'number' 		=> $this->data['invoice_nr'],
            'charge' 		=> $type ? $this->data['table_sum_charge_net'] : $this->data['cdr_charge'],
        ];

        $ret = self::create($data);

        $this->id = $ret->id;
    }

    /**
     * Creates Tex File of Invoice or CDR
     * replaces all '\_' and all fields of data array that are set by it's value
     *
     * @param string	$type 	'invoice'/'cdr'
     */
    private function _make_tex($type = 'invoice')
    {
        if ($this->error_flag) {
            ChannelLog::error('billing', "Missing Data from SepaAccount or Company to Create $type", [$this->data['contract_id']]);

            return -2;
        }

        if (! $template = file_get_contents($this->_get_abs_template_path($type))) {
            ChannelLog::error('billing', 'Failed to Create Invoice: Could not read template '.$this->_get_abs_template_path($type), [$this->data['contract_id']]);

            return -3;
        }

        // Replace placeholder by value
        $template = $this->_replace_placeholder($template);

        // ChannelLog::debug('billing', 'Store '. self::$rel_storage_invoice_dir.$this->data['contract_id'].'/'.$this->{"filename_$type"});

        // Create tex file(s)
        Storage::put(self::$rel_storage_invoice_dir.$this->data['contract_id'].'/'.$this->{"filename_$type"}, $template);
    }

    private function _replace_placeholder($template)
    {
        $template = str_replace('\\_', '_', $template);

        foreach ($this->data as $key => $string) {
            $template = str_replace('{'.$key.'}', $string, $template);
        }

        return $template;
    }

    /**
     * Creates the pdfs out of the prepared tex files - Note: this function is very time consuming
     */
    private function _create_pdfs()
    {
        $dir_path = $this->get_invoice_dir_path();

        $file_paths['Invoice'] = $this->get_invoice_dir_path().$this->filename_invoice;
        $file_paths['CDR'] = $this->get_invoice_dir_path().$this->filename_cdr;

        foreach ($file_paths as $key => $file) {
            if (is_file($file)) {
                pdflatex($dir_path, $file, true);
                ChannelLog::debug('billing', "New $key for Contract ".$this->data['contract_nr'], [$this->data['contract_id'], $file.'.pdf']);
            }
        }
    }

    /**
     * Removes the temporary latex files after all pdfs were created simultaniously by multiple threads
     * Test if all Invoices were created successfully
     *
     * @throws Exception 	when pdflatex was not able to create PDF from tex document for an invoice
     */
    public static function remove_templatex_files($sepaacc = null)
    {
        $invoices = \DB::table('invoice')->whereBetween('created_at', [date('Y-m-01'), date('Y-m-01', strtotime('next month'))]);
        // $invoices = self::whereBetween('created_at', [date('Y-m-01 00:00:00'), date('Y-m-01 00:00:00', strtotime('next month'))]);
        if ($sepaacc) {
            $invoices = $invoices->where('sepaaccount_id', '=', $sepaacc->id);
        }

        $invoices = $invoices->get();
        foreach ($invoices as $invoice) {
            // $fn = $invoice->get_invoice_dir_path().$invoice->filename;
            $fn = self::getFilePathFromData($invoice);

            if (is_file($fn)) {
                $fn = str_replace('.pdf', '', $fn);
                unlink($fn);
                unlink($fn.'.aux');
                unlink($fn.'.log');
            } else {
                // possible errors: syntax/filename/...
                ChannelLog::error('billing', 'pdflatex: Error creating Invoice PDF '.$fn);
            }
        }
    }

    /**
     * Remove all old Invoice & CDR DB-Entries & Files as it's prescribed by law
     * Germany: CDRs 6 Months (§97 TKG) - Invoices ?? -
     *
     * NOTE: This can be different from country to country
     * TODO: Remove old Invoices
     */
    public static function cleanup()
    {
        $conf = BillingBase::first();

        $period = $conf->cdr_retention_period; 				// retention period - total months CDRs should be kept
        $offset = $conf->cdr_offset;
        $target_time_o = \Carbon\Carbon::create()->subMonthsNoOverflow($offset + $period);

        \Log::info("Delete all CDRs older than $period Months");

        $query = self::where('type', '=', 'CDR')
                ->where('year', '<=', $target_time_o->__get('year'))
                ->where('month', '<', $target_time_o->__get('month'));

        $cdrs = $query->get();

        foreach ($cdrs as $cdr) {
            $filepath = $cdr->get_invoice_dir_path().$cdr->filename;
            if (is_file($filepath)) {
                unlink($filepath);
            }
        }

        $query->delete();

        // Delete all CDR CSVs older than $period months
        \App::setLocale($conf->userlang);

        $cdrFiles = CdrGetter::get_cdr_pathnames($target_time_o->subMonthNoOverflow()->__get('timestamp'));

        foreach ($cdrFiles as $f) {
            if (is_file($f)) {
                unlink($f);
            }
        }
    }
}

class InvoiceObserver
{
    public function deleted($invoice)
    {
        // Delete PDF from Storage
        $ret = Storage::delete($invoice->rel_storage_invoice_dir.$invoice->contract_id.'/'.$invoice->filename);
        ChannelLog::info('billing', 'Removed Invoice from Storage', [$invoice->contract_id, $invoice->filename]);
    }
}
