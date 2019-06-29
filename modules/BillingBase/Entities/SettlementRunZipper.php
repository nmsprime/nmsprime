<?php

namespace Modules\BillingBase\Entities;

use ChannelLog;
use App\Http\Controllers\BaseViewController;
use Illuminate\Database\Eloquent\Collection;

class SettlementRunZipper
{
    protected $settlementrun;

    protected $output;

    /**
     * @var Maximum number of files ghost script shall concatenate at once
     *
     * Take care: Ghost scripts argument length must not be exceeded when you change this number
     */
    private $split = 1000;

    public function __construct(SettlementRun $settlementrun, $output = null)
    {
        $conf = BillingBase::first();
        if ($conf) {
            \App::setLocale($conf->userlang);
        }

        $this->settlementrun = $settlementrun;
        $this->output = $output;
    }

    public function fire($sepaacc = null, $postalInvoices = false)
    {
        // set directory via mutator function
        $this->settlementrun->directory = $this->settlementrun->directory;
        $this->settlementrun->txt = $this->settlementrun->year.'-'.$this->settlementrun->month.' [ID: '.$this->settlementrun->id.']';

        // Only concatenate postal invoices if flag is set
        if ($postalInvoices) {
            $this->concatPostalInvoices();

            return;
        } else {
            ChannelLog::debug('billing', 'Zip accounting files for SettlementRun '.$this->settlementrun->txt);

            $sepaAccs = $this->getRelevantSepaAccounts($this->settlementrun, $sepaacc);

            if (! $sepaAccs) {
                $msg = 'No invoices found for SettlementRun '.$this->settlementrun->txt;
                \ChannelLog::error('billing', $msg);
                echo "$msg\n";

                return;
            }

            $this->concatenateInvoicesForSepaAccounts($sepaAccs);
            $this->zipDirectory();
        }
    }

    /**
     * TODO: merge with concatenateInvoicesForSepaAccounts()
     */
    private function concatPostalInvoices()
    {
        $prod_ids = Product::where('type', 'Postal')->pluck('id', 'id')->all();

        if (! $prod_ids) {
            ChannelLog::error('billing', 'Build postal invoices PDF failed: No product is of type Postal!');

            return;
        }

        SettlementRun::push_state(0, trans('billingbase::messages.settlementrun.state.getData'));

        $month = $this->settlementrun->year.'-'.str_pad($this->settlementrun->month, 2, '0', STR_PAD_LEFT);
        $start = date('Y-m-d', strtotime('first day of next month', strtotime($month)));
        $end = date('Y-m-d', strtotime('first day of this month', strtotime($month)));

        $invoices = Invoice::join('contract', 'contract.id', '=', 'invoice.contract_id')
            ->join('item', 'contract.id', '=', 'item.contract_id')
            ->where('invoice.settlementrun_id', $this->settlementrun->id)
            ->whereIn('item.product_id', $prod_ids)
            ->whereNull('item.deleted_at')
            ->where('item.valid_from', '<', $start)
            ->where(function ($query) use ($end) {
                $query
                ->where('item.valid_to', '>=', $end)
                ->orWhereNull('item.valid_to');
            })
            // ->where('contract.number', 35129)
            ->orderBy('contract.number')->orderBy('invoice.type')
            ->groupBy('invoice.id')
            ->select('invoice.*')
            ->get();

        $num = count($invoices);

        if (! $num) {
            $msg = trans('billingbase::messages.zip.noPostal');
            ChannelLog::info($msg);
            echo "$msg\n";

            return;
        }

        SettlementRun::push_state(0, trans('billingbase::messages.settlementrun.state.concatPostalInvoices'));
        echo "Concatenate $num postal invoices...";
        ChannelLog::debug('billing', "Concatenate $num postal invoices for SettlementRun ".$this->settlementrun->txt);

        $files = [];
        foreach ($invoices as $inv) {
            $files[] = $inv->get_invoice_dir_path().$inv->filename;
        }

        // build temporary PDFs if more than 1000 invoices
        $files = $this->_concat_split_pdfs($files);

        // wait for all background processes to be finished if some exist and get next invoices already
        if (count($invoices) > $this->split) {
            $this->_wait_for_background_processes($files, true);
            $files = array_keys($files);
        }

        SettlementRun::push_state(50, trans('billingbase::messages.settlementrun.state.concatPostalInvoices'));

        // Concat Invoices/temporary files to final target file
        $targetFilepath = $this->settlementrun->directory.'/'.trans('messages.postalInvoices').'.pdf';

        concat_pdfs($files, $targetFilepath, false);
        echo "\nNew file: $targetFilepath\n";
        SettlementRun::push_state(100, trans('billingbase::messages.settlementrun.state.concatPostalInvoices'));

        // delete temp files
        if (count($files) >= $this->split) {
            foreach ($files as $fn) {
                if (is_file($fn)) {
                    unlink($fn);
                }
            }
        }
    }

