<?php 
namespace Modules\Billingbase\Http\Controllers;
use Modules\BillingBase\Entities\SettlementRun;

class SettlementRunController extends \BaseModuleController {

	public function view_form_fields($model = null)
	{

		return [
			array('form_type' => 'text', 'name' => 'year', 'description' => 'Year', 'hidden' => 'C', 'options' => ['readonly']),
			array('form_type' => 'text', 'name' => 'month', 'description' => 'Month', 'hidden' => 'C', 'options' => ['readonly']),
			// array('form_type' => 'text', 'name' => 'path', 'description' => 'Path'),
			array('form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'),
			array('form_type' => 'checkbox', 'name' => 'verified', 'description' => 'Verified', 'hidden' => 'C'),
		];
	}


	/**
	 * Set default values, add array keys if not existent - in case of clicked rerun botton
	 */
	public function prepare_input($data)
	{
		$time_last_month = strtotime('first day of last month');

		$data['year']  = date('Y', $time_last_month);
		$data['month'] = date('m', $time_last_month);

		if (!isset($data['description']))
		{
			$data['description'] = '';
			$data['verified'] = '';			
		}

		return parent::prepare_input($data);
	}

	/**
	 * Create new Model, Run Accounting Command and Delete current Model if already existent
	 */
	public function store($redirect = true)
	{
		SettlementRun::where('month', '=', (date('m') + 11) % 12)->delete();

		\Artisan::call('billing:accounting');

		return parent::store();
	}


	/*
	 * Remove Rerun Button when next month has begun
	 */
	public function edit($id)
	{
		$obj = SettlementRun::find($id);
		$bool = date('m') == $obj->updated_at->__get('month');

		return parent::edit($id)->with('rerun_button', $bool);
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

}