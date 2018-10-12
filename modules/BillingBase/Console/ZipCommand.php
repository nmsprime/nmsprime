<?php

namespace Modules\BillingBase\Console;

use Storage;
use ChannelLog;
use Illuminate\Console\Command;
use Modules\BillingBase\Entities\Invoice;
use App\Http\Controllers\BaseViewController;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\SettlementRun;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ZipCommand extends Command
{
    /**
     * The console command & table name
     *
     * @var string
     */
    protected $name = 'billing:zip';
    protected $description = 'Build Zip File file with all relevant Accounting Files for one specified Month';
    protected $signature = 'billing:zip {sepaacc_id?} {--settlementrun=}';

    /**
     * @var Maximum number of files ghost script shall concatenate at once
     *
     * Take care: Ghost scripts argument length must not be exceeded when you change this number
     */
    private $split = 1000;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
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
    public function fire()
    {
        $conf = BillingBase::first();
        \App::setLocale($conf->userlang);

        $settlementrun = $this->getRelevantSettlementRun();

        if (! $settlementrun) {
            $msg = 'Could not find SettlementRun for last month or wrong SettlementRun ID specified!';
            \ChannelLog::error('billing', $msg);
            echo "$msg\n";

            return;
        }

        // get directory from mutator function
        $settlementrun->directory = $settlementrun->directory;

        ChannelLog::debug('billing', "Zip accounting files for SettlementRun $settlementrun->month/$settlementrun->year [ID: $settlementrun->id]");

        $sepaaccs = $this->getRelevantSepaAccounts($settlementrun);

        if (! $sepaaccs) {
            $msg = "No invoices found for SettlementRun $settlementrun->year-$settlementrun->month [ID: $settlementrun->id]!";
            \ChannelLog::error('billing', $msg);
            echo "$msg\n";

            return;
        }

        $this->concatenateInvoices($settlementrun, $sepaaccs);
        $this->zipDirectory($settlementrun);

        system('chmod -R 0700 '. $settlementrun->directory);
        system('chown -R apache '. $settlementrun->directory);

        Storage::delete('tmp/accCmdStatus');
    }


    private function getRelevantSettlementRun()
    {
        if ($this->option('settlementrun'))
            return SettlementRun::find($this->option('settlementrun'));

        $time = strtotime('last month');

        return SettlementRun::where('month', date('m', $time))->where('year', date('Y', $time))->orderBy('id', 'desc')->first();
    }

    /**
     * Get all SepaAccounts that have Invoices in the specified settlementrun
     *
     * Note: the number of invoices is appended to each SepaAccount
     *
     * @return Collection
     */
    private function getRelevantSepaAccounts($settlementrun)
    {
        if ($this->argument('sepaacc_id')) {
            $sepaacc_ids = [$this->argument('sepaacc_id')];

            // Get invoice count
            $counts[$sepaacc_ids] = Invoice::where('settlementrun_id', $settlementrun->id)->where('sepaaccount_id', $sepaacc_ids)->count();
        } else {
            $sepaaccs = Invoice::where('settlementrun_id', $settlementrun->id)
                ->groupBy('sepaaccount_id')
                ->select('sepaaccount_id', \DB::raw('count(*) as invoice_cnt'))
                ->get();

            foreach ($sepaaccs as $acc) {
                $counts[$acc->sepaaccount_id] = $acc->invoice_cnt;
            }

            $sepaacc_ids = $sepaaccs->pluck('sepaaccount_id')->all();
        }

        $sepaaccs = SepaAccount::whereIn('id', $sepaacc_ids)->get();

        // append invoice_cnt
        foreach ($sepaaccs as $acc) {
            $acc->invoice_cnt = $counts[$acc->id];
        }

        return $sepaaccs;
    }

    private function getRelevantInvoices($settlementrun, $sepaacc)
    {
        return Invoice::where('settlementrun_id', $settlementrun->id)->where('sepaaccount_id', $sepaacc->id)->get();
    }

    private function concatenateInvoices($settlementrun, $sepaaccs)
    {
        // variable initialization
        $num = ['total' => 0, 'current' => 0, 'sum' => 0];
        foreach ($sepaaccs as $acc) {
            $num['total'] += $acc->invoice_cnt <= $this->split ? $acc->invoice_cnt : 2 * $acc->invoice_cnt;
        }

        if ($this->output) {
            echo "Concatenate invoices\n";
            $this->bar = $this->output->createProgressBar(100);
            $this->bar->start();
        }
        SettlementRunCommand::push_state(0, 'Concatenate invoices');

        foreach ($sepaaccs as $i => $sepaacc) {
            $invoices = isset($invoices) ? $invoices : $this->getRelevantInvoices($settlementrun, $sepaacc);

            // build temporary PDFs for 1000 invoices
            $files = [];
            foreach ($invoices as $inv) {
                $files[] = $inv->get_invoice_dir_path().$inv->filename;
            }

            $files = $this->_concat_split_pdfs($files);

            $num['current'] = count($invoices);
            unset($invoices);

            // wait for all background processes to be finished if some exist and get next invoices already
            if ($num['current'] > $this->split) {
                if (isset($sepaaccs[$i + 1]))
                    $invoices = $this->getRelevantInvoices($settlementrun, $sepaaccs[$i + 1]);

                $this->_wait_for_background_processes($files, $num);
                $files = array_keys($files);
            }

            // Concat Invoices/temporary files to final target file
            $fpath = $settlementrun->directory.'/'.sanitize_filename($sepaacc->name);
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

    private function zipDirectory ($settlementrun)
    {
        $filename = $settlementrun->year.'-'.str_pad($settlementrun->month, 2, '0', STR_PAD_LEFT).'.zip';
        chdir($settlementrun->directory);

        ChannelLog::debug('billing', "ZIP Files to $filename");
        echo "\nZIP accounting files and directories...\n";

        // suppress output of zip command
        ob_start();
        system("zip -r $filename *");
        ob_end_clean();

        echo "New file: $settlementrun->directory/$filename\n";
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
