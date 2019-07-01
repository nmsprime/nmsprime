<?php

namespace Modules\BillingBase\Entities;

use DB;
use Storage;
use ChannelLog;
use Modules\ProvBase\Entities\Contract;
use App\Http\Controllers\BaseViewController;
use Illuminate\Database\Eloquent\Collection;
use Modules\BillingBase\Jobs\SettlementRunJob;
use Symfony\Component\Console\Helper\ProgressBar;
use Modules\BillingBase\Providers\SettlementRunData;

class SettlementRun extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'settlementrun';

    // don't try to add these Input fields to Database of this model
    public $guarded = ['rerun', 'sepaaccount', 'fullrun', 'banking_file_upload'];

    // Add your validation rules here
    public static function rules($id = null)
    {
        return [
            // see SettlementRunController@prepare_rules
        ];
    }

    /**
     * Init Observer
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new SettlementRunObserver);
    }

    /**
     * View related stuff
     */

    // Name of View
    public static function view_headline()
    {
        return 'Settlement Run';
    }

    public static function view_icon()
    {
        return '<i class="fa fa-file-pdf-o"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        $bsclass = $this->get_bsclass();
        $day = (isset($this->created_at)) ? $this->created_at : '';

        return ['table' 		=> $this->table,
                'index_header' 	=> [$this->table.'.year',
                                    $this->table.'.month',
                                    $this->table.'.created_at',
                                    'verified', ],
                'header' 		=>  $this->year.' - '.$this->month.' - '.$day,
                'bsclass' 		=> $bsclass,
                'order_by' 		=> ['0' => 'desc'],
                'edit' 			=> ['verified' => 'run_verified',
                                    'created_at' => 'created_at_toDateString', ],
                ];
    }

    public function get_bsclass()
    {
        return $this->verified ? 'info' : 'warning';
    }

    public function run_verified()
    {
        return  $this->verified ? 'Yes' : 'No';
    }

    public function set_index_delete()
    {
        if ($this->verified) {
            $this->index_delete_disabled = true;
        }

        return $this->index_delete_disabled;
    }

    public function created_at_toDateString()
    {
        return $this->created_at->toDateString();
    }

    public function view_has_many()
    {
        $ret['Edit']['Files']['view']['view'] = 'billingbase::SettlementRun.files';
        $ret['Edit']['Files']['view']['vars']['files'] = $this->accounting_files();

        // option to rerun settlementrun only for a specific SepaAccount
        if (SepaAccount::count() > 1) {
            $accs1 = [0 => trans('messages.ALL')];
            $accs2 = $this->html_list(SepaAccount::orderBy('id')->get(), ['id', 'name'], false, ': ');
            $accs = $accs1 + $accs2;
            $ret['Edit']['Files']['view']['vars']['sepaaccs'] = $accs;
        }

        // NOTE: logs are fetched in SettlementRunController::edit
        $ret['Edit']['Logs']['view']['view'] = 'billingbase::SettlementRun.logs';
        $ret['Edit']['Logs']['view']['vars']['md_size'] = 12;

        return $ret;
    }

    /**
     * Mutator function to get accounting storage directory path via: model->directory
     * (so calling it in e.g. constructor is superfluous, used in e.g. ZipCommand)
     *
     * @return string   SettlementRun absolute directory path
     */
    public function getDirectoryAttribute()
    {
        return storage_path('app/'.$this->getRelativeDirectoryAttribute());
    }

    /**
     * Mutator function to get accounting storage directory path via: model->relativeDirectory
     *
     * @return string   SettlementRun relative directory path
     */
    public function getRelativeDirectoryAttribute()
    {
        return 'data/billingbase/accounting/'.$this->year.'-'.str_pad($this->month, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Relations
     */
    public function invoices()
    {
        return $this->hasMany('Modules\BillingBase\Entities\Invoice');
    }

    /**
     * Return all Billing Files the corresponding directory contains
     *
     * @return array 	containing all files ordered for view
     */
    public function accounting_files()
    {
        if (! is_dir($this->directory)) {
            return [];
        }

        $arr = [];

        $files = \File::allFiles($this->directory);

        //order files
        foreach ($files as $file) {
            $sepaacc = $file->getRelativePath() ?: BaseViewController::translate_label('General');
            $arr[$sepaacc][] = $file;
        }

        return $arr;
    }

    public function parseBankingFile($mt940)
    {
        $parser = new \Kingsquare\Parser\Banking\Mt940();
        $transactionParser = new \Modules\Dunning\Entities\TransactionParser;

        $statements = $parser->parse($mt940);
        foreach ($statements as $statement) {
            foreach ($statement->getTransactions() as $transaction) {
                $debt = $transactionParser->parse($transaction);

                // only for analysis during development!
                // $transactions[$transaction->getDebitCredit()][] = ['price' => $transaction->getPrice(), 'description' => explode('?', $transaction->getDescription())];

                if (! $debt) {
                    continue;
                }

                $debt->save();
            }
        }
        // d($transactions, $statements, str_replace(':61:', "\r\n---------------\r\n:61:", $mt940));
    }

    //////////////////////////////////////////////////////
    ////////////// SettlementRun execution ///////////////
    //////////////////////////////////////////////////////

    /**
     * Execute SettlementRun for specific SepaAccount
     *
     * @var bool
     */
    public $specific = false;

    /**
     * Execute the SettlementRun
     *
     * Creates invoices, SEPA xmls, booking & accounting record files
     *
     * @param obj   SepaAccount the run shall be executed for - null for all accounts
     */
    public function execute($sepaacc = null, $output = null)
    {
        ChannelLog::debug('billing', '########  Start SettlementRun  ########');
        $this->output = $output;

        // Set execution timestamp to always show log entries on SettlementRun edit page
        self::where('id', $this->id)->update(['executed_at' => \Carbon\Carbon::now()]);

        $accs = $this->getSepaAccounts($sepaacc);

        $this->init($sepaacc);

        $this->user_output('parseCdr', 0);
        $cdrs = SettlementRunData::getCdrs();
        // $cdrs = [[]];

        // TODO: use load_salesman_from_contracts() in future ?
        $this->user_output('loadData', 0);
        $salesmen = Salesman::all();
        $contracts = self::getContracts($sepaacc ?: null);

        // show progress bar if not called silently via queues (would throw exception otherwise)
        $num = count($contracts);
        if ($this->output) {
            echo "Create Invoices:\n";
            // $bar = new ProgressBar($this->output, $num);
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
                self::push_state((int) $i / $num * 100, trans('billingbase::messages.settlementrun.state.createInvoices'));
                // echo ($i/$num [$c->id][".(memory_get_usage()/1000000)."]\r";
            }

            // Skip invalid contracts
            if (! $c->check_validity('yearly') && ! (isset($cdrs[$c->id]) || isset($cdrs[$c->number]))) {
                ChannelLog::debug('billing', "Contract $c->number [$c->id] is invalid for current year");
                continue;
            }

            /*
             * Collect item specific data for all billing files
             */
            foreach ($c->items as $item) {
                // skip items that are related to a deleted product
                if (! isset($item->product)) {
                    ChannelLog::error('billing', "Product of $item->accounting_text was deleted", [$c->id]);
                    continue;
                }

                // skip if price is 0 (or item dates are invalid)
                if (! ($ret = $item->calculate_price_and_span())) {
                    ChannelLog::debug('billing', 'Item '.$item->product->name.' isn\'t charged this month', [$c->id]);
                    continue;
                }

                // get account via costcenter
                $costcenter = $item->get_costcenter();
                $acc = $accs->find($costcenter->sepaaccount_id);

                // If SR runs for specific SA skip item if it does not belong to that SA (SepaAccount)
                if (! $acc) {
                    continue;
                }

                // increase invoice nr of sepa account
                if (! isset($c->charge[$acc->id])) {
                    $c->charge[$acc->id] = ['net' => 0, 'tax' => 0];
                    $acc->invoice_nr += 1;
                }

                // increase charge for account by price, calculate tax
                $c->charge[$acc->id]['net'] += $item->charge;
                $c->charge[$acc->id]['tax'] += $item->product->tax ? $item->charge * SettlementRunData::getConf('tax') / 100 : 0;

                $item->charge = round($item->charge, 2);

                // save to accounting table (as backup for future) - NOTE: invoice nr counters are set initially from that table
                $rec = new AccountingRecord;
                $rec->store_item($item, $acc);

                // add item to accounting records of account, invoice and salesman
                $acc->add_accounting_record($item);
                $acc->add_invoice_item($item, $this->id);
                if ($c->salesman_id) {
                    $salesmen->find($c->salesman_id)->add_item($c, $item, $acc->id);
                }
            } // end of item loop

            $this->addCdrs($accs, $c, $cdrs);

            /*
             * Add contract specific data for accounting files
             */
            // get actual globally valid sepa mandate (valid for all CostCenters/SepaAccounts)
            $mandate_global = $c->get_valid_mandate();

            foreach ($c->charge as $acc_id => $value) {
                $value['net'] = round($value['net'], 2);
                $value['tax'] = round($value['tax'], 2);
                $value['tot'] = $value['net'] + $value['tax'];

                $acc = $accs->find($acc_id);

                $mandate_specific = $c->get_valid_mandate('now', $acc->id);
                $mandate = $mandate_specific ?: $mandate_global;

                $rcd = $this->rcd($c);

                $acc->add_booking_record($c, $mandate, $value, $rcd);
                $acc->set_invoice_data($c, $mandate, $value, $rcd);

                // create invoice pdf already - this task is the most timeconsuming and therefore threaded!
                $acc->invoices[$c->id]->make_invoice();

                // Add debt (overdue/outstanding payment)
                $this->add_debt($c, $value['tot'], $acc->invoices[$c->id]);

                if ($mandate) {
                    $mandate->setRelation('contract', $c);
                    $acc->add_sepa_transfer($mandate, $value['tot'], $rcd);
                    $this->add_debt($c, (-1) * $value['tot'], $acc->invoices[$c->id], $rcd);
                } else {
                    ChannelLog::debug('billing', "Contract $c->number [$c->id] has no valid sepa mandate for SepaAccount $acc->name [$acc->id]");
                }

                unset($acc->invoices[$c->id]);
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
        Invoice::remove_templatex_files($sepaacc);
        $this->_make_billing_files($accs, $salesmen);

        if ($this->output) {
            Storage::delete('tmp/accCmdStatus');
        } else {
            self::push_state(100, trans('billingbase::messages.settlementrun.state.finish'));
        }
    }

    /**
     * (1) Set Language for Billing
     * (2) Clear/Create (Prepare) Directories
     * (3) Remove already created Invoices
     */
    private function init($sepaacc)
    {
        \App::setLocale(SettlementRunData::getConf('userlang'));

        if ($sepaacc) {
            $this->specific = true;
        }

        // Create directory structure and remove old invoices
        if (is_dir(self::get_absolute_accounting_dir_path())) {
            $this->user_output('clean', 0);
            $this->directory_cleanup($sepaacc);
        } else {
            mkdir(self::get_absolute_accounting_dir_path(), 0700, true);
        }

        // TODO: Reset mandate state on rerun if changed

        $this->resetItemPayedMonth($sepaacc);
    }

    /**
     * This function removes all "old" files and DB Entries created by the previous called accounting Command
     * This is necessary because otherwise e.g. after deleting contracts the invoice would be kept and is still
     * available in customer control center
     * Used in: SettlementRunObserver@deleted, SettlementRun::execute()
     *
     * USE WITH CARE!
     *
     * @param string    dir                 Accounting Record Files Directory relative to storage/app/
     * @param object    sepaacc
     */
    public function directory_cleanup($sepaacc = null)
    {
        $dir = $this->relativeDirectory;
        $start = $this->created_at->toDateString();
        $end = $this->created_at->addMonth()->toDateString();

        // remove all entries of this month permanently (if already created)
        $query = AccountingRecord::whereBetween('created_at', [$start, $end]);
        if ($sepaacc) {
            $query = $query->where('sepaaccount_id', '=', $sepaacc->id);
        }

        $ret = $query->forceDelete();
        if ($ret) {
            ChannelLog::debug('billing', 'SettlementRun was already executed this month - accounting table will be recreated now! (for this month)');
        }

        // Delete all invoices
        $logmsg = 'Remove all already created Invoices and Accounting Files for this month';
        ChannelLog::debug('billing', $logmsg);
        echo "$logmsg\n";

        $this->delete_current_invoices($sepaacc);

        $cdr_filepaths = CdrGetter::get_cdr_pathnames();
        $salesman_csv_path = Salesman::get_storage_rel_filename();

        // everything in accounting directory
        if (! $sepaacc) {
            foreach (glob(storage_path("app/$dir/*")) as $f) {
                // keep cdr
                if (in_array($f, $cdr_filepaths)) {
                    continue;
                }

                if (is_file($f)) {
                    unlink($f);
                }
            }

            foreach (Storage::directories($dir) as $d) {
                Storage::deleteDirectory($d);
            }
        }
        // SepaAccount specific stuff
        else {
            // delete ZIP
            Storage::delete("$dir/".date('Y-m', strtotime('first day of last month')).'.zip');

            // delete concatenated Invoice pdf
            Storage::delete("$dir/".BaseViewController::translate_label('Invoices').'.pdf');

            Salesman::remove_account_specific_entries_from_csv($sepaacc->id);

            // delete account specific dir
            $dir = $sepaacc->get_relative_accounting_dir_path();
            foreach (Storage::files($dir) as $f) {
                Storage::delete($f);
            }

            Storage::deleteDirectory($dir);
        }
    }

    private function resetItemPayedMonth($sepaacc)
    {
        // TODO: Remove when cronjob is tested
        if (SettlementRunData::getDate('lastm') == '01') {
            // Senseless where statement is necessary because update can not be called directly
            Item::where('payed_month', '!=', '0')->update(['payed_month' => '0']);
        }

        // Update SepaAccount specific items in case item was charged in first run, but sth changed during
        // time to second run and item must be charged later
        if ($sepaacc) {
            $query = self::getSepaAccSpecificContractsBaseQuery($sepaacc);

            $query->where('p.billing_cycle', 'Yearly')->toBase()->update(['i.payed_month' => '0']);
        } else {
            Item::where('payed_month', SettlementRunData::getDate('lastm'))->update(['payed_month' => '0']);
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
     * Get initialized SepaAccounts for SettlementRun
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    private function getSepaAccounts($account = null)
    {
        if ($account) {
            $accs = new Collection([0 => $account]);

            ChannelLog::debug('billing', "Execute settlementrun for SepaAccount $account->name (ID: $account->id)");
        } else {
            $accs = SepaAccount::all();

            if (! $accs) {
                ChannelLog::error('billing', 'There are no Sepa Accounts to create Billing Files for - Stopping here!');
                throw new \Exception('There are no Sepa Accounts to create Billing Files for');
            }

            ChannelLog::debug('billing', 'Execute settlementrun for all SepaAccounts');
        }

        foreach ($accs as $acc) {
            $acc->settlementrun_init();
        }

        return $accs;
    }

    /**
     * Deletes currently created invoices (created in actual month)
     * Used to delete invoices created by previous settlement run (SR) in current month - executed in SettlementRun::execute()
     * is used to remove files before settlement run is repeatedly created (SettlementRun::execute() executed again)
     * NOTE: Use Carefully!!
     *
     * @param obj   Delete only invoices related to specific SepaAccount, 0 - delete all invoices of current SR
     */
    public function delete_current_invoices($sepaaccount)
    {
        $query = Invoice::whereBetween('created_at', [date('Y-m-01 00:00:00'), date('Y-m-01 00:00:00', strtotime('next month'))]);
        if ($sepaaccount) {
            $query = $query->where('sepaaccount_id', '=', $sepaaccount->id);
        }

        $invoices = $query->get();

        // Delete PDFs
        foreach ($invoices as $invoice) {
            $filepath = $invoice->get_invoice_dir_path().$invoice->filename;
            if (is_file($filepath)) {
                unlink($filepath);
            }

            if (\Module::collections()->has('Dunning')) {
                $invoice->debts()->forceDelete();
            }
        }

        // Delete DB Entries - Note: keep this order
        $query->forceDelete();
    }

    /**
     * Get all Contracts an invoice shall be created for
     *
     * NOTE: If SettlementRun is executed for a specific SepaAccount this function will only return the contracts
     *  that can have resulting charges for that account
     *
     * @param int
     */
    public static function getContracts($sepaaccount)
    {
        if ($sepaaccount) {
            $query = self::getSepaAccSpecificContractsQuery($sepaaccount);

            self::logSepaAccSpecificDiscardedContracts(clone $query);

            return self::getSepaAccSpecificContracts($query);
        }

        self::logAllDiscardedContracts();

        return self::getAllContracts();
    }

    private static function getSepaAccSpecificContractsQuery($sepaaccount)
    {
        $query = self::getSepaAccSpecificContractsBaseQuery($sepaaccount);

        return $query->orderBy('number')->distinct();
    }

    private static function getSepaAccSpecificContractsBaseQuery($sepaaccount)
    {
        return Contract::leftJoin('item as i', 'contract.id', '=', 'i.contract_id')
            ->leftJoin('costcenter as ccc', 'contract.costcenter_id', '=', 'ccc.id')
            ->leftJoin('costcenter as cci', 'i.costcenter_id', '=', 'cci.id')
            ->leftJoin('product as p', 'i.product_id', '=', 'p.id')
            ->leftJoin('costcenter as ccp', 'p.costcenter_id', '=', 'ccp.id')
            ->where(whereLaterOrEqual('contract.contract_end', date('Y-m-d', strtotime('last day of nov last year'))))
            ->where('i.valid_from_fixed', 1)
            ->where(function ($query) use ($sepaaccount) {
                $query
                ->where('ccc.sepaaccount_id', '=', $sepaaccount->id)
                ->orWhere('ccp.sepaaccount_id', '=', $sepaaccount->id)
                ->orWhere('cci.sepaaccount_id', '=', $sepaaccount->id);
            });
    }

    private static function logSepaAccSpecificDiscardedContracts($query)
    {
        // Log all contracts where invoice creation is deactivated
        $deactivated = $query
            ->where('create_invoice', '=', 0)
            ->select('contract.number')
            ->pluck('number')->all();

        if ($deactivated) {
            ChannelLog::info('billing', trans('messages.accCmd_invoice_creation_deactivated', ['contractnrs' => implode(',', $deactivated)]));
        }

        $errors = $query
            ->where('contract.costcenter_id', 0)
            ->select('contract.number')
            ->pluck('number')->all();

        if ($errors) {
            ChannelLog::error('billing', trans('messages.accCmd_error_noCC', ['numbers' => implode(',', $errors)]));
        }
    }

    private static function getSepaAccSpecificContracts($query)
    {
        return $query
            ->where('create_invoice', '!=', 0)
            ->select('contract.*')
            ->with('items.product', 'costcenter')
            ->get();
    }

    private static function logAllDiscardedContracts()
    {
        // Log all discarded contracts of any reason
        $query = Contract::where('create_invoice', '=', 0)->orderBy('number');

        // where invoice creation is deactivated
        $deactivated = $query->pluck('number')->all();
        if ($deactivated) {
            ChannelLog::info('billing', trans('messages.accCmd_invoice_creation_deactivated', ['contractnrs' => implode(',', $deactivated)]));
        }

        $errors = $query->where('costcenter_id', 0)->pluck('number')->all();
        if ($errors) {
            ChannelLog::error('billing', trans('messages.accCmd_error_noCC', ['numbers' => implode(',', $errors)]));
        }
    }

    /**
     * Get all relevant contracts for the SettlementRun - with eager loading
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    private static function getAllContracts()
    {
        return Contract::orderBy('number')
            ->with('items.product', 'costcenter')
            ->where('create_invoice', '!=', 0)
            ->where('costcenter_id', '!=', 0)
            // ->where(function ($query) {
            //     $query
            //     ->whereBetween('number', [10010, 10090])
            //     ->orWhereIn('id', [502832])
            //     ->orWhereIn('number', [10106]);
            // })
            // TODO: make time we have to look back dependent of CDR offset in BillingBase config
            ->where(whereLaterOrEqual('contract_end', date('Y-m-d', strtotime('last day of sep last year'))))
            ->get();
    }

    /**
     * Add Call Data Records
     */
    private function addCdrs($sepaaccs, $c, $cdrs)
    {
        // Dont add CDRs if this command is not destined to the corresponding SepaAcc
        $acc = $sepaaccs->find($c->costcenter->sepaaccount_id);
        if (! $acc) {
            return;
        }

        $id = 0;
        if (isset($cdrs[$c->id])) {
            $id = $c->id;
        } elseif (isset($cdrs[$c->number])) {
            $id = $c->number;
        }

        if (! $id) {
            return;
        }

        // calculate charge and count
        $charge = $calls = 0;
        foreach ($cdrs[$id] as $entry) {
            $charge += $entry['price'];
            $calls++;
        }

        // increase charge for booking record
        // Keep this order in case we need to increment the invoice nr if only cdrs are charged for this contract
        if (! isset($c->charge[$acc->id])) {
            // this case should only happen when contract/voip tarif ended and deferred CDRs are calculated
            ChannelLog::notice('billing', trans('messages.accCmd_notice_CDR', ['contract_nr' => $c->number, 'contract_id' => $c->id]));
            $c->charge[$acc->id] = ['net' => 0, 'tax' => 0];
            $acc->invoice_nr += 1;
        }

        $c->charge[$acc->id]['net'] += $charge;
        $c->charge[$acc->id]['tax'] += $charge * SettlementRunData::getConf('tax') / 100;

        // accounting record
        $rec = new AccountingRecord;
        $rec->add_cdr($c, $acc, $charge, $calls);
        $acc->add_cdr_accounting_record($c, $charge, $calls);

        // invoice
        $acc->add_invoice_cdr($c, $cdrs[$id], $this->id);
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
        $zipper = new SettlementRunZipper($this, $this->output);
        $zipper->fire($this->specific ? $sepaaccs->first() : null);
    }

    /**
     * Write Status to temporary file as buffer for settlement run status bar in GUI
     *
     * @param int       Value of progress bar
     * @param string    message string
     */
    public static function push_state($value, $msg)
    {
        $arr = [
            'message' => $msg,
            'value'   => round($value),
            ];

        Storage::put('tmp/accCmdStatus', json_encode($arr));
    }

    /**
     * Push message and state either to commandline or to state file for GUI
     *
     * @param string    Key in billingbase::messages language file (modules/BillingBase/Resources/lang/{de,en,...}/messages.php)
     * @param float     state
     */
    public function user_output($key, $state)
    {
        $msg = trans("billingbase::messages.settlementrun.state.$key");

        if ($this->output) {
            echo "$msg\n";
        } else {
            self::push_state($state, $msg);
        }
    }

    private function add_debt($contract, $amount, $invoice, $rcd = '')
    {
        if (! \Module::collections()->has('Dunning')) {
            return;
        }

        \Modules\Dunning\Entities\Debt::create([
            'contract_id' => $contract->id,
            'invoice_id' => $invoice->id,
            // TODO: Make date configurable? (Global conf: number for specific day, or d for actual day or rcd for rcd)
            'date' => $rcd ?: date('Y-m-d', strtotime('last day of last month')),
            'amount' => $amount,
            ]);
    }

    /**
     * Get requested collection date / date of value for contract
     * This is the date when the bank performs the booking of the customers debit
     *
     * @return string   date
     */
    private function rcd($contract)
    {
        $rcdDefault = SettlementRunData::getConf('rcd');
        $rcd = date('Y-m-');

        if ($contract->value_date) {
            $rcd .= $contract->value_date;
        } elseif ($rcdDefault) {
            $rcd .= $rcdDefault;
        } else {
            $rcd = date('Y-m-d', strtotime('+1 day'));
        }

        return $rcd;
    }
}

class SettlementRunObserver
{
    public function creating($settlementrun)
    {
        // dont show every settlementrun that was created in one month
        $time = strtotime('first day of last month');
        SettlementRun::where('month', '=', date('m', $time))->where('year', '=', date('Y', $time))->delete();
        $settlementrun->fullrun = 1;
    }

    public function created($settlementrun)
    {
        if (! $settlementrun->observer_enabled) {
            return;
        }

        // NOTE: Make sure that we use Database Queue Driver - See .env!
        // \Artisan::call('billing:accounting', ['--debug' => 1]);
        \Session::put('job_id', \Queue::push(new SettlementRunJob($settlementrun)));
    }

    public function updated($settlementrun)
    {
        if (\Input::has('rerun')) {
            // Make sure that settlement run is queued only once
            $queued = DB::table('jobs')->where('payload', 'like', '%SettlementRunJob%')->count();
            if (! $queued) {
                $acc = \Input::get('sepaaccount') ? SepaAccount::find(\Input::get('sepaaccount')) : null;
                \Session::put('job_id', \Queue::push(new SettlementRunJob($settlementrun, $acc)));
            }
        }

        // TODO: implement this as command and queue this?
        if (\Input::hasFile('banking_file_upload')) {
            SettlementRun::where('id', $settlementrun->id)->update(['uploaded_at' => date('Y-m-d H:i:s')]);

            $mt940 = \Input::file('banking_file_upload');

            $mt940s[] = Storage::get('tmp/Erznet-sta.sta');
            $mt940s[] = Storage::get('tmp/Erznet-sta-2019-04-24.sta');
            $mt940s[] = Storage::get('tmp/Erznet-sta-2019-04-25.sta');

            foreach ($mt940s as $mt940) {
                $settlementrun->parseBankingFile($mt940);
            }
        }
    }
}
