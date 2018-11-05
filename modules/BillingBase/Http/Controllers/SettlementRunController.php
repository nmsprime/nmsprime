<?php

namespace Modules\BillingBase\Http\Controllers;

use ChannelLog;
use Monolog\Logger;
use Modules\BillingBase\Entities\Invoice;
use Modules\BillingBase\Entities\Salesman;
use Modules\BillingBase\Console\cdrCommand;
use Modules\BillingBase\Entities\SettlementRun;
use Modules\BillingBase\Entities\AccountingRecord;
use Modules\BillingBase\Console\SettlementRunCommand;

class SettlementRunController extends \BaseController
{
    protected $edit_left_md_size = 6;
    protected $edit_right_md_size = 6;

    public function view_form_fields($model = null)
    {
        return [
            ['form_type' => 'text', 'name' => 'year', 'description' => 'Year', 'hidden' => 'C', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'month', 'description' => 'Month', 'hidden' => 'C', 'options' => ['readonly']],
            // array('form_type' => 'text', 'name' => 'path', 'description' => 'Path'),
            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
            ['form_type' => 'checkbox', 'name' => 'verified', 'description' => 'Verified', 'hidden' => 'C', 'help' => trans('helper.settlement_verification')],
        ];
    }

    /**
     * Set default values, add array keys if not existent - in case of clicked rerun botton
     */
    public function prepare_input($data)
    {
        $time_last_month = strtotime('first day of last month');

        $data['year'] = isset($data['year']) && $data['year'] ? $data['year'] : date('Y', $time_last_month);
        $data['month'] = (int) (isset($data['month']) && $data['month'] ? $data['month'] : date('m', $time_last_month));

        if (! isset($data['description'])) {
            $data['description'] = '';
            $data['verified'] = '';
        }

        return parent::prepare_input($data);
    }

    /**
     * Remove Index Create button when actual Run was already created
     */
    public function __construct()
    {
        $time = strtotime('first day of last month');
        $count = SettlementRun::where('month', intval(date('m', $time)))->where('year', date('Y', $time))->count();

        if ($count) {
            $this->index_create_allowed = false;
        }

        return parent::__construct();
    }

    /**
     * Extends generic edit function from Basecontroller for own view
     * Removes Rerun Button when next month has begun
     * passes logs dependent of execution status of SettlementRunCommand
     *
     * @return View
     */
    public function edit($id)
    {
        $logs = $failed_jobs = [];
        $sr = SettlementRun::find($id);
        $rerun_button = true;
        $status_msg = '';
        $job_queued = \DB::table('jobs')->where('payload', 'like', '%SettlementRunCommand%')->orWhere('payload', 'like', '%ZipCommand%')->get();
        $job_queued = $job_queued->isNotEmpty() ? $job_queued[0] : null;

        if ($job_queued) {
            // get status message
            $job = json_decode($job_queued->payload);
            $status_msg = self::getStatusMessage($job->data->commandName);

            // dont let multiple users create a lot of jobs - Session key is checked in blade
            \Session::put('job_id', $job_queued->id);
        } elseif (\Session::get('job_id')) {
            // delete Session job id if job is done in case someone broke the tcp connection (close tab/window) manually
            \Session::remove('job_id');
        }

        if ($job_queued || date('m') != $sr->created_at->__get('month') || $sr->verified) {
            $rerun_button = false;
        }

        // get error logs in case job failed and remove failed job from table
        $failed_jobs = \DB::table('failed_jobs')->get();
        foreach ($failed_jobs as $failed_job) {
            $obj = unserialize((json_decode($failed_job->payload)->data->command));
            if (\Str::contains($obj->name, 'billing:')) {
                \Artisan::call('queue:forget', ['id' => $failed_job->id]);
                $logs = self::get_logs($sr->updated_at->subSeconds(1)->__get('timestamp'), Logger::ERROR);
                break;
            }
        }

        // get execution logs if job has finished successfully - (show error logs otherwise - show nothing during execution)
        // NOTE: when SettlementRun gets verified the logs will disappear because timestamp is updated
        // $logs = !$logs && !\Session::get('job_id') ? self::get_logs($sr->updated_at->__get('timestamp')) : $logs;
        $logs = $logs ?: self::get_logs($sr->updated_at->__get('timestamp'));

        return parent::edit($id)->with(compact('rerun_button', 'logs', 'status_msg'));
    }

