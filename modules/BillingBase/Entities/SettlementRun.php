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
		$ret['Billing']['SettlementRun']['view']['view'] = 'billingbase::settlementrun';
		$ret['Billing']['SettlementRun']['view']['vars'] = $this->accounting_files();

		return $ret;
	}


	public function get_files_dir()
	{
		return storage_path('app/data/billingbase/accounting/'.$this->year.'-'.sprintf('%02d', $this->month));		
	}


	public function accounting_files()
	{
		return \File::allFiles($this->get_files_dir());
	}

}
