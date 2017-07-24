<?php 
namespace Modules\Billingbase\Http\Controllers;

use Modules\BillingBase\Entities\AccountingRecord;
use Modules\BillingBase\Entities\BillingLogger;
use Modules\BillingBase\Entities\SettlementRun;
use Modules\BillingBase\Entities\Invoice;
use Modules\BillingBase\Console\accountingCommand;

class SettlementRunController extends \BaseController {

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


	/*
	 * Extends generic edit function from Basecontroller for own view - Removes Rerun Button when next month has begun
	 */
	public function edit($id)
	{
		$obj = SettlementRun::find($id);
		$bool = (date('m') == $obj->created_at->__get('month')) && !$obj->verified;

		return parent::edit($id)->with('rerun_button', $bool);
	}


	// public function store($redirect = true)
	// {
	// 	$this->dispatch(new \Modules\BillingBase\Console\accountingCommand());
	// 	return parent::store();
	// }


	/**
	 * Extend BaseControllers update to call Artisan Command when Settlement run shall rerun
	 */
	public function update($id)
	{
		// used as workaround to not display output
		// ob_start();

		if (\Input::has('rerun'))
			$this->dispatch(new \Modules\BillingBase\Console\accountingCommand);
			// \Queue::push(new \Modules\BillingBase\Console\accountingCommand);

		// ob_end_clean();

		return parent::update($id);
	}


	/**
	 * Download a billing file or all files as ZIP archive
	 */
	public function download($id, $key)
	{
		$obj 	= SettlementRun::find($id);
		$files  = $obj->accounting_files();

		return response()->download($files[$key]->getRealPath());
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
		$logger = new BillingLogger;

		$start  = $settlementrun ? date('Y-m-01 00:00:00', strtotime($settlementrun->created_at)) : date('Y-m-01');
		$end 	= $settlementrun ? date('Y-m-01 00:00:00', strtotime('+1 month', strtotime($settlementrun->created_at))) : date('Y-m-01', strtotime('+1 month'));

		// remove all entries of this month permanently (if already created)
		$ret = AccountingRecord::whereBetween('created_at', [$start, $end])->forceDelete();
		if ($ret)
			$logger->addInfo('Accounting Command was already executed this month - accounting table will be recreated now! (for this month)');

		// Delete all invoices
		$logmsg = 'Remove all already created Invoices and Accounting Files for this month';
		$logger->addDebug($logmsg);	echo "$logmsg\n";

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