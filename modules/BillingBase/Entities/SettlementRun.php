<?php

namespace Modules\BillingBase\Entities;

use DB;
use Module;
use Request;
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

    // don't try to add theseRequest fields to Database of this model
    public $guarded = ['rerun', 'sepaaccount', 'fullrun', 'banking_file_upload', 'voucher_nr'];

    // Add your validation rules here
    public static function rules($id = null)
    {
        // see SettlementRunController@prepare_rules

        if (Module::collections()->has('OverdueDebts') && config('overduedebts.debtMgmtType') == 'csv') {
            return [
                'banking_file_upload' => 'mimes:csv,txt',
            ];
        }

        return [];
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
        $time = $this->executed_at ?? '';

        return [
            'table' 		=> $this->table,
            'index_header' 	=> [$this->table.'.year', $this->table.'.month', $this->table.'.executed_at', 'verified'],
            'header' 		=> $this->year.' - '.$this->month.' - '.$time,
            'bsclass' 		=> $bsclass,
            'order_by' 		=> ['0' => 'desc'],
            'edit' 			=> ['verified' => 'run_verified'],
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
        return $this->hasMany(Invoice::class);
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

        try {
            // It is possible that permissions are not yet set correctly during settlement run
            // (e.g. a file is currently written by root user and the user tries to open the edit page before owner was changed by the process)
            $files = \File::allFiles($this->directory);
        } catch (\Exception $e) {
            return $arr;
        }

        //order files
        foreach ($files as $file) {
            $sepaacc = $file->getRelativePath() ?: BaseViewController::translate_label('General');
            $arr[$sepaacc][] = $file;
        }

        return $arr;
    }

    //////////////////////////////////////////////////////
    ////////////// SettlementRun execution ///////////////
    //////////////////////////////////////////////////////

    /**
     * Execute SettlementRun for specific SepaAccount
     *
     * @var SepaAccount
     */
    public $specificSepaAcc = null;

    /**
     * Output Interface for console command
     *
     * @var Illuminate\Console\OutputStyle
     */
    public $output;

    /**
     * Execute the SettlementRun
     *
     * Creates invoices, SEPA xmls, booking & accounting record files
     *
     * @param  SepaAccount  $sepaacc  The SEPA account the run shall be executed for - null for all accounts
     * @param  Illuminate\Console\OutputStyle  $output  console output
     */
    public function execute($sepaacc = null, $output = null)
    {
        ChannelLog::debug('billing', '########  Start SettlementRun  ########');
        $this->output = $output;

        if ($sepaacc) {
            $this->specificSepaAcc = $sepaacc;
        }

        // Set execution timestamp to always show log entries on SettlementRun edit page
        self::where('id', $this->id)->update(['executed_at' => \Carbon\Carbon::now()]);

        $this->init();
        $this->getSepaAccounts();

        $this->user_output('parseCdr', 0);

        $cdrs = [[]];
        $cdrs = SettlementRunData::getCdrs();

        // TODO: use load_salesman_from_contracts() in future ?
        $this->user_output('loadData', 0);
        $salesmen = Salesman::all();
        $contracts = $this->getContracts();

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
                // echo round($i/$num *100, 1)."% [$c->id][".(memory_get_usage()/1000000)."]\r";
                $bar->advance();
            } elseif (! ($i % 10)) {
                self::push_state((int) $i / $num * 100, trans('billingbase::messages.settlementrun.state.createInvoices'));
            }

            // Skip invalid contracts
            if (! $c->isValid('yearly') && ! (isset($cdrs[$c->id]) || isset($cdrs[$c->number]))) {
                ChannelLog::debug('billing', "Contract $c->number [$c->id] is invalid for current year");
                continue;
            }

            /*
             * Collect item specific data for all billing files
             */
            foreach ($c->items as $item) {
                // skip items that are related to a deleted product
                if (! isset($item->product)) {
                    ChannelLog::error('billing', "Product of $item->accounting_text [$item->id] was deleted", [$c->id]);
                    continue;
                }

                // skip if price is 0 (or item dates are invalid)
                if (! ($ret = $item->calculate_price_and_span())) {
                    ChannelLog::debug('billing', 'Item '.$item->product->name.' isn\'t charged this month', [$c->id]);
                    continue;
                }

                // get account via costcenter
                $costcenter = $item->get_costcenter();
                $acc = $this->accs->find($costcenter->sepaaccount_id);

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
                $rec->store_item($item, $acc, $this->id);

                // add item to accounting records of account, invoice and salesman
                $acc->add_accounting_record($item);
                $acc->add_invoice_item($item, $this->id);
                if ($c->salesman_id) {
                    $salesmen->find($c->salesman_id)->add_item($c, $item, $acc->id);
                }
            } // end of item loop

            $this->addCdrs($c, $cdrs);

            /*
             * Add contract specific data for accounting files
             */
            // get actual globally valid sepa mandate (valid for all CostCenters/SepaAccounts)
            $mandate_global = $c->get_valid_mandate();

            foreach ($c->charge as $acc_id => $value) {
                $value['net'] = round($value['net'], 2);
                $value['tax'] = round($value['tax'], 2);
                $value['tot'] = $value['net'] + $value['tax'];

                $acc = $this->accs->find($acc_id);

                $mandate_specific = $c->get_valid_mandate('now', $acc->id);
                $mandate = $mandate_specific ?: $mandate_global;

                $rcd = $this->rcd($c);

                $acc->add_booking_record($c, $mandate, $value, $rcd);
                $acc->set_invoice_data($c, $mandate, $value, $rcd);

                // create invoice pdf already - this task is the most timeconsuming and therefore threaded!
                $acc->invoices[$c->id]->make_invoice();

                // Add debt - permit to search for available debt to clear if no valid mandate exists by setting parent_id to 0
                $parent_id = $this->add_debt($c, $value['tot'], $acc->invoices[$c->id], $rcd, $mandate ? null : 0);

                if ($mandate) {
                    $mandate->setRelation('contract', $c);
                    $acc->add_sepa_transfer($mandate, $value['tot'], $rcd);
                    $this->add_debt($c, (-1) * $value['tot'], $acc->invoices[$c->id], $rcd, $parent_id);
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
        $this->delete_current_invoices(true);
        $this->_make_billing_files($salesmen);

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
    private function init()
    {
        \App::setLocale(SettlementRunData::getConf('userlang'));

        // Create directory structure and remove old invoices
        if (is_dir(self::get_absolute_accounting_dir_path())) {
            $this->user_output('clean', 0);
            $this->directory_cleanup();
        } else {
            $dir = self::get_absolute_accounting_dir_path();

            mkdir($dir, 0700, true);
            system("chown -R apache $dir");
        }

        // TODO: Reset mandate state on rerun if changed

        $this->resetItemPayedMonth();
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
    public function directory_cleanup()
    {
        $dir = $this->relativeDirectory;
        $start = $this->created_at->toDateString();
        $end = $this->created_at->addMonth()->toDateString();

        // remove all entries of this month permanently (if already created)
        $query = AccountingRecord::whereBetween('created_at', [$start, $end]);
        if ($this->specificSepaAcc) {
            $query = $query->where('sepaaccount_id', '=', $this->specificSepaAcc->id);
        }

        $ret = $query->forceDelete();
        if ($ret) {
            ChannelLog::debug('billing', 'SettlementRun was already executed this month - accounting table will be recreated now! (for this month)');
        }

        // Delete all invoices
        $logmsg = 'Remove all already created Invoices and Accounting Files for this month';
        ChannelLog::debug('billing', $logmsg);
        echo "$logmsg\n";

        $this->delete_current_invoices();

        $cdr_filepaths = CdrGetter::get_cdr_pathnames();
        $salesman_csv_path = Salesman::get_storage_rel_filename();

        // everything in accounting directory
        if (! $this->specificSepaAcc) {
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

            Salesman::remove_account_specific_entries_from_csv($this->specificSepaAcc->id);

            // delete account specific dir
            $dir = $this->specificSepaAcc->get_relative_accounting_dir_path();
            foreach (Storage::files($dir) as $f) {
                Storage::delete($f);
            }

            Storage::deleteDirectory($dir);
        }
    }

    /**
     * Reset payed_month of appropriate items to zero
     */
    private function resetItemPayedMonth()
    {
        $lastMonth = intval(SettlementRunData::getDate('lastm'));

        // Reset SepaAccount specific items in case item was charged in first run, but sth changed during
        // time to second run and item must be charged later
        if ($this->specificSepaAcc) {
            $query = self::getSepaAccSpecificContractsBaseQuery();

            $query->where('p.billing_cycle', 'Yearly')->where('payed_month', $lastMonth)->toBase()->update(['i.payed_month' => '0']);
            ChannelLog::info('billing', "Reset payed_month flag of items related to SepaAccount {$this->specificSepaAcc->name} ({$this->specificSepaAcc->id}) (set to 0)");

            return;
        }

        // Reset all yearly charged items in january
        // NOTE keep the order of the if statements
        // TODO ?: Remove this part when cronjob is tested or better leave it here as cronjob could e.g. be interrupted or sth like that
        if ($lastMonth == 1) {
            // Senseless where statement is necessary because update can not be called directly
            Item::where('payed_month', '!=', '0')->update(['payed_month' => '0']);
            ChannelLog::info('billing', 'Reset all items payed_month flag (to 0)');

            return;
        }

        Item::where('payed_month', $lastMonth)->update(['payed_month' => '0']);
        ChannelLog::info('billing', "Reset items with payed_month flag of $lastMonth (to 0)");
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
    private function getSepaAccounts()
    {
        if ($this->specificSepaAcc) {
            $accs = new Collection([0 => $this->specificSepaAcc]);

            ChannelLog::debug('billing', "Execute settlementrun for SepaAccount {$this->specificSepaAcc->name} (ID: {$this->specificSepaAcc->id})");
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

        return $this->accs = $accs;
    }

    /**
     * Remove created invoices of current month/settlement run (PDF files and DB entries)
     *
     *  Used to clean up directory and DB on repeated run of SettlementRun::execute()
     *
     * @param  bool  $tempFiles  Delete only temporary LaTeX files created during SettlementRun and check if Invoices where created successfully
     */
    public function delete_current_invoices($tempFiles = false)
    {
        $query = \DB::table('invoice')->whereBetween('created_at', [date('Y-m-01'), date('Y-m-01', strtotime('next month'))]);
        if ($this->specificSepaAcc) {
            $query = $query->where('sepaaccount_id', '=', $this->specificSepaAcc->id);
        }

        $invoices = $query->get();

        foreach ($invoices as $invoice) {
            // $filepath = $invoice->get_invoice_dir_path().$invoice->filename;
            $filepath = Invoice::getFilePathFromData($invoice);

            if (is_file($filepath)) {
                if ($tempFiles) {
                    // Delete LaTex files
                    $fn = str_replace('.pdf', '', $filepath);
                    unlink($fn);
                    unlink($fn.'.aux');
                    unlink($fn.'.log');
                } else {
                    // Delete PDFs
                    unlink($filepath);
                }
            } elseif ($tempFiles) {
                // Error on failed PDF creation - possible errors: syntax/filename/...
                ChannelLog::error('billing', 'pdflatex: Error creating Invoice PDF '.$filepath);
            }

            // Delete debts
            if (! $tempFiles && Module::collections()->has('OverdueDebts')) {
                \Modules\OverdueDebts\Entities\Debt::where('invoice_id', $invoice->id)->forceDelete();
            }
        }

        // Delete DB Entries - Note: keep this order
        if (! $tempFiles) {
            $query->delete();
        }
    }

    /**
     * Get all Contracts an invoice shall be created for
     *
     * NOTE: If SettlementRun is executed for a specific SepaAccount this function will only return the contracts
     *  that can have resulting charges for that account
     */
    public function getContracts()
    {
        if ($this->specificSepaAcc) {
            $query = $this->getSepaAccSpecificContractsQuery();

            $this->logSepaAccSpecificDiscardedContracts(clone $query);

            return $this->getSepaAccSpecificContracts($query);
        }

        $this->logAllDiscardedContracts();

        return $this->getAllContracts();
    }

    private function getSepaAccSpecificContractsQuery()
    {
        $query = $this->getSepaAccSpecificContractsBaseQuery();

        return $query->orderBy('number')->distinct();
    }

    private function getSepaAccSpecificContractsBaseQuery()
    {
        $sepaaccount = $this->specificSepaAcc;

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

    private function logSepaAccSpecificDiscardedContracts($query)
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

    private function getSepaAccSpecificContracts($query)
    {
        return $query
            ->where('create_invoice', '!=', 0)
            ->select('contract.*')
            ->with('items.product', 'costcenter')
            ->get();
    }

    private function logAllDiscardedContracts()
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
    private function getAllContracts()
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
            // TODO: Change it in CostCenter set_index_delete as well
            ->where(whereLaterOrEqual('contract_end', date('Y-m-d', strtotime('last day of sep last year'))))
            ->get();
    }

    /**
     * Add Call Data Records
     */
    private function addCdrs($c, $cdrs)
    {
        // Dont add CDRs if this command is not destined to the corresponding SepaAcc
        $acc = $this->accs->find($c->costcenter->sepaaccount_id);
        if (! $acc) {
            return;
        }

        $id = 0;
        if (isset($cdrs[$c->id])) {
            $id = $c->id;
        } elseif (isset($cdrs[$c->number])) {
            $id = $c->number;
        }

        // No CDRs existing for this contract
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
        $rec->add_cdr($c, $acc, $charge, $calls, $this->id);
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
    private function _make_billing_files($salesmen)
    {
        foreach ($this->accs as $acc) {
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
        $zipper->fire($this->specificSepaAcc);
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

    /**
     * Add Debt (Outstanding/Overdue payment) for invoice
     */
    private function add_debt($contract, $amount, $invoice, $rcd, $parent_id = null)
    {
        if (! Module::collections()->has('OverdueDebts') || config('overduedebts.debtMgmtType') != 'sta') {
            return;
        }

        $debt = \Modules\OverdueDebts\Entities\Debt::create([
            'contract_id' => $contract->id,
            'invoice_id' => $invoice->id,
            'voucher_nr' => $invoice->data['invoice_nr'],
            // TODO: Make date configurable? (Global conf: number for specific day, or d for actual day or rcd for rcd)
            'date' => date('Y-m-d', strtotime('last day of last month')),
            'due_date' => $rcd ?: date('Y-m-d', strtotime('last day of last month')),
            'amount' => $amount,
            'parent_id' => $parent_id,
        ]);

        return $debt->id;
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
        \Session::put('srJobId', \Queue::push(new SettlementRunJob($settlementrun)));
    }

    public function updated($settlementrun)
    {
        if (Request::filled('rerun')) {
            // Make sure that settlement run is queued only once
            $queued = DB::table('jobs')->where('payload', 'like', '%SettlementRunJob%')->count();
            if (! $queued) {
                $acc = Request::get('sepaaccount') ? SepaAccount::find(Request::get('sepaaccount')) : null;
                \Session::put('srJobId', \Queue::push(new SettlementRunJob($settlementrun, $acc)));
            }
        }

        if (Request::hasFile('banking_file_upload')) {
            SettlementRun::where('id', $settlementrun->id)->update(['uploaded_at' => date('Y-m-d H:i:s')]);

            $dir = storage_path('app/tmp');
            $fn = config('overduedebts.debtMgmtType') == 'csv' ? 'uploadedOverdueDebts.csv' : 'mt940.sta';

            Request::file('banking_file_upload')->move($dir, $fn);

            if (config('overduedebts.debtMgmtType') == 'csv') {
                \Session::put('srJobId', \Queue::push(new \Modules\OverdueDebts\Jobs\DebtImportJob("$dir/$fn")));
            } else {
                \Session::put('srJobId', \Queue::push(new \Modules\OverdueDebts\Jobs\ParseMt940("$dir/$fn", Request::get('voucher_nr'))));
            }
        }
    }
}
