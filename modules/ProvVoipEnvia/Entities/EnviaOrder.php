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
	public static function view_headline()
	{
		return 'EnviaOrders';
	}

	// link title in index view
	public function view_index_label()
	{
        $bsclass = 'success';

        return ['index' => [$this->orderid],
                'index_header' => ['ID'],
                'bsclass' => $bsclass,
                'header' => $this->orderid.': '.$this->ordertype.' ('.$this->orderstatus.')'];
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

	// returns all objects that are related to an EnviaOrder
	public function view_has_many()
	{
		if (\PPModule::is_active('provvoipenvia')) {
			$ret['Envia']['EnviaOrderDocument']['class'] = 'EnviaOrderDocument';
			$ret['Envia']['EnviaOrderDocument']['relation'] = $this->enviaorderdocument;
			$ret['Envia']['EnviaOrderDocument']['method'] = 'show';
			$ret['Envia']['EnviaOrderDocument']['options']['hide_delete_button'] = '1';
		}
		else {
			$ret = array();
		}

		return $ret;
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
