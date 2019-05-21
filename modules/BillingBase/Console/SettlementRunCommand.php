<?php

namespace Modules\BillingBase\Console;

use DB;
use Storage;
use ChannelLog as Log;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Queue\SerializesModels;
use Modules\BillingBase\Entities\Item;
use Modules\ProvBase\Entities\Contract;
use Illuminate\Queue\InteractsWithQueue;
use Modules\BillingBase\Entities\Invoice;
use Modules\BillingBase\Entities\Product;
use Modules\BillingBase\Entities\Salesman;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\BaseViewController;
use Illuminate\Database\Eloquent\Collection;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\SettlementRun;
use Symfony\Component\Console\Input\InputOption;
use Modules\BillingBase\Entities\AccountingRecord;
use Symfony\Component\Console\Input\InputArgument;
use Modules\BillingBase\Http\Controllers\SettlementRunController;

class SettlementRunCommand extends Command implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The console command & table name, description, data arrays
     *
     * @var string
     */
    public $name = 'billing:settlementrun';
    protected $description = 'Execute/Create SettlementRun - create Direct Debit/Credit XML, invoices and accounting/booking record files';

    protected $dates;					// offen needed time strings for faster access - see constructor
    protected $sr;
    protected $sepaacc; 			// is set in constructor if we only wish to run command for specific SepaAccount

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SettlementRun $sr, SepaAccount $sa = null)
    {
        $this->sr = $sr;
        $this->sepaacc = $sa;

        parent::__construct();
    }

    /**
     * Execute the console command
     *
     * Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
     */
    public function handle()
    {
        // $start = microtime(true);
        $this->dates = self::create_dates_array();

        // Determine SR (SettlementRun) ID as this is necessary to create relation between Invoice & SR
        if (! $this->sr->getAttribute('id')) {
            $this->sr = SettlementRun::where('year', '=', $this->dates['Y'])->where('month', '=', (int) $this->dates['lastm'])->orderBy('id', 'desc')->first();
            SettlementRun::where('id', $this->sr->id)->update(['updated_at' => date('Y-m-d H:i:s')]);
        }

        if (! $this->sr || ! $this->sr->getAttribute('id')) {
            // Note: create will run the observer that calls this command again with this SR
            SettlementRun::create(['year' => $this->dates['Y'], 'month' => $this->dates['lastm']]);
            echo "\nSettlementRun is called in background via Queue\n";
            exit(0);
        }

        // Set execution timestamp to always show log entries on SettlementRun edit page
        SettlementRun::where('id', $this->sr->id)->update(['executed_at' => \Carbon\Carbon::now()]);

        Log::debug('billing', ' ##############################');
        Log::debug('billing', ' ## Start Accounting Command ##');
        Log::debug('billing', ' ##############################');

        // Get Collection of SepaAccounts
        if ($this->output) {
            $this->sepaacc = $this->argument('sepaaccount_id') ? SepaAccount::findOrFail($this->argument('sepaaccount_id')) : null;
        }

        $sepaaccs = $this->sepaacc ? new Collection([0 => $this->sepaacc]) : SepaAccount::all();

        if (! $sepaaccs) {
            Log::error('billing', 'There are no Sepa Accounts to create Billing Files for - Stopping here!');
            throw new Exception('There are no Sepa Accounts to create Billing Files for');
        }

        // TODO: set full run flag here as well?

        if ($sepaaccs->count() == 1) {
            $a = $sepaaccs->first();
            Log::info('billing', "Execute settlementrun for SepaAccount $a->name (ID: $a->id)");
        } else {
            Log::info('billing', 'Execute settlementrun for all SepaAccounts');
        }

        // init must be before _get_cdr_data()
        $this->_init($sepaaccs);

        // Get all Data from Database
        $this->user_output('Load Data...', 0);

        // get call data records as ordered structure (array)
        $cdrs = $this->_get_cdr_data();
        // $cdrs = [[]];
        if (! $cdrs) {
            Log::warning('billing', 'No Call Data Records available for this Run!');
        }

        // TODO: use load_salesman_from_contracts() in future ?
        $salesmen = Salesman::all();
        $contracts = self::load_contracts($this->sepaacc ? $this->sepaacc->id : 0);

        // show progress bar if not called silently via queues (would throw exception otherwise)
        $num = count($contracts);
        if ($this->output) {
            echo "Create Invoices:\n";
            $bar = $this->output->createProgressBar($num);
            $bar->start();
        }

        /*
         * Loop over all Contracts
         */
        foreach ($contracts as $i => $c) {
            if ($this->output) {
                $bar->advance();
            } elseif (! ($i % 10)) {
                self::push_state((int) $i / $num * 100, 'Create Invoices');
                // echo ($i/$num [$c->id][".(memory_get_usage()/1000000)."]\r";
            }

            // Skip invalid contracts
            if (! $c->check_validity('yearly') && ! (isset($cdrs[$c->id]) || isset($cdrs[$c->number]))) {
                Log::debug('billing', "Contract $c->number [$c->id] is invalid for current year");
                continue;
            }

            if (! $c->costcenter) {
                Log::error('billing', trans('messages.accCmd_error_noCC', ['contract_nr' => $c->number, 'contract_id' => $c->id]));
                continue;
            }

            /*
             * Collect item specific data for all billing files
             */
            foreach ($c->items as $item) {
                // skip items that are related to a deleted product
                if (! isset($item->product)) {
                    Log::error('billing', "Product of $item->accounting_text was deleted", [$c->id]);
                    continue;
                }

                // skip if price is 0 (or item dates are invalid)
                if (! ($ret = $item->calculate_price_and_span($this->dates))) {
                    Log::debug('billing', 'Item '.$item->product->name.' isn\'t charged this month', [$c->id]);
                    continue;
                }

                // get account via costcenter
                $costcenter = $item->get_costcenter();
                $acc = $sepaaccs->find($costcenter->sepaaccount_id);

                // If SR runs for specific SA skip item if it does not belong to that SA (SepaAccount)
                if (! $acc) { // || ($this->sepaacc && ($this->sepaacc->id != $acc->id)))
                    continue;
                }

                // increase invoice nr of sepa account
                if (! isset($c->charge[$acc->id])) {
                    $c->charge[$acc->id] = ['net' => 0, 'tax' => 0];
                    $acc->invoice_nr += 1;
                }

                // increase charge for account by price, calculate tax
                $c->charge[$acc->id]['net'] += $item->charge;
                $c->charge[$acc->id]['tax'] += $item->product->tax ? $item->charge * $this->conf->tax / 100 : 0;

                $item->charge = round($item->charge, 2);

                // save to accounting table (as backup for future) - NOTE: invoice nr counters are set initially from that table
                $rec = new AccountingRecord;
                $rec->store_item($item, $acc);

                // add item to accounting records of account, invoice and salesman
                $acc->add_accounting_record($item);
                $acc->add_invoice_item($item, $this->conf, $this->sr->id);
                if ($c->salesman_id) {
                    $salesmen->find($c->salesman_id)->add_item($c, $item, $acc->id);
                }
            } // end of item loop

            /**
             * Add Call Data Records
             * Note: dont add CDRs if this command is not destined to the corresponding SepaAcc
             */
            $acc = $sepaaccs->find($c->costcenter->sepaaccount_id);
            if ($acc) {
                $charge = $calls = $id = 0;

                if (isset($cdrs[$c->id])) {
                    $id = $c->id;
                } elseif (isset($cdrs[$c->number])) {
                    $id = $c->number;
                }

                if ($id) {
                    // calculate charge and count
                    foreach ($cdrs[$id] as $entry) {
                        $charge += $entry['price'];
                        $calls++;
                    }

                    // increase charge for booking record
                    // Keep this order in case we need to increment the invoice nr if only cdrs are charged for this contract
                    if (! isset($c->charge[$acc->id])) {
                        // this case should only happen when contract/voip tarif ended and deferred CDRs are calculated
                        Log::notice('billing', trans('messages.accCmd_notice_CDR', ['contract_nr' => $c->number, 'contract_id' => $c->id]));
                        $c->charge[$acc->id] = ['net' => 0, 'tax' => 0];
                        $acc->invoice_nr += 1;
                    }

                    $c->charge[$acc->id]['net'] += $charge;
                    $c->charge[$acc->id]['tax'] += $charge * $this->conf->tax / 100;

                    // accounting record
                    $rec = new AccountingRecord;
                    $rec->add_cdr($c, $acc, $charge, $calls);
                    $acc->add_cdr_accounting_record($c, $charge, $calls);

                    // invoice
                    $acc->add_invoice_cdr($c, $cdrs[$id], $this->conf, $this->sr->id);
                }
            }

            /*
             * Add contract specific data for accounting files
             */
            // get actual globally valid sepa mandate (valid for all CostCenters/SepaAccounts)
            $mandate_global = $c->get_valid_mandate();

            foreach ($c->charge as $acc_id => $value) {
                $value['net'] = round($value['net'], 2);
                $value['tax'] = round($value['tax'], 2);

                $acc = $sepaaccs->find($acc_id);

                $mandate_specific = $c->get_valid_mandate('now', $acc->id);
                $mandate = $mandate_specific ?: $mandate_global;

                $acc->add_booking_record($c, $mandate, $value, $this->conf);
                $acc->set_invoice_data($c, $mandate, $value);

                // create invoice pdf already - this task is the most timeconsuming and therefore threaded!
                $acc->invoices[$c->id]->make_invoice();
                unset($acc->invoices[$c->id]);

                // skip sepa part if contract has no valid mandate
                if (! $mandate) {
                    Log::debug('billing', "Contract $c->number [$c->id] has no valid sepa mandate for SepaAccount $acc->name [$acc->id]");
                    continue;
                }

                $mandate->setRelation('contract', $c);
                $acc->add_sepa_transfer($mandate, $value['net'] + $value['tax']);
            }
        } // end of loop over contracts

        if ($this->output) {
            $bar->finish();
            echo "\n";
        }

        // avoid deleting temporary latex files before last invoice was built (multiple threads are used)
        // and wait for all invoice pdfs to be created for concatenation in zipCommand@_make_billing_files()
        usleep(500000);

        // while removing it's tested if all PDFs were created successfully
        Invoice::remove_templatex_files($this->sepaacc ?: null);
        $this->_make_billing_files($sepaaccs, $salesmen);

        if ($this->output) {
            Storage::delete('tmp/accCmdStatus');
        } else {
            self::push_state(100, 'Finished');
        }
    }

    /**
     * @param  int if > 0 the pathname of the timestamps month is returned
     * @return string  Absolute path of accounting directory for actual settlement run (when no argument is specified)
     */
    public static function get_absolute_accounting_dir_path($timestamp = 0)
    {
        return storage_path('app/'.self::get_relative_accounting_dir_path($timestamp));
    }

    /**
     * @param  int if > 0 the pathname of the timestamps month is returned
     * @return string  Relative path of accounting dir to storage dir for actual settlement run
     */
    public static function get_relative_accounting_dir_path($timestamp = 0)
    {
        $time = $timestamp ?: strtotime('first day of last month');

        return 'data/billingbase/accounting/'.date('Y-m', $time);
    }

    /**
     * (1) Clear/Create (Prepare) Directories
     *
     * (2) Initialise models for this billing cycle (could also be done during runtime but with performance degradation)
     * invoice number counter
     * storage directories
     * Set Language for Billing
     * Remove already created Invoice Database Entries
     */
    private function _init($sepaaccs)
    {
        $this->conf = BillingBase::first();

        // set language for this run
        \App::setLocale($this->conf->userlang);

        // create directory structure and remove old invoices
        if (is_dir(self::get_absolute_accounting_dir_path())) {
            $this->user_output('Clean up directory...', 0);
            SettlementRunController::directory_cleanup(null, $this->sepaacc);
        } else {
            mkdir(self::get_absolute_accounting_dir_path(), 0700, true);
        }

        // SepaAccount
        foreach ($sepaaccs as $acc) {
            $acc->settlementrun_init($this->conf->rcd ? date('Y-m-'.$this->conf->rcd) : date('Y-m-d', strtotime('+1 day')));
        }

        // Reset yearly payed items payed_month column
        if ($this->dates['lastm'] == '01') {
            // Senseless where statement is necessary because update can not be called directly
            Item::where('payed_month', '!=', '0')->update(['payed_month' => '0']);
        }
    }

    /**
     * Get all Contracts an invoice shall be created for
     *
     * NOTE: If SettlementRun is executed for a specific SepaAccount this function will only return the contracts
     * 	that can have resulting charges for that account
     *
     * @param int
     *
     * @TODO: Dont load contracts that are outdated
     */
    public static function load_contracts($sepaaccount_id)
    {
        // All contracts - with eager loading
        if (! $sepaaccount_id) {
            // Log all contracts where invoice creation is deactivated
            $deactivated = Contract::where('create_invoice', '=', 0)->orderBy('number')->get(['number'])->pluck('number')->all();
            Log::info('billing', trans('messages.accCmd_invoice_creation_deactivated', ['contractnrs' => implode(',', $deactivated)]));

            return Contract::orderBy('number')->with('items.product', 'costcenter')
                ->where('create_invoice', '!=', 0)
                // TODO: make time we have to look back dependent of CDR offset in BillingBase config
                ->where(whereLaterOrEqual('contract_end', date('Y-m-d', strtotime('last day of sep last year'))))
                ->get();
        }

        // Log all contracts where invoice creation is deactivated
        $deactivated = Contract::leftJoin('item as i', 'contract.id', '=', 'i.contract_id')
            ->leftJoin('costcenter as ccc', 'contract.costcenter_id', '=', 'ccc.id')
            ->leftJoin('costcenter as cci', 'i.costcenter_id', '=', 'cci.id')
            ->leftJoin('product as p', 'i.product_id', '=', 'p.id')
            ->leftJoin('costcenter as ccp', 'p.costcenter_id', '=', 'ccp.id')
            ->where('create_invoice', '=', 0)
            ->where(whereLaterOrEqual('contract.contract_end', date('Y-m-d', strtotime('last day of nov last year'))))
            ->where('i.valid_from_fixed', 1)
            ->where(function ($query) use ($sepaaccount_id) {
                $query
                ->where('ccc.sepaaccount_id', '=', $sepaaccount_id)
                ->orWhere('ccp.sepaaccount_id', '=', $sepaaccount_id)
                ->orWhere('cci.sepaaccount_id', '=', $sepaaccount_id);
            })
            ->select('contract.number')
            ->with('items.product', 'costcenter')
            ->distinct()
            ->orderBy('number')
            ->get()->pluck('number')->all();

        Log::info('billing', trans('messages.accCmd_invoice_creation_deactivated', ['contractnrs' => implode(',', $deactivated)]));

        return Contract::leftJoin('item as i', 'contract.id', '=', 'i.contract_id')
            ->leftJoin('costcenter as ccc', 'contract.costcenter_id', '=', 'ccc.id')
            ->leftJoin('costcenter as cci', 'i.costcenter_id', '=', 'cci.id')
            ->leftJoin('product as p', 'i.product_id', '=', 'p.id')
            ->leftJoin('costcenter as ccp', 'p.costcenter_id', '=', 'ccp.id')
            ->where('create_invoice', '!=', 0)
            ->where(whereLaterOrEqual('contract.contract_end', date('Y-m-d', strtotime('last day of nov last year'))))
            ->where('i.valid_from_fixed', 1)
            ->where(function ($query) use ($sepaaccount_id) {
                $query
                ->where('ccc.sepaaccount_id', '=', $sepaaccount_id)
                ->orWhere('ccp.sepaaccount_id', '=', $sepaaccount_id)
                ->orWhere('cci.sepaaccount_id', '=', $sepaaccount_id);
            })
            ->select('contract.*')
            ->with('items.product', 'costcenter')
            ->distinct()
            ->orderBy('number')
            ->get();

        // Contracts that are related to specific SepaAccount
        // All contracts with costcenter belonging to the specific SA (SEPA-Account)
        $filter1 = Contract::join('costcenter as cc', 'contract.costcenter_id', '=', 'cc.id')
            ->where('cc.sepaaccount_id', '=', $sepaaccount_id)
            ->select('contract.*');

        // All contracts with items with costcenter belonging to the specific SA
        $filter2 = Contract::join('item as i', 'contract.id', '=', 'i.contract_id')
            ->join('costcenter as cc', 'i.costcenter_id', '=', 'cc.id')
            ->where('cc.sepaaccount_id', '=', $sepaaccount_id)
            ->select('contract.*');

        // All contracts with items of products with costcenter belonging to the specific SA
        $filter3 = Contract::join('item as i', 'contract.id', '=', 'i.contract_id')
            ->join('product as p', 'i.product_id', '=', 'p.id')
            ->join('costcenter as cc', 'p.costcenter_id', '=', 'cc.id')
            ->where('cc.sepaaccount_id', '=', $sepaaccount_id)
            ->select('contract.*');

        // Not working: some contracts with create_invoice = 0 are included!
        return $filter1->union($filter2)->union($filter3)
            ->where('create_invoice', '!=', 0)
            ->select('contract.*')
            ->with('items.product', 'costcenter')
            ->distinct()
            ->orderBy('number')
            ->get();
    }

    /**
     * Load only necessary salesmen from contract list
     *
     * @param Collection
     */
    public static function load_salesman_from_contracts($contracts)
    {
        $salesmen_ids = $contracts->filter(function ($contract) {
            if ($contract->salesman_id) {
                return $contract;
            }
        })
            ->pluck('salesman_id')->unique()->all();

        $salesmen = Salesman::whereIn('id', $salesmen_ids)->get();

        return $salesmen;
    }

    /*
     * Stores all billing files besides invoices in the directory defined as property of this class
     */
    private function _make_billing_files($sepaaccs, $salesmen)
    {
        foreach ($sepaaccs as $acc) {
            $acc->make_billing_files();
        }

        if (isset($salesmen[0])) {
            $salesmen[0]->prepare_output_file();
            foreach ($salesmen as $sm) {
                $sm->print_commission();
            }

            // delete file if there are no entries
            if (Storage::size(Salesman::get_storage_rel_filename()) < 160) {
                Storage::delete(Salesman::get_storage_rel_filename());
            }
        }

        // create zip file
        \Artisan::call('billing:zip', ['sepaacc_id' => $this->sepaacc ? $this->sepaacc->id : 0]);
    }

    /**
     * Write Status to temporary file as buffer for settlement run status bar in GUI
     *
     * @param int
     * @param string 	Note: is automatically translated to the appropriate language if string exists in lang/./messages.php
     */
    public static function push_state($value, $message)
    {
        $arr = [
            'message' => BaseViewController::translate_label($message),
            'value'   => round($value),
            ];

        Storage::put('tmp/accCmdStatus', json_encode($arr));
    }

    /**
     * Push message and state either to commandline or to state file for GUI
     *
     * @param string 	msg
     * @param float 	state
     */
    public function user_output($msg, $state)
    {
        if ($this->output) {
            echo "$msg\n";
        } else {
            self::push_state($state, $msg);
        }
    }

    /**
     * @return string 	Filename   e.g.: 'Call Data Record_2016_08.csv' or if app language is german 'Einzelverbindungsnachweis_2015_01.csv'
     */
    public static function _get_cdr_filename()
    {
        $offset = BillingBase::first()->cdr_offset;
        $time = $offset ? strtotime('-'.($offset + 1).' month') : strtotime('first day of last month');

        return \App\Http\Controllers\BaseViewController::translate_label('Call Data Record').'_'.date('Y_m', $time).'.csv';
    }

    /**
     * Calls cdrCommand to get Call data records from Provider and formats relevant data to structured array
     *
     * @return array 	[contract_id => [phonr_nr, time, duration, ...],
     *					 next_contract_id => [...],
     * 					 ...]
     *					on success, else 2 dimensional empty array
     *
     * NOTE/TODO: 1000 Phonecalls need a bit more than 1 MB memory - if files get too large and we get memory
     *  problems again, we should probably save calls to database and get them during command when needed
     */
    private function _get_cdr_data()
    {
        $calls = $calls_total = [[]];

        \Artisan::call('billing:cdr');

        $filepaths = cdrCommand::get_cdr_pathnames();

        foreach ($filepaths as $provider => $filepath) {
            if (! is_file($filepath)) {
                Log::error('billing', "Missing call data record file from $provider");
                throw new Exception("Missing call data record file from $provider");
            }

            $calls = $this->{"_parse_$provider".'_csv'}($filepath);

            // combine arrays - NOTE: Dont simplify by array_merge or + operator here!
            foreach ($calls as $cnr => $entries) {
                foreach ($entries as $entry) {
                    $calls_total[$cnr][] = $entry;
                }
            }
        }

        return $calls_total;
    }

    /**
     * Parse envia TEL CSV and Check if customerNr to Phonenr assignment exists
     *
     * @return array  [contract_id/contract_number => [Calling Number, Date, Starttime, Duration, Called Number, Price], ...]
     */
    protected function _parse_envia_csv($filepath)
    {
        Log::debug('billing', 'Parse envia TEL Call Data Records CSV');

        $csv = file($filepath);
        $calls = [[]];

        if (! $csv) {
            Log::error('billing', 'Empty envia call data record file');

            return $calls;
        }

        $pns = $this->_get_phonenumbers('sip.enviatel.net')->all();
        $pns2 = $this->_get_phonenumbers('verbindet.net', false)->all();

        $pns = array_merge($pns, $pns2);

        foreach ($pns as $pn) {
            $pn_customer[substr_replace($pn->prefix_number, '49', 0, 1).$pn->number][] = $pn->contractnr;
        }

        // this is needed for backward compatibility to old system
        $cdr_nr_prefix_replacements = self::getCdrNrPrefixReplacements();

        // skip first line of csv (column description)
        unset($csv[0]);
        $price = $count = 0;
        $unassigned = $mismatches = [];
        $customer_nrs = self::_get_customer_nrs();

        foreach ($csv as $line) {
            $arr = str_getcsv($line, ';');

            // replace prefixes of enviatel customer numbers that not exist in NMSPrime
            $customer_nr = str_replace($cdr_nr_prefix_replacements, '', $arr[0]);

            $data = [
                'calling_nr' => $arr[3],
                'date' 		=> substr($arr[4], 4).'-'.substr($arr[4], 2, 2).'-'.substr($arr[4], 0, 2),
                'starttime' => $arr[5],
                'duration' 	=> $arr[6],
                'called_nr' => $arr[7],
                'price' 	=> str_replace(',', '.', $arr[10]),
                ];

            if (in_array($customer_nr, $customer_nrs)) {
                $calls[$customer_nr][] = $data;

                // check and log if phonenumber does not exist or does not belong to contract
                if (! isset($pn_customer[$data['calling_nr']])) {
                    $mismatches[$customer_nr][$data['calling_nr']] = 'missing';
                } elseif (! in_array($customer_nr, $pn_customer[$data['calling_nr']])) {
                    $mismatches[$customer_nr][$data['calling_nr']] = 'mismatch';
                }
            } else {
                // cumulate price of calls that can not be assigned to any contract
                if (! isset($unassigned[$arr[0]][$data['calling_nr']])) {
                    $unassigned[$arr[0]][$data['calling_nr']] = ['count' => 0, 'price' => 0];
                }

                $unassigned[$arr[0]][$data['calling_nr']]['count'] += 1;
                $unassigned[$arr[0]][$data['calling_nr']]['price'] += $data['price'];
            }
        }

        $this->_log_phonenumber_mismatches($mismatches, 'EnviaTel');
        $this->_log_unassigned_calls($unassigned);

        // warning when there are 5 times more customers then calls
        if ($csv && (count($customer_nrs) > 10 * count($csv))) {
            Log::warning('billing', 'Very little data in enviatel call data record file ('.count($csv).' records). Possibly missing data!');
        }

        return $calls;
    }

    /**
     * Parse HLKomm CSV
     *
     * @return array 	[contract_id/contract_number => [Calling Number, Date, Starttime, Duration, Called Number, Price], ...]
     */
    protected function _parse_hlkomm_csv($filepath)
    {
        $csv = file($filepath);
        $calls = [[]];

        if (! $csv) {
            Log::warning('billing', 'Empty hlkomm call data record file');

            return [[]];
        }

        // skip first 5 lines (descriptions)
        unset($csv[0], $csv[1], $csv[2], $csv[3], $csv[4]);

        $config = BillingBase::first();
        $unassigned = [];

        // get phonenr to contract_id listing - needed because only phonenr is mentioned in csv
        // BUG: Actually when a phonenumber is deleted on date 1.5. and then the same number is assigned to another contract, all
        // records of 1.4.-30.4. would be assigned to the new contract that actually hasn't done any call yet
        // As precaution we warn the user when he changes or creates a phonenumber so that this bug would be affected
        $phonenumbers_db = $this->_get_phonenumbers('sip.hlkomm.net');

        foreach ($phonenumbers_db as $value) {
            if ($value->username) {
                if (substr($value->username, 0, 4) == '0049') {
                    $phonenrs[substr_replace($value->username, '49', 0, 4)] = $value->contract_id;
                }
            }
        }

        // create structured array
        foreach ($csv as $line) {
            $line = str_getcsv($line, "\t");
            $phonenr1 = $line[4].$line[5].$line[6];			// calling nr
            $phonenr2 = $line[7].$line[8].$line[9];			// called nr

            $data = [
                'calling_nr' => $phonenr1,
                'date' 		=> $line[0],
                'starttime' => $line[1],
                'duration' 	=> $line[10],
                'called_nr' => $phonenr2,
                'price' 	=> str_replace(',', '.', $line[13]),
                ];

            // calculate price with hlkomms distance zone
            // $a[5] = strpos($line[3], 'Mobilfunk national') !== false ? $a[5] * ($config->voip_extracharge_mobile_national / 100 + 1) : $a[5] * ($config->voip_extracharge_default / 100 + 1);
            $data['price'] = $line[15] == '990711' ? $data['price'] * ($config->voip_extracharge_mobile_national / 100 + 1) : $data['price'] * ($config->voip_extracharge_default / 100 + 1);

            if (isset($phonenrs[$phonenr1])) {
                $calls[$phonenrs[$phonenr1]][] = $data;
            } elseif (isset($phonenrs[$phonenr2])) {
                // our phonenr is the called nr - TODO: proof if this case can actually happen - normally this shouldnt be the case
                $calls[$phonenrs[$phonenr2]][] = $data;
            } else {
                // there is a phonenr entry in csv that doesnt exist in our db - this case should never happen
                if (! isset($unassigned[$phonenr1])) {
                    $unassigned[$phonenr1] = ['count' => 0, 'price' => 0];
                }

                $unassigned[$phonenr1]['count'] += 1;
                $unassigned[$phonenr1]['price'] += $data['price'];
            }
        }

        foreach ($unassigned as $pn => $arr) {
            $price = \App::getLocale() == 'de' ? number_format($arr['price'], 2, ',', '.') : number_format($arr['price'], 2, '.', ',');
            Log::error('billing', trans('messages.cdr_missing_phonenr', ['phonenr' => $pn, 'count' => $arr['count'], 'price' => $price, 'currency' => $this->conf->currency]));
        }

        return $calls;
    }

    /**
     * Parse PurTel CSV
     *
     * NOTE: Username to phonenumber combination must never change!
     *
     * @return array 	[contract_id/contract_number => [Calling Number, Date, Starttime, Duration, Called Number, Price], ...]
     */
    protected function _parse_purtel_csv($filepath)
    {
        Log::debug('billing', 'Parse PurTel Call Data Records CSV');

        $csv = file($filepath);
        $calls = [[]];

        if (! $csv) {
            Log::warning('billing', 'Empty envia call data record file');

            return $calls;
        }

        // skip first line of csv (column description)
        unset($csv[0]);

        $logged = $phonenumbers = $unassigned = $mismatches = [];
        $price = $count = 0;
        $customer_nrs = self::_get_customer_nrs();
        $registrar = 'deu3.purtel.com';
        $cdr_first_day_of_month = date('Y-m-01', strtotime('first day of -'.(1 + $this->conf->cdr_offset).' month'));

        // get phonenumbers because only username is given in CDR.csv
        $phonenumbers_db = $this->_get_phonenumbers($registrar);

        // Identification and comparison is done via unique username of phonenr and customer number (contract number must be equal to external customer nr)
        foreach ($phonenumbers_db as $p) {
            $phonenumbers[$p->username] = $p->prefix_number.$p->number;
            $contractnrs[$p->username][] = $p->contractnr;
        }

        // this is needed for backward compatibility to old system
        $cdr_nr_prefix_replacements = self::getCdrNrPrefixReplacements();

        foreach ($csv as $line) {
            $arr = str_getcsv($line, ';');

            $customer_nr = str_replace($cdr_nr_prefix_replacements, '', $arr[7]);
            $username = $arr[2];
            $date = explode(' ', $arr[1]);

            if (! isset($phonenumbers[$username])) {
                // Log::warning('billing', "Phonenr of contract $customer_nr with username $username not found in DB. Calling number will not appear on invoice.");
                $phonenumbers[$username] = ' - ';
            }

            $data = [
                'calling_nr' => $phonenumbers[$username],
                'date'      => $date[0],
                'starttime' => $date[1],
                'duration'  => gmdate('H:i:s', $arr[4]),
                'called_nr' => $arr[3],
                'price'     => $arr[10] / 100,
                ];

            if (in_array($customer_nr, $customer_nrs)) {
                $calls[$customer_nr][] = $data;

                // check and log if phonenumber does not exist or does not belong to contract
                if (! isset($contractnrs[$username])) {
                    $mismatches[$customer_nr][$data['calling_nr']] = 'missing';
                } elseif (! in_array($customer_nr, $contractnrs[$username])) {
                    $mismatches[$customer_nr][$data['calling_nr']] = 'mismatch';
                }
            } else {
                // cumulate price of calls that can not be assigned to any contract
                if (! isset($unassigned[$arr[7]][$data['calling_nr']])) {
                    $unassigned[$arr[7]][$data['calling_nr']] = ['count' => 0, 'price' => 0];
                }

                $unassigned[$arr[7]][$data['calling_nr']]['count'] += 1;
                $unassigned[$arr[7]][$data['calling_nr']]['price'] += $data['price'];
            }
        }

        $this->_log_phonenumber_mismatches($mismatches, 'PurTel');
        $this->_log_unassigned_calls($unassigned);

        if ($logged) {
            Log::notice('billing', 'Purtel-CSV: Discard calls from customer numbers '.implode(', ', $logged).' (still km3 customer - from Drebach)');
        }

        $this->_log_unassigned_calls($unassigned);

        // warning when there are approx 5 times more customers then calls
        if ($calls && (count($phonenumbers_db) > 5 * count($calls))) {
            Log::warning('billing', 'Very little data in purtel call data record file ('.count($csv).' records). Possibly missing data!');
        }

        return $calls;
    }

    /**
     * Get Array of formerly used prefixes of external customer numbers on provider side (EnviaTel, PurTel, ...)
     * These prefixes have to be removed to establish the connection to the customers in NMSPrime
     *
     * @return array    default is an empty array (file does not exist)
     */
    private static function getCdrNrPrefixReplacements()
    {
        $relFilePath = 'config/billingbase/cdr-nr-prefix-replacements';

        if (! Storage::exists($relFilePath)) {
            return [];
        }

        $cdr_nr_prefix_replacements = explode(PHP_EOL, Storage::get($relFilePath));

        array_filter($cdr_nr_prefix_replacements, function ($value) {
            return $value !== '';
        });

        return $cdr_nr_prefix_replacements;
    }

    private static function _get_customer_nrs()
    {
        $customer_nrs = [];

        $numbers = \DB::table('contract')->select(['id', 'number'])->whereNull('deleted_at')->get();

        foreach ($numbers as $num) {
            $customer_nrs[] = $num->id;
            $customer_nrs[] = $num->number;
        }

        return $customer_nrs;
    }

    /**
     * Get list of all phonenumbers of all contracts belonging to a specific registrar
     *
     * @return array
     */
    private function _get_phonenumbers($registrar, $withEmptyRegistrar = true)
    {
        $cdr_first_day_of_month = date('Y-m-01', strtotime('first day of -'.(1 + $this->conf->cdr_offset).' month'));

        if ($withEmptyRegistrar) {
            $whereCondition = function ($query) use ($registrar) {
                $query
                ->where('sipdomain', 'like', "%$registrar%")
                ->orWhereNull('sipdomain')
                ->orWhere('sipdomain', '=', '');
            };
        } else {
            $whereCondition = function ($query) use ($registrar) {
                $query
                ->where('sipdomain', 'like', "%$registrar%");
            };
        }

        return \DB::table('phonenumber as p')
            ->join('mta', 'p.mta_id', '=', 'mta.id')
            ->join('modem', 'modem.id', '=', 'mta.modem_id')
            ->join('contract as c', 'c.id', '=', 'modem.contract_id')
            ->where($whereCondition)
            ->where(function ($query) use ($cdr_first_day_of_month) {
                $query
                ->whereNull('p.deleted_at')
                ->orWhere('p.deleted_at', '>=', $cdr_first_day_of_month);
            })
            ->select('modem.contract_id', 'c.number as contractnr', 'c.create_invoice', 'p.*')
            ->orderBy('p.deleted_at', 'asc')->orderBy('p.created_at', 'desc')
            // ->limit(50)
            ->get();
    }

    /**
     * Log all phonenumbers that actually do not belong to the identifier/contract number labeled in CSV
     *
     * @param array      [customer_id][phonenr] => true
     */
    private function _log_phonenumber_mismatches($mismatches, $provider)
    {
        foreach ($mismatches as $contract_nr => $pns) {
            foreach ($pns as $p => $type) {

                // NOTE: type actually can be missing or mismatch
                Log::warning('billing', trans("messages.phonenumber_$type", [
                    'provider' => $provider,
                    'contractnr' => $contract_nr,
                    'phonenr' => $p,
                    ]));
            }
        }
    }

    /**
     * Log all cumulated prices of calls from specific phonenumbers that could not be assigned to any contract
     *
     * @param array 	 [customer_id][phonenr] => [count, price]
     */
    private function _log_unassigned_calls($unassigned)
    {
        foreach ($unassigned as $customer_nr => $pns) {
            foreach ($pns as $p => $arr) {
                $price = \App::getLocale() == 'de' ? number_format($arr['price'], 2, ',', '.') : number_format($arr['price'], 2, '.', ',');

                Log::warning('billing', trans('messages.cdr_discarded_calls', [
                    'contractnr' => $customer_nr,
                    'count' => $arr['count'],
                    'phonenr' => $p,
                    'price' => $price,
                    'currency' => $this->conf->currency,
                    ]));
            }
        }
    }

    /**
     * Instantiates an Array of all necessary date formats needed during execution of this Command
     *
     * Also needed in Item::calculate_price_and_span and in DashboardController!!
     *
     * TODO: Maybe implement this as service Provider or just dont use it
     */
    public static function create_dates_array()
    {
        return [

            'today' 		=> date('Y-m-d'),
            'm' 			=> date('m'),
            'Y' 			=> date('Y', strtotime('first day of last month')),

            'this_m'	 	=> date('Y-m'),
            'thism_01'		=> date('Y-m-01'),
            'thism_bill'	=> date('m/Y'),

            'lastm'			=> date('m', strtotime('first day of last month')),			// written this way because of known bug ("-1 month" or "last month" is erroneous)
            'lastm_01' 		=> date('Y-m-01', strtotime('first day of last month')),
            'lastm_bill'	=> date('m/Y', strtotime('first day of last month')),
            'lastm_Y'		=> date('Y-m', strtotime('first day of last month')),		// strtotime(first day of last month) is integer with actual timestamp!

            'nextm_01' 		=> date('Y-m-01', strtotime('+1 month')),

            'null' 			=> '0000-00-00',
            'm_in_sec' 		=> 60 * 60 * 24 * 30,						// month in seconds
        ];
    }

    /**
     * Get the console command arguments / options
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['sepaaccount_id', InputArgument::OPTIONAL, 'SEPA-Account-ID: Run command for Specific account'],
        ];
    }

    protected function getOptions()
    {
        return [
            // array('debug', null, InputOption::VALUE_OPTIONAL, 'Print Debug Output to Commandline (1 - Yes, 0 - No (Default))', 0),
        ];
    }
}