    /**
     * Concatenate Invoices of specific SettlementRun for specific SepaAccounts (performance optimized)
     *
     * Show actual (but imprecise) status
     */
    private function concatenateInvoicesForSepaAccounts($sepaAccs)
    {
        // variable initialization
        $num = ['total' => 0, 'current' => 0, 'sum' => 0, 'percentage' => 0];
        $invoices = $final_files = [];
        foreach ($sepaAccs as $acc) {
            $num['total'] += $acc->invoice_cnt <= $this->split ? $acc->invoice_cnt : 2 * $acc->invoice_cnt;
        }

        if ($this->output) {
            echo "Concatenate invoices\n";
            $this->bar = $this->output->createProgressBar(100);
            $this->bar->start();
        }
        SettlementRun::push_state(0, trans('billingbase::messages.settlementrun.state.concatInvoices'));

        foreach ($sepaAccs as $i => $sepaAcc) {
            $invoices = $invoices ?: $this->getRelevantInvoices($sepaAcc);

            if (! $invoices) {
                continue;
            }

            $files = [];
            foreach ($invoices as $inv) {
                $files[] = $inv->get_invoice_dir_path().$inv->filename;
            }

            // build temporary PDFs if more than 1000 invoices
            $files = $this->_concat_split_pdfs($files);

            $num['current'] = count($invoices);
            $invoices = [];

            // wait for all background processes to be finished if some exist and get next invoices already
            if ($num['current'] > $this->split) {
                if (isset($sepaAccs[$i + 1])) {
                    $invoices = $this->getRelevantInvoices($sepaAccs[$i + 1]);
                }

                $this->_wait_for_background_processes($files, $num);
                $files = array_keys($files);
            }

            // Concat Invoices/temporary files to final target file
            $fpath = $this->settlementrun->directory.'/'.sanitize_filename($sepaAcc->name);
            $fpath .= '/'.BaseViewController::translate_label('Invoices').'.pdf';

            $pid = concat_pdfs($files, $fpath, true);
            $final_files[$fpath] = $pid;

            // delete temp files
            if (count($files) >= $this->split) {
                foreach ($files as $fn) {
                    if (is_file($fn)) {
                        unlink($fn);
                    }
                }
            }

            // Output
            $num['sum'] += $num['current'];

            if ($num['sum'] == $num['total']) {
                $num['percentage'] = 100 - (100 - $num['percentage']) / 2;
            } else {
                $num['percentage'] = $num['sum'] / $num['total'] * 100;
            }

            SettlementRun::push_state($num['percentage'], trans('billingbase::messages.settlementrun.state.concatInvoices'));
            if ($this->output) {
                $this->bar->setProgress($num['percentage']);
            }
        }

        $this->_wait_for_background_processes($final_files, $num, true);

        SettlementRun::push_state(100, trans('billingbase::messages.settlementrun.state.concatInvoices'));
        if ($this->output) {
            $this->bar->finish();
        }

        foreach (array_keys($final_files) as $fpath) {
            echo "\nNew file: $fpath";
        }
    }