    public static function getStatusMessage($commandName)
    {
        switch ($commandName) {
            case 'Modules\BillingBase\Console\ZipCommand':
                return trans('messages.zipCmdProcessing');

            case 'Modules\BillingBase\Console\SettlementRunCommand':
                return trans('messages.accCmd_processing');

            default:
                return '';
        }
    }

    /**
     * Check State of Job "SettlementRunCommand"
     * Send Reload info when job has finished
     *
     * @return 	response 	Stream
     */
    public function check_state()
    {
        // ob_implicit_flush();
        // ob_end_flush();

        \Log::debug(__CLASS__.'::'.__FUNCTION__);
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () {
            $job = true;
            while ($job) {
                $job = \DB::table('jobs')->find(\Session::get('job_id'));

                if (! isset($commandName) && $job) {
                    $commandName = self::getJobCommandName($job);
                }

                $state = \Storage::exists('tmp/accCmdStatus') ? \Storage::get('tmp/accCmdStatus') : '';

                echo "data: $state".PHP_EOL.PHP_EOL;

                // Dirty fix: fill buffer to flush message as PHP-FPM's buffer can actually not really be disabled
                // Note: PHP buffer and buffer of webbrowser can be disabled by setting output_buffering = Off in /etc/php.ini and probably 'SetEnv no-gzip 1' in /etc/httpd/conf.d/nmsprime-admin.conf
                echo 'fill: '.str_pad(' ', 4095).PHP_EOL.PHP_EOL;
                ob_flush();
                flush();

                if ($state == '{"message":"Finished","value":100}') {
                    $success = true;
                    \Storage::delete('tmp/accCmdStatus');
                    goto reload;
                }

                sleep(2);
            }

            if (! isset($commandName)) {
                $commandName = 'Modules\BillingBase\Console\SettlementRunCommand';
            }

            \Log::debug("Job $commandName \[".\Session::get('job_id').'] stopped');

            \Session::remove('job_id');

            // wait for job to land in failed jobs table - if it failed - wait max 10 seconds
            $i = 5;
            $success = true;

            while ($i && $success) {
                $i--;
                $failed_jobs = \DB::table('failed_jobs')->get();
                foreach ($failed_jobs as $job) {
                    $obj = unserialize(json_decode($job->payload)->data->command);
                    if ($obj->name == 'billing:accounting') {
                        $success = false;
                        break;
                    }
                }

                sleep(2);
            }
            reload:
            $success ? \Log::info("$commandName finished successfully") : \Log::error("$commandName failed!");

            \Log::debug('Reload Settlementrun Edit View');
            echo "data: reload\n\n";
            ob_flush();
            flush();
        });

        $response->headers->set('Content-Type', 'text/event-stream');

