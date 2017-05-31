<?php namespace Modules\Voipmon\Entities;

class Cdr extends \BaseModel {

	// SQL connection
	// Default config of the voipmonitor daemon is to create its own database, use it instead of db_lara
	protected $connection = 'mysql-voipmonitor';

	// The associated SQL table for this Model
	public $table = 'cdr';

	// Name of View
	public static function view_headline()
	{
		return 'VoipMonitor Call Data Records';
	}

	// View Icon
	public static function view_icon()
	{
		return '<i class="fa fa-phone"></i>';
	}

	public static function view_no_entries()
	{
		return 'No CDRs found: 1. Is VoipMonitor running? 2. Does remote VoipMonitor have access to local DB? 3. Is MySQL port open to remote VoipMonitor?';
	}


	// There are no validation rules
	public static function rules($id=null)
	{
		return array();
	}

	// Show only the last 1000 non-ideal CDRs
	public function index_list()
	{
		return $this->where('mos_min_mult10','<',45)->orderBy('id', 'DESC')->simplePaginate(1000);
	}

	// Link title in index view
	public function view_index_label()
	{
		if ($this->mos_min_mult10 > 40)
			$bsclass = 'success';
		elseif ($this->mos_min_mult10 > 30)
			$bsclass = 'info';
		elseif ($this->mos_min_mult10 > 20)
			$bsclass = 'warning';
		else
			$bsclass = 'danger';

		return ['index' =>	[$this->calldate, $this->caller, $this->called, $this->mos_min_mult10 / 10],
			'index_header' =>	['Call Start', 'Caller', 'Callee', 'min. MOS'],
			'bsclass' => $bsclass,
			'header' => 'Caller: '.$this->caller.' (Start: '.$this->calldate.')'];
	}

	/**
	 * All Relations
	 *
	 * link with phonenumbers
	 */
	public function phonenumber()
	{
		return $this->belongsTo('Modules\ProvVoip\Entities\Phonenumber', 'phonenumber_id');
	}

	// Belongs to a phonenumber
	public function view_belongs_to()
	{
		return $this->phonenumber;
	}

}
