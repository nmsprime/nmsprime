<?php

namespace Modules\BillingBase\Http\Controllers;

use DB;
use Log;
use Session;
use Monolog\Logger;
use Modules\BillingBase\Entities\Product;
use Modules\BillingBase\Entities\SettlementRun;

class SettlementRunController extends \BaseController
{
    protected $edit_left_md_size = 6;
    protected $edit_right_md_size = 6;

    public function view_form_fields($model = null)
    {
        $ret1 = [
            ['form_type' => 'text', 'name' => 'year', 'description' => 'Year', 'hidden' => 'C', 'options' => ['readonly']],
            ['form_type' => 'text', 'name' => 'month', 'description' => 'Month', 'hidden' => 'C', 'options' => ['readonly']],
            // array('form_type' => 'text', 'name' => 'path', 'description' => 'Path'),
            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
            ['form_type' => 'checkbox', 'name' => 'verified', 'description' => 'Verified', 'hidden' => 'C', 'help' => trans('helper.settlement_verification')],
            ['form_type' => 'checkbox', 'name' => 'fullrun', 'description' => 'For internal use', 'hidden' => 1],
        ];

        $ret2 = [];
        if (\Module::collections()->has('OverdueDebts') && ! $this->jobQueued()) {
            if (config('overduedebts.debtMgmtType') == 'csv') {
                $descKey = 'overduedebts::view.uploadCsv';
            } else {
                $descKey = 'overduedebts::view.uploadSta';

                $ret2[] = ['form_type' => 'text', 'name' => 'voucher_nr', 'description' => 'Voucher number'];
            }

            $ret2[] = ['form_type' => 'file', 'name' => 'banking_file_upload', 'description' => trans($descKey)];
        }

        return array_merge($ret1, $ret2);
    }

    /**
     * Set default values, add array keys if not existent - in case of clicked rerun botton
     */
    public function prepare_input($data)
    {
        $time_last_month = strtotime('first day of last month');

        $data['year'] = $data['year'] ?? date('Y', $time_last_month);
        $data['month'] = (int) ($data['month'] ?? date('m', $time_last_month));

        return parent::prepare_input($data);
    }