        return $response;
    }

    /**
     * Get name of a job inside the jobs/failed_jobs table
     *
     * @return string
     */
    public static function getJobCommandName($job)
    {
        return json_decode($job->payload)->data->commandName;
    }

    /**
     * Concatenate invoices that need to be sent by post
     *
     * Note: you need to set Product IDs in storage/app/config/billingbase/post-invoice-product-ids
     *
     * @return view  SettlementRun edit page
     */
    public function create_post_invoices_pdf($id)
    {
        $settlementrun = SettlementRun::find($id);

        $id = \Queue::push(new \Modules\BillingBase\Console\ZipCommand($settlementrun, true));
        \Session::put('job_id', $id);

        return \Redirect::route('SettlementRun.edit', $settlementrun->id);
    }

    /**
     * Get Logs from Parent Function from billing.log and Format for table view
     *
     * @param date_time 	Unix Timestamp  	Return only Log entries after this timestamp
     * @param severity_lvl 	Enum 				Minimum Severity Level to show
     * @return array 		[timestamp => [color, type, message], ...]
     */
    public static function get_logs($date_time, $severity_lvl = Logger::NOTICE)
    {
        $logs = parent::get_logs(storage_path('logs/billing.log'), $severity_lvl);
        $old = $filtered = [];

        foreach ($logs as $key => $string) {
            $timestamp = substr($string, 1, 19);
            $type = substr($string, $x = strpos($string, 'billing.') + 8, $y = strpos($string, ': ') - $x);

            switch ($type) {
                case 'CRITICAL':
                case 'ALERT':
                case 'ERROR': $bsclass = 'danger'; break;
                case 'WARNING': $bsclass = 'warning'; break;
                case 'INFO': $bsclass = 'info'; break;
                default: $bsclass = ''; break;
            }

            $arr = [
                'color' 	=> $bsclass,
                'time' 		=> $timestamp,
                'type' 		=> $type,
                'message' 	=> substr($string, $x + $y + 2),
                ];

            if ($old == $arr) {
                continue;
            }
            if (strtotime($timestamp) < $date_time) {
                break;
            }

            $filtered[] = $arr;

            $old = $arr;
        }

        return $filtered;
    }

    /**
     * Return CSV with all Log Entries of minimum log level INFO
     */
    public function download_logs($id)
    {
        $sr = SettlementRun::find($id);

        $logs = self::get_logs($sr->updated_at->__get('timestamp'), Logger::INFO);

        $fn = '/tmp/billing-logs.csv';
        $fh = fopen($fn, 'w+');

        foreach (array_reverse($logs) as $key => $arr) {
            unset($arr['color']);
            fputcsv($fh, $arr);
        }

        fclose($fh);

        return response()->download($fn);
    }

    /**
     * Download a billing file or all files as ZIP archive
     */
    public function download($id, $sepaacc, $key)
    {
        $obj = SettlementRun::find($id);
        $files = $obj->accounting_files();

        return response()->download($files[$sepaacc][$key]->getRealPath());
    }

    /**
     * This function removes all "old" files and DB Entries created by the previous called accounting Command
     * This is necessary because otherwise e.g. after deleting contracts the invoice would be kept and is still
     * available in customer control center
     * Used in: SettlementRunObserver@deleted, SettlementRunCommand
     *
     * USE WITH CARE!
     *
     * @param string	dir 				Accounting Record Files Directory relative to storage/app/
     * @param object	settlementrun 		SettlementRun the directory should be cleared for
     * @param object 	sepaacc
     */
    public static function directory_cleanup($settlementrun = null, $sepaacc = null)
    {
        $dir = SettlementRunCommand::get_relative_accounting_dir_path();
        $start = $settlementrun ? date('Y-m-01 00:00:00', strtotime($settlementrun->created_at)) : date('Y-m-01');
        $end = $settlementrun ? date('Y-m-01 00:00:00', strtotime('+1 month', strtotime($settlementrun->created_at))) : date('Y-m-01', strtotime('+1 month'));

        // remove all entries of this month permanently (if already created)
        $query = AccountingRecord::whereBetween('created_at', [$start, $end]);
        if ($sepaacc) {
            $query = $query->where('sepaaccount_id', '=', $sepaacc->id);
        }

        $ret = $query->forceDelete();
        if ($ret) {
            ChannelLog::debug('billing', 'Accounting Command was already executed this month - accounting table will be recreated now! (for this month)');
        }

        // Delete all invoices
        $logmsg = 'Remove all already created Invoices and Accounting Files for this month';
        ChannelLog::debug('billing', $logmsg);
        echo "$logmsg\n";

        if (! $settlementrun) {
            Invoice::delete_current_invoices($sepaacc ? $sepaacc->id : 0);
        }

        $cdr_filepaths = cdrCommand::get_cdr_pathnames();
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

            foreach (\Storage::directories($dir) as $d) {
                \Storage::deleteDirectory($d);
            }
        }
        // SepaAccount specific stuff
        else {
            // delete ZIP
            \Storage::delete("$dir/".date('Y-m', strtotime('first day of last month')).'.zip');

            // delete concatenated Invoice pdf
            \Storage::delete("$dir/".\App\Http\Controllers\BaseViewController::translate_label('Invoices').'.pdf');

            Salesman::remove_account_specific_entries_from_csv($sepaacc->id);

            // delete account specific dir
            $dir = $sepaacc->get_relative_accounting_dir_path();
            foreach (\Storage::files($dir) as $f) {
                \Storage::delete($f);
            }

            \Storage::deleteDirectory($dir);
        }
    }
}
