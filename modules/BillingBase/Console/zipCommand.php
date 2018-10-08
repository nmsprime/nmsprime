<?php

namespace Modules\BillingBase\Console;

use Storage;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\BillingBase\Entities\Invoice;
use App\Http\Controllers\BaseViewController;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaAccount;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class zipCommand extends Command
{
    /**
     * The console command & table name
     *
     * @var string
     */
    protected $name = 'billing:zip';
    protected $description = 'Build Zip File file with all relevant Accounting Files for one specified Month';
    protected $signature = 'billing:zip {sepaacc_id?} {year?} {month?}';

    /**
     * @var Maximum number of files ghost script shall concatenate at once
     *
     * Take care: Ghost scripts argument length must not be exceeded when you change this number
     */
    private $split = 1000;

    /**
     * @var global var to store total number of splits to generate percentual output
     */
    private $num;

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
     * Package all Files in accounting directory if specified month (or last month as default)
     * NOTE: this Command is called in accounting Command!
     *
     * @author Nino Ryschawy
     *
     * TODO: Add more Error checking
     */
    public function fire()
    {
        $conf = BillingBase::first();
        $offset = intval($conf->cdr_offset);
        \App::setLocale($conf->userlang);

        $year = $this->argument('year');
        $month = $this->argument('month');
        $sepaacc = $this->argument('sepaacc_id') ? SepaAccount::findOrFail($this->argument('sepaacc_id')) : null;

        $target_t = $year && $month ? Carbon::create($year, $month) : Carbon::create()->subMonth();

        $cdr_target_t = clone $target_t;
        $cdr_target_t->subMonthNoOverflow($offset);
        $acc_files_dir_abs_path = storage_path('app/data/billingbase/accounting/').$target_t->format('Y-m');

        \ChannelLog::debug('billing', 'Zip accounting files for Month '.$target_t->toDateString());

        // Get all invoices
        // NOTE: This probably has to be replaced by DB::table for more than 10k contracts as Eloquent gets too slow then
        $invoices = Invoice::where(function ($query) use ($target_t, $cdr_target_t) {
            $query
            ->where('type', '=', 'Invoice')
            ->where('year', '=', $target_t->__get('year'))
            ->where('month', '=', $target_t->__get('month'))
            ->orWhere(function ($query) use ($cdr_target_t) {
                $query
                ->where('type', '=', 'CDR')
                ->where('year', '=', $cdr_target_t->__get('year'))->where('month', '=', $cdr_target_t->__get('month'));
            });
        })
            ->join('contract as c', 'c.id', '=', 'invoice.contract_id')
            // ->limit(1500)
            ->orderBy('c.number', 'desc')->orderBy('invoice.type');

        if ($sepaacc) {
            $invoices = $invoices->where('sepaaccount_id', '=', $sepaacc->id);
        }

        // Prepare and start output
        // $this->num = $num >= $this->split ? ((int) ($num / $this->split)) + 1 + ($num % $this->split ? 1 : 0) : null;
        // Note: 4 steps to distinguish: (1) load data (2a) concat temp files (2b) concat final invoices pdf (4) zip files
        $num = $invoices->count(); 		// Note: count doesnt consider limit() !

        if (! $num) {
            exit(0);
        }

        $this->num = $num >= $this->split ? 4 : 3;
        if ($this->output) {
            $this->bar = $this->output->createProgressBar($this->num);
            $this->bar->start();
        }
        accountingCommand::push_state(0, 'Concatenate invoices');

        // Get Data
        $invoices = $invoices->get();

        $files = [];
        foreach ($invoices as $inv) {
            $files[] = $inv->get_invoice_dir_path().$inv->filename;
        }

        accountingCommand::push_state($this->num == 3 ? 33 : 25, 'Concatenate invoices');
        if ($this->output) {
            $this->bar->advance();
        }

        /**
         * Concat Invoices
         */
        $files = $this->_concat_split_pdfs($files);

        // wait for all background processes to be finished
        if ($num > $this->split) {
            self::_wait_for_background_processes($files);
            $files = array_keys($files);

            accountingCommand::push_state(50, 'Concatenate invoices');
            if ($this->output) {
                $this->bar->advance();
            }
        }

        // concat temporary files to final target file
        $fpath = $acc_files_dir_abs_path;
        if ($sepaacc) {
            $fpath .= '/'.sanitize_filename($sepaacc->name);
        }
        $fpath .= '/'.BaseViewController::translate_label('Invoices').'.pdf';

        concat_pdfs($files, $fpath);
        // sleep(10);
        accountingCommand::push_state($this->num == 3 ? 66 : 75, 'Zip Files');
        if ($this->output) {
            $this->bar->advance();
        }

        // Zip all - suppress output of zip command
        $filename = $target_t->format('Y-m').'.zip';
        $dir = $acc_files_dir_abs_path.($sepaacc ? '/'.sanitize_filename($sepaacc->name) : '');
        chdir($dir);

        \ChannelLog::debug('billing', "ZIP Files to $filename");

        ob_start();
        // sleep(10);
        system("zip -r $filename *");
        ob_end_clean();
        if ($this->output) {
            $this->bar->finish();
            echo "\n";
        }

        echo "New file (concatenated invoices): $fpath\n";
        echo "New file (Zip): $dir/$filename\n";

        system('chmod -R 0700 '.$acc_files_dir_abs_path);
        system('chown -R apache '.$acc_files_dir_abs_path);

        // delete temp files
        if (count($files) >= $this->split) {
            foreach ($files as $fn) {
                if (is_file($fn)) {
                    unlink($fn);
                }
            }
        }
        Storage::delete('tmp/accCmdStatus');
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

            $pid = concat_pdfs($files2, $tmp_fn, true);

            $tmp_pdfs[$tmp_fn] = $pid;

            $count++;
            // Status update
            // if ($this->output)
            // 	$this->bar->advance();
            // accountingCommand::push_state((int) $count/$this->num*100, 'Concatenate invoices');
        }

        return $tmp_pdfs;
    }

    /**
     * Wait for processes when ghost script was started in background to concatenate invoices in multiple threads
     *
     * @param array 	[temporary file paths => process IDs]
     */
    private static function _wait_for_background_processes($files)
    {
        while ($files) {
            foreach ($files as $path => $pid) {
                if (self::process_running($pid)) {
                    continue;
                } else {
                    if (is_file($path)) {
                        unset($files[$path]);
                    } else {
                        ChannelLog::error('billing', "Ghostscript failed to concatenate temporary file $path while concatenating all invoices");

                        return;
                    }
                }
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
     * Remove all SepaAccount specific zip files and invoice PDFs and build the general ones
     *
     * TODO if accounting staff asks for it
     */
    private function _rezip()
    {
        // get all accounts from invoices
        // $accounts = $invoices->pluck('sepaaccount_id')->unique();

        // remove pdf and zip from account specific directories

        // build general PDF with all invoices and zip with all account directories
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
