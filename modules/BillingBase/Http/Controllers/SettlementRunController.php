<?php
namespace Modules\BillingBase\Http\Controllers;

use Modules\BillingBase\Entities\AccountingRecord;
use Modules\BillingBase\Entities\BillingLogger;
use Modules\BillingBase\Entities\SettlementRun;
use Modules\BillingBase\Entities\Invoice;
use Modules\BillingBase\Console\accountingCommand;
use \Monolog\Logger;
use ChannelLog;

class SettlementRunController extends \BaseController {

	protected $edit_left_md_size = 6;
	protected $edit_right_md_size = 6;

	public function view_form_fields($model = null)
	{

		return [
			array('form_type' => 'text', 'name' => 'year', 'description' => 'Year', 'hidden' => 'C', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'month', 'description' => 'Month', 'hidden' => 'C', 'options' => ['readonly']),
			// array('form_type' => 'text', 'name' => 'path', 'description' => 'Path'),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'),
			array('form_type' => 'checkbox', 'name' => 'verified', 'description' => 'Verified', 'hidden' => 'C', 'help' => trans('helper.settlement_verification')),
		];
	}


	/**
	 * Set default values, add array keys if not existent - in case of clicked rerun botton
	 */
	public function prepare_input($data)
	{
		$time_last_month = strtotime('first day of last month');

		$data['year']  = isset($data['year']) && $data['year'] ? $data['year'] : date('Y', $time_last_month);
		$data['month'] = (int) (isset($data['month']) && $data['month'] ? $data['month'] : date('m', $time_last_month));

		if (!isset($data['description']))
		{
			$data['description'] = '';
			$data['verified'] = '';
		}

		return parent::prepare_input($data);
	}

	/**
	 * Remove Index Create button when actual Run was already created and is verified - so it's not possible
	 * to overwrite accidentially the verified data
	 */
	public function __construct()
	{
		$last_run = SettlementRun::get_last_run();
		$this->index_create_allowed = !is_object($last_run) || !($last_run->verified && ($last_run->month == date('m', strtotime('first day of last month'))));

		return parent::__construct();
	}


	/**
	 * Extends generic edit function from Basecontroller for own view
	 	* Removes Rerun Button when next month has begun
	 	* passes logs dependent of execution status of accountingCommand
	 *
	 * @return View
	 */
	public function edit($id)
	{
		$logs = $failed_jobs = [];
		$sr   = SettlementRun::find($id);
		$bool = (date('m') == $sr->created_at->__get('month')) && !$sr->verified;

		// delete Session job id if job is done in case someone broke the tcp connection (close tab/window) manually
		if (\Session::get('job_id')) {
			if (!\DB::table('jobs')->find(\Session::get('job_id')))
				\Session::remove('job_id');
		}

		// get error logs in case job failed and remove failed job from table
		$failed_jobs = \DB::table('failed_jobs')->get();
		foreach ($failed_jobs as $failed_job)
		{
			$obj = unserialize((json_decode($failed_job->payload)->data->command));
			if ($obj->name == 'billing:accounting')
			{
				\Artisan::call('queue:forget', ['id' => $failed_job->id]);
				$logs = self::get_logs($sr->updated_at->subSeconds(1)->__get('timestamp'), Logger::ERROR);
				break;
			}
		}

		// get execution logs if job has finished successfully - (show error logs otherwise - show nothing during execution)
		// NOTE: when SettlementRun gets verified the logs will disappear because timestamp is updated
		$logs = !$logs && !\Session::get('job_id') ? self::get_logs($sr->updated_at->__get('timestamp')) : $logs;

		return parent::edit($id)->with('rerun_button', $bool)->with('logs', $logs);
	}


