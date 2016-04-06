<?php

namespace Modules\ProvVoipEnvia\Entities;

// Model not found? execute composer dump-autoload in lara root dir
class EnviaOrder extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'enviaorder';

	// Add your validation rules here
	public static function rules($id=null) {

		return array(
			// Prevent users from creating orders (table enviaorder is only changable through Envia API!)
			// TODO: later remove delete button
			'orderid' => 'required|integer|min:1',
			'related_order_id' => 'exists:enviaorder,id',
		);

	}

	// Don't forget to fill this array
	protected $fillable = [
		'orderid',
		'ordertype_id',
		'ordertype',
		'orderstatus_id',
		'orderstatus',
		'orderdate',
		'ordercomment',
		'related_order_id',
		'customerreference',
		'contractreference',
		'contract_id',
		'phonenumber_id',
	];

	// Name of View
	public static function get_view_header()
	{
		return 'EnviaOrders';
	}

	// link title in index view
	public function get_view_link_title()
	{
		$ret = $this->orderid.' – '.$this->ordertype;

		return $ret;
	}

	// belongs to a modem - see BaseModel for explanation
	public function view_belongs_to ()
	{
		if (boolval($this->phonenumber_id)) {
			return $this->phonenumber->phonenumbermanagement;
		}
		else {
			return $this->contract;
		}
	}

	// returns all objects that are related to a mta
	public function view_has_many()
	{
		return array(
			'EnviaOrderDocument' => $this->enviaorderdocument,
		);
	}

	public function contract() {
		return $this->belongsTo('Modules\ProvBase\Entities\Contract');
	}

	public function phonenumber() {
		return $this->belongsTo('Modules\ProvVoip\Entities\Phonenumber');
	}

	public function enviaorderdocument() {
		return $this->hasMany('Modules\ProvVoipEnvia\Entities\EnviaOrderDocument', 'enviaorder_id')->orderBy('created_at');
	}

}
