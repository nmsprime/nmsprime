<?php

namespace Modules\BillingBase\Entities;

class SettlementRun extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'settlementrun';

	// Add your validation rules here
	public static function rules($id = null)
	{
		return array(
			// 'rcd' 	=> 'numeric|between:1,28',
		);
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

		return ['index' => [$this->year, $this->month, $this->updated_at->__get('day')],
		        'index_header' => ['Year', 'Month', 'Day'],
		        'bsclass' => $bsclass,
		        'header' => $this->year.' - '.$this->month.' - '.$this->updated_at->__get('day')];
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


	/**
	 * Return all Billing Files the corresponding directory contains
	 *
	 * @return array 	containing all files ordered for view
	 */
	public function accounting_files()
	{
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

}
