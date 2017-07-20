<?php 
namespace Modules\Billingbase\Http\Controllers;
use Modules\BillingBase\Entities\SettlementRun;

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