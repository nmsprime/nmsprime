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

	// There are no validation rules
	public static function rules($id=null)
	{
		return array();
	}

	// Link title in index view
	public function view_index_label()
	{
		$bsclass = 'success';
		return ['index' =>	[$this->calldate, $this->callend, $this->caller,
					$this->called, $this->mos_min_mult10, $this->packet_loss_perc_mult1000,
					$this->jitter_mult10, $this->delay_avg_mult100],
			'index_header' =>	['Call Start', 'Call End', 'Caller',
				'		Callee', 'MOS x 10', 'Packet loss/% x 1000',
						'Jitter/ms x 10', 'Delay/ms x 100'],
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
