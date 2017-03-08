<?php

namespace Modules\BillingBase\Entities;

class SettlementRun extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'settlementrun';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			// 'month' => 'unique:settlementrun,month,'.$id.',id,year,'.$year.',deleted_at,NULL', //,year,'.$year
		);
	}


	/**
	 * Init Observer
	 */
	public static function boot()
	{
		parent::boot();

		// SettlementRun::observe(new SettlementRunObserver);
	}



	/**
	 * View related stuff
	 */

	// Name of View
	public static function view_headline()
	{
		return 'Settlement Run';
	}

	// link title in index view
	public function view_index_label()
	{
		$bsclass = $this->verified ? 'info' : 'warning';

		return ['index' => [$this->year, $this->month, $this->created_at->__get('day')],
		        'index_header' => ['Year', 'Month', 'Day'],
		        'bsclass' => $bsclass,
		        'header' => $this->year.' - '.$this->month.' - '.$this->created_at->__get('day')];
	}

	public function index_list()
	{
		return $this->orderBy('id', 'desc')->get();
	}

	public function view_has_many()
	{
		$ret['Files']['SettlementRun']['view']['view'] = 'billingbase::settlementrun';
		$ret['Files']['SettlementRun']['view']['vars'] = $this->accounting_files();

		return $ret;
	}


	public function get_files_dir()
	{
		return storage_path('app/data/billingbase/accounting/'.$this->year.'-'.sprintf('%02d', $this->month));		
	}

	public static function get_last_run()
	{
		return SettlementRun::orderBy('id', 'desc')->get()->first();
	}


	/**
	 * Relations
	 */
	public function invoices()
	{
		return $this->hasMany('Modules\BillingBase\Entities\Invoice');
	}


	/**
	 * Return all Billing Files the corresponding directory contains
	 *
	 * @return array 	containing all files ordered for view
	 */
	public function accounting_files()
	{
		$a = $b = [];

		if (is_dir($this->get_files_dir()))
		{
			$files = \File::allFiles($this->get_files_dir());

			//order files
			foreach ($files as $file)
			{
				if (!$file->getRelativePath())
					$a[] = $file;
				else
					$b[] = $file;
			}

			return array_merge($a,$b);
		}

		return [];
	}


	/**
	 * Get a list of all Invoice & CDR - Filenames from Settlement Runs that are not verified yet
	 * This list is used to hide these files until they & the Settlement Run are verified
	 *
	 * @return 	Array 	Filenames, empty array if all is verified
	 *
	 * TODO: This function is deprecated - Remove when Contract@invoices & CccAuthuserController@get_customer_invoices run fine
	 */
	public static function unverified_files()
	{
		$runs 	= Settlementrun::where('verified', '=', 0)->get(['year', 'month']);
		$offset = \Modules\BillingBase\Entities\BillingBase::first()->cdr_offset;
		$hide 	= [];


		foreach ($runs as $run)
		{
			$hide[] = $run->year.'_'.sprintf("%'.02d", $run->month).'.pdf';
			$hide[] = $offset ? date('Y_m', strtotime("-$offset month", strtotime($run->year.'-'.$run->month))).'_cdr.pdf' : $run->year.'_'.sprintf("%'.02d", $run->month).'_cdr.pdf';
			// $hide[] = ($run->month == 1 ? $run->year - 1 : $run->year).'_'.sprintf("%'.02d", $run->month == 1 ? 12 : $run->month - 1).'_cdr.pdf';
		}

		return $hide;
	}


	public function delete_related_files()
	{
		// Delete invoices - this deletes db entry and pdf file via observer
		foreach ($this->invoices as $invoice)
			$invoice->delete();
		
		// Delete accounting record files and directories
		$rel_dir = 'data/billingbase/accounting/'.$this->year.'-'.sprintf("%'.02d", $this->month).'/';
		$files 	 = Storage::allFiles($rel_dir);
		$dirs 	 = Storage::allDirectories($rel_dir);

		foreach ($files as $f)
			unlink($f);

		foreach ($dirs as $d)
			rmdir($d);

		// $dir = storage_path('app/data/billingbase/accounting/'.$this->year.'-'.sprintf("%'.02d", $this->month).'/');
	}

}


class SettlementRunObserver
{
	public function deleted($settlementrun)
	{
		// Delete all corresponding/related files in Storage and all Invoices from Database
		// $settlementrun->delete_related_files();
	}
}