    /**
     * Get all SepaAccounts that have Invoices in the specified settlementrun
     *
     * Note: the number of invoices is appended to each SepaAccount
     *
     * @return Collection
     */
    private function getRelevantSepaAccounts($settlementrun, $sepaacc = null)
    {
        if ($sepaacc) {
            $sepaAcc_ids = $sepaacc->id;

            // Get invoice count
            $counts[$sepaAcc_ids] = Invoice::where('settlementrun_id', $this->settlementrun->id)->where('sepaaccount_id', $sepaAcc_ids)->count();
            $sepaAcc_ids = [$sepaAcc_ids];

            $sepaAccs = new Collection([$sepaacc]);
        } else {
            $sepaAccs = Invoice::where('settlementrun_id', $this->settlementrun->id)
                ->groupBy('sepaaccount_id')
                ->select('sepaaccount_id', \DB::raw('count(*) as invoice_cnt'))
                ->get();

            foreach ($sepaAccs as $acc) {
                $counts[$acc->sepaaccount_id] = $acc->invoice_cnt;
            }

            $sepaAcc_ids = $sepaAccs->pluck('sepaaccount_id')->all();

            $sepaAccs = SepaAccount::whereIn('id', $sepaAcc_ids)->get();
        }

        // append invoice_cnt
        foreach ($sepaAccs as $acc) {
            $acc->invoice_cnt = $counts[$acc->id];
        }

        return $sepaAccs;
    }

    private function getRelevantInvoices($sepaAcc)
    {
        return Invoice::where('settlementrun_id', $this->settlementrun->id)->where('sepaaccount_id', $sepaAcc->id)->get();
    }

    private function zipDirectory()
    {
        $filename = $this->settlementrun->year.'-'.str_pad($this->settlementrun->month, 2, '0', STR_PAD_LEFT).'.zip';
        chdir($this->settlementrun->directory);

        ChannelLog::debug('billing', "ZIP Files to $filename");
        echo "\nZIP accounting files and directories...\n";
        SettlementRun::push_state(100, trans('billingbase::messages.settlementrun.state.zip'));

        // suppress output of zip command
        ob_start();
        system("zip -r $filename *");
        ob_end_clean();

        echo 'New file: '.$this->settlementrun->directory."/$filename\n";
    }

    /**
     * Split the number of Invoices to defined number (see global variable) and concat them to temporary files
     *
     * Note: This reduces performance dramatically, but is necessary to make sure the order of invoices
     * is kept and the argument length limit for ghost script is not exceeded
     *
     * @param array     invoice files
     */
    private function _concat_split_pdfs($files)
    {
        static $count = 0;

        if (count($files) < $this->split) {
            return $files;
        }

        $arr = array_chunk($files, $this->split);

        // recursive splitting - TODO: to be tested!
        if (count($arr) > $this->split) {
            return $this->_concat_split_pdfs($arr);
        }

        foreach ($arr as $files2) {
            $tmp_fn = storage_path('app/tmp/').'tmp_inv_'.$count.'.pdf';
            $count++;

            // concat temporary PDFs
            $pid = concat_pdfs($files2, $tmp_fn, true);
            $tmp_pdfs[$tmp_fn] = $pid;
        }

        return $tmp_pdfs;
    }

    /**
     * Wait for processes when ghost script was started in background to concatenate invoices in multiple threads
     *
     * Sets a value for the progress bar too (with the help of $num array)
     *
     * @param array     [temporary file paths => process IDs]
     */
    private function _wait_for_background_processes($files, &$num, $final = false)
    {
        while ($files) {
            foreach ($files as $path => $pid) {
                if (self::process_running($pid)) {
                    continue;
                }

                if (is_file($path)) {
                    unset($files[$path]);

                    // Dont advance progress bar for final files
                    if ($final) {
                        continue;
                    }

                    // Output
                    $num['sum'] += count($files) ? $this->split : $num['current'] % $this->split;
                    $num['percentage'] = $num['sum'] / $num['total'] * 100;

                    SettlementRun::push_state($num['percentage'], trans('billingbase::messages.settlementrun.state.concatInvoices'));
                    if ($this->output) {
                        $this->bar->setProgress($num['percentage']);
                    }

                    continue;
                }

                ChannelLog::error('billing', "Ghostscript failed to concatenate temporary file $path while concatenating all invoices");

                // Delete temporary files when concatenation failed
                foreach ($files as $fpath => $pid) {
                    if (is_file($fpath)) {
                        unlink($fpath);
                    }
                }

                return;
            }

            sleep(2);
        }
    }

    /**
     * Check if process is running
     *
     * @param int   pid (Process-ID)
     * @return bool     True if process is still running
     */
    public static function process_running($pid)
    {
        exec('ps -p '.escapeshellarg($pid), $op);

        return isset($op[1]);
    }
}
