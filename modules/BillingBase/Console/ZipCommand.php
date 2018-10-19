<?php

namespace Modules\BillingBase\Console;

use Storage;
use ChannelLog;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Modules\BillingBase\Entities\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\BaseViewController;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\SettlementRun;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ZipCommand extends Command implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The console command & table name
     *
     * @var string
     */
    public $name = 'billing:zip';
    protected $description = 'Build Zip file with all relevant Accounting Files of specified SettlementRun';
    protected $signature = 'billing:zip {sepaacc_id?} {--settlementrun=} {--postal-invoices}';

    /**
     * @var Maximum number of files ghost script shall concatenate at once
     *
     * Take care: Ghost scripts argument length must not be exceeded when you change this number
     */
    private $split = 1000;

    /**
     * @var object  SettlementRun the ZIP shall be built for
     */
    private $settlementrun;

    /**
     * @var bool    If true -> concatenate invoices to single PDF that need to be sent via post
     */
    private $postalInvoices;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SettlementRun $sr, $postalInvoices = false)
    {
        $this->settlementrun = $sr;
        $this->postalInvoices = $postalInvoices;

        parent::__construct();
    }

    /**
     * Execute the console command
     *
     * Package all Files in SettlementRun's accounting files directory
     * NOTE: this Command is called in SettlementRunCommand!
     *
     * @author Nino Ryschawy
     */
    public function handle()
    {
        $conf = BillingBase::first();
        \App::setLocale($conf->userlang);

        $this->settlementrun = $this->getRelevantSettlementRun();

        if (! $this->settlementrun) {
            $msg = 'Could not find SettlementRun for last month or wrong SettlementRun ID specified!';
            \ChannelLog::error('billing', $msg);
            echo "$msg\n";

            return;
        }

        // set directory via mutator function
        $this->settlementrun->directory = $this->settlementrun->directory;
        $this->settlementrun->txt = $this->settlementrun->year.'-'.$this->settlementrun->month.' [ID: '.$this->settlementrun->id.']';

        // Only concatenate postal invoices if flag is set
        if ($this->postalInvoices || ($this->output && $this->option('postal-invoices'))) {
            $this->concatPostalInvoices();

            goto end;
        }

        ChannelLog::debug('billing', 'Zip accounting files for SettlementRun '.$this->settlementrun->txt);

        $sepaAccs = $this->getRelevantSepaAccounts($this->settlementrun);

        if (! $sepaAccs) {
            $msg = 'No invoices found for SettlementRun '.$this->settlementrun->txt;
            \ChannelLog::error('billing', $msg);
            echo "$msg\n";

            return;
        }

        $this->concatenateInvoicesForSepaAccounts($sepaAccs);
        $this->zipDirectory();

end:
        system('chmod -R 0700 '. $this->settlementrun->directory);
        system('chown -R apache '. $this->settlementrun->directory);
        Storage::delete('tmp/accCmdStatus');
    }


    private function getRelevantSettlementRun()
    {
        if ($this->settlementrun->id) {
            return $this->settlementrun;
        }

        if ($this->output && $this->option('settlementrun')) {
            return SettlementRun::find($this->option('settlementrun'));
        }

        // Default
        $time = strtotime('last month');

        return SettlementRun::where('month', date('m', $time))->where('year', date('Y', $time))->orderBy('id', 'desc')->first();
    }

    /**
     * TODO: merge with concatenateInvoicesForSepaAccounts()
     */
    private function concatPostalInvoices()
    {
        $prod_fpath_rel = 'config/billingbase/post-invoice-product-ids';
        $prod_fpath_abs = storage_path("app/$prod_fpath_rel");

        if (! \Storage::exists($prod_fpath_rel)) {
            ChannelLog::error('billing', 'Build postal invoices PDF failed: Missing file '.$prod_fpath_abs);

            return;
        }

        $prod_ids = \Storage::get($prod_fpath_rel);
        $prod_ids = str_replace(' ', '', $prod_ids);
        $prod_ids = explode(',', trim(str_replace([';', PHP_EOL], ',', $prod_ids), ','));

        SettlementRunCommand::push_state(0, 'Get data...');

        $invoices = Invoice::join('contract', 'contract.id', '=', 'invoice.contract_id')
            ->join('item', 'contract.id', '=', 'item.contract_id')
            ->where('invoice.settlementrun_id', $this->settlementrun->id)
            ->whereIn('item.product_id', $prod_ids)
            ->orderBy('contract.number')->orderBy('invoice.type')
            ->get();

        $num = count($invoices);

        SettlementRunCommand::push_state(0, 'Concatenate postal invoices...');
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

        SettlementRunCommand::push_state(50, 'Concatenate postal invoices...');

        // Concat Invoices/temporary files to final target file
        $targetFilepath = $this->settlementrun->directory."/".trans('messages.postalInvoices').'.pdf';

        concat_pdfs($files, $targetFilepath, false);
        echo "\nNew file: $targetFilepath\n";
        SettlementRunCommand::push_state(100, 'Concatenate postal invoices...');

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
     * Get all SepaAccounts that have Invoices in the specified settlementrun
     *
     * Note: the number of invoices is appended to each SepaAccount
     *
     * @return Collection
     */
    private function getRelevantSepaAccounts()
    {
        if ($this->input && $this->argument('sepaacc_id')) {
            $sepaAcc_ids = $this->argument('sepaacc_id');

            // Get invoice count
            $counts[$sepaAcc_ids] = Invoice::where('settlementrun_id', $this->settlementrun->id)->where('sepaaccount_id', $sepaAcc_ids)->count();
            $sepaAcc_ids = [$sepaAcc_ids];
        } else {
            $sepaAccs = Invoice::where('settlementrun_id', $this->settlementrun->id)
                ->groupBy('sepaaccount_id')
                ->select('sepaaccount_id', \DB::raw('count(*) as invoice_cnt'))
                ->get();

            foreach ($sepaAccs as $acc) {
                $counts[$acc->sepaaccount_id] = $acc->invoice_cnt;
            }

            $sepaAcc_ids = $sepaAccs->pluck('sepaaccount_id')->all();
        }

        $sepaAccs = SepaAccount::whereIn('id', $sepaAcc_ids)->get();

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

    /**
     * Concatenate Invoices of specific SettlementRun for specific SepaAccounts (performance optimized)
     *
     * Show actual (but imprecise) status
     */
    private function concatenateInvoicesForSepaAccounts($sepaAccs)
    {
        // variable initialization
        $num = ['total' => 0, 'current' => 0, 'sum' => 0, 'percentage' => 0];
        $invoices = [];
        foreach ($sepaAccs as $acc) {
            $num['total'] += $acc->invoice_cnt <= $this->split ? $acc->invoice_cnt : 2 * $acc->invoice_cnt;
        }

        if ($this->output) {
            echo "Concatenate invoices\n";
            $this->bar = $this->output->createProgressBar(100);
            $this->bar->start();
        }
        SettlementRunCommand::push_state(0, 'Concatenate invoices');

        foreach ($sepaAccs as $i => $sepaAcc) {
            $invoices = $invoices ?: $this->getRelevantInvoices($sepaAcc);

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
                if (isset($sepaAccs[$i + 1]))
                    $invoices = $this->getRelevantInvoices($sepaAccs[$i + 1]);

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

            if ($num['sum'] == $num['total'])
                $num['percentage'] = 100 - (100 - $num['percentage']) / 2;
            else
                $num['percentage'] = $num['sum'] / $num['total'] * 100;

            SettlementRunCommand::push_state($num['percentage'], 'Concatenate invoices');
            if ($this->output) {
                $this->bar->setProgress($num['percentage']);
            }
        }

        $this->_wait_for_background_processes($final_files, $num, true);

        SettlementRunCommand::push_state(100, 'Concatenate invoices');
        if ($this->output) {
            $this->bar->finish();
        }

        foreach (array_keys($final_files) as $fpath) {
            echo "\nNew file: $fpath";
        }
    }

    private function zipDirectory ()
    {
        $filename = $this->settlementrun->year.'-'.str_pad($this->settlementrun->month, 2, '0', STR_PAD_LEFT).'.zip';
        chdir($this->settlementrun->directory);

        ChannelLog::debug('billing', "ZIP Files to $filename");
        echo "\nZIP accounting files and directories...\n";

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
     * @param array 	invoice files
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
     * @param array 	[temporary file paths => process IDs]
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

                    SettlementRunCommand::push_state($num['percentage'], 'Concatenate invoices');
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
     * @param int 	pid (Process-ID)
     * @return bool 	True if process is still running
     */
    public static function process_running($pid)
    {
        exec('ps -p '.escapeshellarg($pid), $op);

        return isset($op[1]);
    }

    /**
     * Get the console command arguments / options
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            // ['cycle', InputArgument::OPTIONAL, '1 - without TV, 2 - only TV'],
        ];
    }

    protected function getOptions()
    {
        return [
            // ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