    public function prepare_rules($rules, $data)
    {
        if (! (isset($data['fullrun']) && $data['fullrun'])) {
            $rules['verified'] = 'In:0';
        }

        if (\Module::collections()->has('OverdueDebts') && config('overduedebts.debtMgmtType') == 'sta') {
            if (isset($data['banking_file_upload'])) {
                $rules['voucher_nr'] = 'required';
            }
        }

        return parent::prepare_rules($rules, $data);
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

    private function jobQueued()
    {
        $jobs = ['SettlementRun', 'ZipSettlementRun', 'DebtImport', 'ParseMt940'];

        $jobQueued = DB::table('jobs')->where('payload', 'like', "%{$jobs[0]}%");

        foreach ($jobs as $key => $name) {
            if ($key == 0) {
                continue;
            }

            $jobQueued = $jobQueued->orWhere('payload', 'like', "%{$jobs[$key]}%");
        }

        $jobQueued = $jobQueued->get();

        return $jobQueued->isNotEmpty() ? $jobQueued[0] : null;
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
        $status_msg = '';
        $job_queued = $this->jobQueued();

        if ($job_queued) {
            // get status message
            $job = json_decode($job_queued->payload);
            $status_msg = self::getStatusMessage($job->data->commandName);
            // dont let multiple users create a lot of jobs - Session key is checked in blade
            Session::put('srJobId', $job_queued->id);
        } elseif (Session::get('srJobId')) {
            // delete Session job id if job is done in case someone broke the tcp connection (close tab/window) manually
            Session::remove('srJobId');
        }

        $button['postal'] = ! Session::get('srJobId') && ! $job_queued && Product::where('type', 'Postal')->count() ? true : false;
        $button['rerun'] = true;
        if ($job_queued || date('m') != $sr->created_at->__get('month') || $sr->verified) {
            $button['rerun'] = false;
        }

        // get error logs in case job failed and remove failed job from table
        $failed_jobs = DB::table('failed_jobs')->get();
        foreach ($failed_jobs as $failed_job) {
            $commandName = json_decode($failed_job->payload)->data->commandName;
            if (\Str::contains($commandName, '\\SettlementRun')) {
                \Artisan::call('queue:forget', ['id' => $failed_job->id]);
                $logs = self::get_logs(strtotime('-1 second', strtotime($sr->executed_at)), Logger::ERROR);
                break;
            }
        }

        // get execution logs if job has finished successfully - show error logs otherwise
        $logs['settlementrun'] = $logs ?: self::get_logs(strtotime($sr->executed_at));
        if (\Module::collections()->has('OverdueDebts')) {
            $logs['bankTransfer'] = self::get_logs($sr->uploaded_at ? strtotime($sr->uploaded_at) : 0, Logger::INFO, 'bank-transactions.log');
        }

        return parent::edit($id)->with(compact('button', 'logs', 'status_msg'));
    }

    public static function getStatusMessage($commandName)
    {
        switch ($commandName) {
            case 'Modules\BillingBase\Jobs\ZipSettlementRun':
                return trans('messages.zipCmdProcessing');

            case 'Modules\BillingBase\Jobs\SettlementRunJob':
                return trans('messages.accCmd_processing');

            case 'Modules\OverdueDebts\Jobs\DebtImportJob':
                return trans('overduedebts::messages.csvImportActive');

            case 'Modules\OverdueDebts\Jobs\ParseMt940':
                return trans('overduedebts::messages.staParsingActive');

            default:
                return '';
        }
    }

    /**
     * Check State of Job "SettlementRunCommand"
     * Send Reload info when job has finished
     *
     * @return  response    Stream
     */
    public function check_state()
    {
        // ob_implicit_flush();
        // ob_end_flush();

        Log::debug(__CLASS__.'::'.__FUNCTION__);
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () {
            $job = true;
            while ($job) {
                $job = DB::table('jobs')->find(Session::get('srJobId'));

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
                $commandName = 'Modules\BillingBase\Jobs\SettlementRunJob';
            }

            Log::debug("Job $commandName \[".Session::get('srJobId').'] stopped');

            Session::remove('srJobId');

            // wait for job to land in failed jobs table - if it failed - wait max 10 seconds
            $i = 5;
            $success = true;

            while ($i && $success) {
                $i--;
                $failed_jobs = DB::table('failed_jobs')->get();
                foreach ($failed_jobs as $job) {
                    $commandName = self::getJobCommandName($job);
                    if (\Str::contains($commandName, '\\SettlementRun')) {
                        $success = false;
                        break;
                    }
                }

                sleep(2);
            }
            reload:
            $success ? Log::info("$commandName finished successfully") : Log::error("$commandName failed!");

            Log::debug('Reload Settlementrun Edit View');
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

        $id = \Queue::push(new \Modules\BillingBase\Jobs\ZipSettlementRun($settlementrun, null, true));
        Session::put('srJobId', $id);

        return \Redirect::route('SettlementRun.edit', $settlementrun->id);
    }

    /**
     * Get Logs from Parent Function from billing.log and Format for table view
     *
     * @param ts_from       Unix Timestamp      Return only Log entries after this timestamp
     * @param severity_lvl  Enum                Minimum Severity Level to show
     * @return array        [timestamp => [color, type, message], ...]
     */
    public static function get_logs($ts_from, $severity_lvl = Logger::NOTICE, $logfile = 'billing.log')
    {
        // TODO: use appropriate file in history
        $fpath = storage_path("logs/$logfile");
        $logs = parent::get_logs($fpath, $severity_lvl);
        $old = $filtered = [];

        foreach ($logs as $key => $string) {
            $timestamp = substr($string, 1, 19);

            if (strtotime($timestamp) < $ts_from) {
                break;
            }

            preg_match('/\[.*\.([A-Z]*):/', $string, $match);
            $type = $match ? $match[1] : '';

            switch ($type) {
                case 'CRITICAL':
                case 'ALERT':
                case 'ERROR': $bsclass = 'danger'; break;
                case 'WARNING': $bsclass = 'warning'; break;
                case 'INFO': $bsclass = 'info'; break;
                case 'NOTICE': $bsclass = 'active'; break;
                default: $bsclass = ''; break;
            }

            $arr = [
                'color'     => $bsclass,
                'time'      => $timestamp,
                'type'      => $type,
                'message'   => substr($string, strpos($string, ': ') + 2),
            ];

            if ($old == $arr) {
                continue;
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

    public function destroy($id)
    {
        $id = key(\Request::get('ids'));
        $settlementrun = SettlementRun::find($id);

        Session::push('tmp_info_above_index_list', trans('messages.deleteSettlementRun', ['time' => $settlementrun->year.'-'.$settlementrun->month]));
        dispatch(new \Modules\BillingBase\Jobs\DeleteSettlementRun($settlementrun));

        return redirect()->back();
    }
}