	/**
	 * Check State of Job "accountingCommand"
	 * Send Reload info when job has finished
	 *
	 * @return 	response 	Stream
	 */
	public function check_state()
	{
		\Log::debug(__CLASS__ .'::'. __FUNCTION__);
		$response = new \Symfony\Component\HttpFoundation\StreamedResponse(function() {

			// Make Sleeptime dependent of Contract count - min 2 sec
			// $num = DB::table('contract')->where('deleted_at', '=', null)->count();
			// $sleep = (int) pow($num/10, 1/3);
			// $sleep = $sleep < 2 ? 2 : $sleep;

			$job = true;
			while ($job)
			{
				$job = \DB::table('jobs')->find(\Session::get('job_id'));
				sleep(3);
				// sleep($sleep);
			}

			\Log::debug('SettlementRun Job ['. \Session::get('job_id').'] stopped');

			\Session::remove('job_id');

			// wait for job to land in failed jobs table - if it failed - wait max 20 seconds
			$i 		 = 10;
			$success = true;

			while ($i && $success)
			{
				$i--;
				$failed_jobs = \DB::table('failed_jobs')->get();
				foreach ($failed_jobs as $job)
				{
					$obj = unserialize((json_decode($job->payload)->data->command));
					if ($obj->name == 'billing:accounting') {
						$success = false;
						break;
					}
				}

				sleep(2);
			}

			$success ? \Log::info('Settlementrun finished successfully') : \Log::error('Settlementrun failed!');

			\Log::debug('Reload Settlementrun Edit View');
			echo "data: reload\n\n";
			ob_flush(); flush();
		});

		$response->headers->set('Content-Type', 'text/event-stream');

		return $response;
	}


	/**
	 * Get Logs from Parent Function from billing.log and Format for table view
	 *
	 * @param date_time 	Unix Timestamp  	Return only Log entries after this timestamp
	 * @param severity_lvl 	Enum 				Minimum Severity Level to show
	 * @return Array 		[timestamp => [color, type, message], ...]
	 */
	public static function get_logs($date_time, $severity_lvl = Logger::NOTICE)
	{
		$logs = parent::get_logs(storage_path('logs/billing.log'), $severity_lvl);
		$old = $filtered = [];

		foreach ($logs as $key => $string)
		{
			$timestamp = substr($string, 1, 19);
			$type = substr($string, $x = strpos($string, 'billing.') + 8, $y = strpos($string, ': ') - $x);

			switch ($type)
			{
				case 'CRITICAL':
				case 'ALERT':
				case 'ERROR': $bsclass = 'danger'; break;
				case 'WARNING': $bsclass = 'warning'; break;
				case 'INFO': $bsclass = 'info'; break;
				default: $bsclass = ''; break;
			}

			$arr = array(
				'color' 	=> $bsclass,
				'time' 		=> $timestamp,
				'type' 		=> $type,
				'message' 	=> substr($string, $x + $y + 2),
				);

			if ($old == $arr)
				continue;
			if (strtotime($timestamp) < $date_time)
				break;

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
		$obj 	= SettlementRun::find($id);
		$files  = $obj->accounting_files();

		return response()->download($files[$sepaacc][$key]->getRealPath());
	}


	/**
	 * This function removes all "old" files and DB Entries created by the previous called accounting Command
	 * This is necessary because otherwise e.g. after deleting contracts the invoice would be kept and is still
	 * available in customer control center
	 * Used in: SettlementRunObserver@deleted, accountingCommand
	 *
	 * USE WITH CARE!
	 *
	 * @param 	dir 			String 		Accounting Record Files Directory relative to storage/app/
	 * @param 	settlementrun 	Object 		SettlementRun the directory should be cleared for
	 */
	public static function directory_cleanup($dir, $settlementrun = null)
	{
		$start  = $settlementrun ? date('Y-m-01 00:00:00', strtotime($settlementrun->created_at)) : date('Y-m-01');
		$end 	= $settlementrun ? date('Y-m-01 00:00:00', strtotime('+1 month', strtotime($settlementrun->created_at))) : date('Y-m-01', strtotime('+1 month'));

		// remove all entries of this month permanently (if already created)
		$ret = AccountingRecord::whereBetween('created_at', [$start, $end])->forceDelete();
		if ($ret)
			ChannelLog::debug('billing', 'Accounting Command was already executed this month - accounting table will be recreated now! (for this month)');

		// Delete all invoices
		$logmsg = 'Remove all already created Invoices and Accounting Files for this month';
		ChannelLog::debug('billing', $logmsg);	echo "$logmsg\n";

		if (!$settlementrun)
			Invoice::delete_current_invoices();

		// everything in accounting directory - SepaAccount specific
		foreach (\Storage::files($dir) as $f)
		{
			// keep cdr
			// if (pathinfo($f, PATHINFO_EXTENSION) != 'csv')
			if (basename($f) != accountingCommand::_get_cdr_filename())
				\Storage::delete($f);
		}

		foreach (\Storage::directories($dir) as $d)
			\Storage::deleteDirectory($d);
	}

}
