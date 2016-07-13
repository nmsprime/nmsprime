<?php

namespace Modules\ProvVoipEnvia\Entities;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvVoip\Entities\Phonenumber;

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
		// combine all possible orderstatus IDs with GUI colors
		$colors = [
			1000 => 'info',			# in Bearbeitung
			1001 => 'success',		# erfolgreich verarbeitet
			1009 => 'warning',		# Warte auf Portierungserklärung
			1010 => 'warning',		# Terminverschiebung
			1012 => 'danger',		# Dokument fehlerhaft oder nicht lesbar
			1013 => 'warning',		# Warte auf Portierungsbestätigung
			1014 => 'danger',		# Fehlgeschlagen, Details siehe Bemerkung
			1015 => 'success',		# Schaltung bestätigt zum Zieltermin
			1017 => 'success',		# Stornierung bestätigt
			1018 => 'danger',		# Stornierung nicht möglich
			1019 => 'warning',		# Warte auf Zieltermin
			1036 => 'danger',		# Eskalationsstufe 1 - Warte auf Portierungsbestätigung
			1037 => 'danger',		# Eskalationsstufe 2 - Warte auf Portierungsbestätigung
			1038 => 'danger',		# Portierungsablehnung, siehe Bemerkung
			1039 => 'warning',		# Warte auf Zieltermin kleiner gleich 180 Kalendertage
		];

		// this is used to order the orders (*grin*) by their escalation levels
		$escalations = [
			'success' => 0,
			'info' => 1,
			'warning' => 2,
			'danger' => 3,
		];

		if (!boolval($this->orderstatus_id)) {
			$bsclass = 'info';
		}
		else {
	        $bsclass = $colors[$this->orderstatus_id];
		}
		$escalation_level = $escalations[$bsclass].' – '.$bsclass;

		$contract_nr = Contract::findOrFail($this->contract_id)->number;
		$contract_nr = '<a href="'.\URL::route('Contract.edit', array($this->contract_id)).'" target="_blank">'.$contract_nr.'</a>';

		if (boolval($this->phonenumber_id)) {
			$phonenumber = Phonenumber::findOrFail($this->phonenumber_id);
			$phonenumbermanagement_id = $phonenumber->phonenumbermanagement->id;
			$phonenumber_nr = $phonenumber->prefix_number.'/'.$phonenumber->number;
			$phonenumber_nr = '<a href="'.\URL::route('PhonenumberManagement.edit', array($phonenumbermanagement_id)).'" target="_blank">'.$phonenumber_nr.'</a>';
		}
		else {
			$phonenumber_nr = '–';
		}

        return ['index' => [$this->ordertype, $this->orderstatus, $escalation_level, $contract_nr, $phonenumber_nr, $this->created_at, $this->updated_at],
                'index_header' => ['Ordertype', 'Orderstatus', 'Escalation', 'Contract&nbsp;Nr.', 'Phonenumber', 'Created at', 'Updated at'],
                'bsclass' => $bsclass,
				'header' => $this->orderid.': '.$this->ordertype.' ('.$this->orderstatus.')',
		];
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
