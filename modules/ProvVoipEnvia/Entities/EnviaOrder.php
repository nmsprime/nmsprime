<?php

namespace Modules\ProvVoipEnvia\Entities;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvVoip\Entities\Phonenumber;

// Model not found? execute composer dump-autoload in lara root dir
class EnviaOrder extends \BaseModel {

	// The associated SQL table for this Model
	public $table = 'enviaorder';

	// collect all order related informations ⇒ later we can use subarrays of this array to get needed informations
	// mark missing data with value null
	protected static $meta = array(

		// TODO: Process the list with all possible ordertypes ⇒ hope to get this from envia some day…
		'orders' => array(
			array(
				'ordertype' => 'Neuschaltung envia TEL voip reselling',
				'ordertype_id' => null,
				'method' => 'contract/create',
				'phonenumber_related' => False,
			),
			array(
				'ordertype' => 'Neuschaltung einer Rufnummer',
				'ordertype_id' => 19,
				'method' => 'voip_account/create',
				'phonenumber_related' => True,
			),
			array(
				'ordertype' => 'Sprachtarif wird geändert',
				'ordertype_id' => null,
				'method' => 'contract/change_tariff',
				'phonenumber_related' => False,
			),
			array(
				'ordertype' => 'Stornierung eines Auftrags',
				'ordertype_id' => null,
				'method' => 'order/cancel',
				'phonenumber_related' => False,
			),
		),
		'states' => array(
			array(
				'orderstatus_id' => 1000,
				'orderstatus' => 'in Bearbeitung',
				'view_class' => 'info',
				'state_type' => 'pending',
				'final' => False,
			),
			array(
				'orderstatus_id' => 1001,
				'orderstatus' => 'erfolgreich verarbeitet',
				'view_class' => 'success',
				'state_type' => 'success',
				'final' => True,
			),
			array(
				'orderstatus_id' => 1009,
				'orderstatus' => 'Warte auf Portierungserklärung',
				'view_class' => 'warning',
				'state_type' => 'pending',
				'final' => False,
			),
			array(
				'orderstatus_id' => 1010,
				'orderstatus' => 'Terminverschiebung',
				'view_class' => 'warning',
				'state_type' => 'pending',
				'final' => False,
			),
			array(
				'orderstatus_id' => 1012,
				'orderstatus' => 'Dokument fehlerhaft oder nicht lesbar',
				'view_class' => 'danger',
				'state_type' => 'pending',
				'final' => False,
			),
			array(
				'orderstatus_id' => 1013,
				'orderstatus' => 'Warte auf Portierungsbestätigung',
				'view_class' => 'warning',
				'state_type' => 'pending',
				'final' => False,
			),
			array(
				'orderstatus_id' => 1014,
				'orderstatus' => 'Fehlgeschlagen, Details siehe Bemerkung',
				'view_class' => 'danger',
				'state_type' => 'failed',
				'final' => True,
			),
			array(
				'orderstatus_id' => 1015,
				'orderstatus' => 'Schaltung bestätigt zum Zieltermin',
				'view_class' => 'success',
				'state_type' => 'success',
				'final' => True,
			),
			array(
				'orderstatus_id' => 1017,
				'orderstatus' => 'Stornierung bestätigt',
				'view_class' => 'success',
				'state_type' => 'success',
				'final' => True,
			),
			array(
				'orderstatus_id' => 1018,
				'orderstatus' => 'Stornierung nicht möglich',
				'view_class' => 'danger',
				'state_type' => 'failed',
				'final' => True,
			),
			array(
				'orderstatus_id' => 1019,
				'orderstatus' => 'Warte auf Zieltermin',
				'view_class' => 'warning',
				'state_type' => 'pending',
				'final' => False,
			),
			array(
				'orderstatus_id' => 1036,
				'orderstatus' => 'Eskalationsstufe 1 - Warte auf Portierungsbestätigung',
				'view_class' => 'danger',
				'state_type' => 'pending',
				'final' => False,
			),
			array(
				'orderstatus_id' => 1037,
				'orderstatus' => 'Eskalationsstufe 2 - Warte auf Portierungsbestätigung',
				'view_class' => 'danger',
				'state_type' => 'pending',
				'final' => False,
			),
			array(
				'orderstatus_id' => 1038,
				'orderstatus' => 'Portierungsablehnung, siehe Bemerkung',
				'view_class' => 'danger',
				'state_type' => 'failed',
				'final' => True,
			),
			array(
				'orderstatus_id' => 1039,
				'orderstatus' => 'Warte auf Zieltermin kleiner gleich 180 Kalendertage',
				'view_class' => 'warning',
				'state_type' => 'pending',
				'final' => False,
			),
		),

	);

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
		'method',
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
		'modem_id',
		'phonenumber_id',
	];


	/**
	 * Get the order subarray from meta
	 *
	 * @author Patrick Reichel
	 *
	 * @return array containing metadata for all order types:
	 *			<str> ordertype
	 *			<int> ordertype_id
	 *			<str> method
	 *			<bool> phonenumber_related
	 */
	public static function get_orders_metadata() {
		return self::$meta['orders'];
	}


	/**
	 * Get the stats subarray from meta
	 *
	 * @author Patrick Reichel
	 *
	 * @return array containing metadata for all order states:
	 *			<int> orderstatus_id
	 *			<str> orderstatus
	 *			<str> view_class
	 *			<str> state_type
	 *			<bool> final
	 */
	public static function get_states_metadata() {
		return self::$meta['states'];
	}


	/**
	 * Checks if an orderstatus is final
	 *
	 * @author Patrick Reichel
	 *
	 * @param $order to check
	 *
	 * @return true if orderstatus is final (will not change anymore), else false
	 */
	public static function orderstate_is_final($order) {

		$finals = array();
		foreach (self::$meta['states'] as $state_meta) {
			$final = $state_meta['final'];
			$type = $state_meta['orderstatus'];
			$id = $state_meta['orderstatus_id'];

			if ($final) {
				if (!is_null($type))
					array_push($finals, $type);
				if (!is_null($id))
					array_push($finals, $id);
			}
		};

		$final_state = (
			in_array($order->orderstatus_id, $finals)
			||
			in_array($order->orderstatus, $finals)
		);

		return $final_state;
	}


	/**
	 * Checks if a given ordertype is phonenmumber related
	 *
	 * @param order to check
	 *
	 * @author Patrick Reichel
	 */
	public static function ordertype_is_phonenumber_related($order) {

		$relates = array();
		foreach (self::$meta['orders'] as $order_meta) {

			$related = $order_meta['phonenumber_related'];
			$type = $order_meta['ordertype'];
			$id = $order_meta['ordertype_id'];

			if ($related) {
				if (!is_null($type))
					array_push($relates, $type);
				if (!is_null($id))
					array_push($relates, $id);
			}
		}

		$related_state = (
			in_array($order->ordertype, $relates)
			||
			in_array($order->ordertype_id, $relates)
			||
			in_array($order->method, $relates)
		);

		return $related_state;
	}


	/**
	 * Checks if an order is related to a given method.
	 * Call this in your specialized methods
	 *
	 * @author Patrick Reichel
	 */
	protected static function _order_mapped_to_method($order, $method) {

		$matches = array();
		foreach (self::$meta['orders'] as $order_meta) {
			$cur_method = $order_meta['method'];
			if ($cur_method == $method) {
				$cur_type = $order_meta['ordertype'];
				$cur_id = $order_meta['ordertype_id'];

				array_push($matches, $cur_method);
				if (!is_null($cur_type))
					array_push($matches, $cur_type);
				if (!is_null($cur_id))
					array_push($matches, $cur_id);
			}
		}

		$mapped_to_method = (
			in_array($order->ordertype, $matches)
			||
			in_array($order->ordertype_id, $matches)
			||
			in_array($order->method, $matches)
		);

		return $mapped_to_method;
	}


	/**
	 * Checks if an order matches a given state_type.
	 * Use this in your concrete checks
	 *
	 * @author Patrick Reichel
	 */
	protected static function _order_mapped_to_state_type($order, $state_type) {

		$matches = array();
		foreach (self::$meta['states'] as $state_meta) {
			$cur_state_type = $state_meta['state_type'];
			if ($cur_state_type == $state_type) {
				$cur_state = $state_meta['orderstatus'];
				$cur_id = $state_meta['orderstatus_id'];
				if (!is_null($cur_state))
					array_push($matches, $cur_state);
				if (!is_null($cur_id))
					array_push($matches, $cur_id);
			}
		}

		$mapped_to_state_type = (
			in_array($order->orderstatus, $matches)
			||
			in_array($order->orderstatus_id, $matches)
		);

		return $mapped_to_state_type;
	}


	/**
	 * Check if order is successfully processed.
	 *
	 * @author Patrick Reichel
	 */
	public static function order_successful($order) {
		return self::_order_mapped_to_state_type($order, 'success');
	}


	/**
	 * Check if order is pending.
	 *
	 * @author Patrick Reichel
	 */
	public static function order_pending($order) {
		return self::_order_mapped_to_state_type($order, 'pending');
	}


	/**
	 * Check if order has failed
	 *
	 * @author Patrick Reichel
	 */
	public static function order_failed($order) {
		return self::_order_mapped_to_state_type($order, 'failed');
	}


	/**
	 * Checks if order is related to creation of a phonenumber
	 *
	 * @author Patrick Reichel
	 */
	public static function order_creates_voip_account($order) {
		return self::_order_mapped_to_method($order, 'voip_account/create');
	}


	/**
	 * Checks if order is related to termination of a phonenumber
	 *
	 * @author Patrick Reichel
	 */
	public static function order_terminates_voip_account($order) {
		return self::_order_mapped_to_method($order, 'voip_account/terminate');
	}


	/**
	 * Checks if order cancels another order
	 *
	 * @author Patrick Reichel
	 */
	public static function order_cancels_other_order($order) {
		return self::_order_mapped_to_method($order, 'order/cancel');
	}


	// Name of View
	public static function view_headline()
	{
		return 'EnviaOrders';
	}

	// link title in index view
	public function view_index_label()
	{
		// combine all possible orderstatus IDs with GUI colors
		$colors = array();
		foreach (self::$meta['states'] as $state) {
			$colors[$state['orderstatus_id']] = $state['view_class'];
		}

		// this is used to group the orders by their escalation levels (so later on we can sort them by these levels)
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

		if (boolval($this->modem_id)) {
			$modem = Modem::findOrFail($this->modem_id);
			$modem_nr = '<a href="'.\URL::route('Modem.edit', array($this->modem_id)).'" target="_blank">'.$this->modem_id.'</a>';
		}
		else {
			$modem_nr = '–';
		}

		if (boolval($this->phonenumber_id)) {
			$phonenumber = Phonenumber::findOrFail($this->phonenumber_id);
			$phonenumbermanagement_id = $phonenumber->phonenumbermanagement->id;
			$phonenumber_nr = $phonenumber->prefix_number.'/'.$phonenumber->number;
			$phonenumber_nr = '<a href="'.\URL::route('PhonenumberManagement.edit', array($phonenumbermanagement_id)).'" target="_blank">'.$phonenumber_nr.'</a>';
		}
		else {
			$phonenumber_nr = '–';
		}

		if (!$this->user_interaction_necessary()) {
			$current = '-';
		    $solve_link = '';
		}
		else {
			$current = '<b>Yes!!</b>';
			$solve_link = '<a href="'.\URL::route("EnviaOrder.marksolved", array('EnviaOrder' => $this->id)).'" target="_self">Mark solved</a>';
		}

        return ['index' => [$this->ordertype, $this->orderstatus, $escalation_level, $contract_nr, $modem_nr, $phonenumber_nr, $this->created_at, $this->updated_at, $current, $solve_link],
                'index_header' => ['Ordertype', 'Orderstatus', 'Escalation', 'Contract&nbsp;Nr.', 'Modem', 'Phonenumber', 'Created at', 'Updated at', 'Interaction needed?', ''],
                'bsclass' => $bsclass,
				'header' => $this->orderid.' – '.$this->ordertype.': '.$this->orderstatus,
		];
	}


	/**
	 * Prepare the list of orders to be shown on index page
	 *
	 * @author Patrick Reichel
	 *
	 * @todo check if there should be some filters (e.g. to show only open orders)
	 */
	public function index_list() {

		return $this->orderBy('id')->get();
	}


	// belongs to phonenumber or modem or contract - see BaseModel for explanation
	public function view_belongs_to ()
	{
		if (boolval($this->phonenumber_id)) {
			return $this->phonenumber->phonenumbermanagement;
		}
		elseif (boolval($this->modem_id)) {
			return $this->modem;
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

	public function modem() {
		return $this->belongsTo('Modules\ProvBase\Entities\Modem');
	}

	public function phonenumber() {
		return $this->belongsTo('Modules\ProvVoip\Entities\Phonenumber');
	}

	public function enviaorderdocument() {
		return $this->hasMany('Modules\ProvVoipEnvia\Entities\EnviaOrderDocument', 'enviaorder_id')->orderBy('created_at');
	}


	/**
	 * Create table containing information about the contract
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_user_action_information_contract($contract){

		$th_style = "padding-right: 10px;";
		$td_style = $th_style;

		$ret = '<table class="table-hover">';
		$ret .= '<tr>';
		$ret .= '<th style="'.$th_style.'">Number</th>';
		$ret .= '<th style="'.$th_style.'">Contract start</th>';
		$ret .= '<th style="'.$th_style.'">Contract end</th>';
		$ret .= '<th style="'.$th_style.'">Internet access?</th>';
		$ret .= '</tr>';

		$ret .= '<tr>';
		$ret .= '<td style="'.$td_style.'"><a href="'.\URL::route("Contract.edit", array("Contract" => $contract->id)).'">'.$contract->number.'</a></td>';
		$ret .= '<td style="'.$td_style.'">';
			(boolval($contract->contract_start) ? $ret.=$contract->contract_start : $ret.='–');
		$ret .= '</td>';
		$ret .= '<td style="'.$td_style.'">';
			(boolval($contract->contract_end) ? $ret.=$contract->contract_end : $ret.='–');
		$ret .= '</td>';
		$ret .= '<td style="'.$td_style.'">';
			($contract->network_access > 0 ? $ret.="✔" : $ret.="");
			$ret .'</td>';
		$ret .= '</tr>';

		$ret .= '</table>';

		return $ret;
	}


	/**
	 * Create table containing information about related items
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_user_action_information_items($items){

		$th_style = "padding-right: 10px;";
		$td_style = $th_style;

		$ret = '<table class="table-hover">';
		$ret .= '<tr>';
		$ret .= '<th style="'.$th_style.'">Product</th>';
		$ret .= '<th style="'.$th_style.'">Type</th>';
		$ret .= '<th style="'.$th_style.'">Valid from</th>';
		$ret .= '<th style="'.$th_style.'">Fix?</th>';
		$ret .= '<th style="'.$th_style.'">Valid to</th>';
		$ret .= '<th style="'.$th_style.'">Fix?</th>';
		$ret .= '</tr>';

		foreach ($items as $item) {
			$ret .= '<tr>';
			$ret .= '<td style="'.$td_style.'"><a href="'.\URL::route("Item.edit", array("Item" => $item->id)).'">'.$item->product->name.'</a></td>';
			$ret .= '<td style="'.$td_style.'">'.$item->product->type.'</td>';
			$ret .= '<td style="'.$td_style.'">'.(boolval($item->valid_from) ? $item->valid_from : "–").'</td>';
			$ret .= '<td style="'.$td_style.'">';
				($item->valid_from_fixed > 0 ? $ret.="✔" : $ret.="");
				$ret .'</td>';
			$ret .= '<td style="'.$td_style.'">'.(boolval($item->valid_to) ? $item->valid_to : "–").'</td>';
			$ret .= '<td style="'.$td_style.'">';
				($item->valid_to_fixed > 0 ? $ret.="✔" : $ret.="");
				$ret .'</td>';
			$ret .= '</tr>';
		}

		$ret .= '</table>';

		/* echo $ret; */
		/* d($item->product); */
		return $ret;
	}


	/**
	 * Create table containing information about the modem
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_user_action_information_modem($modem){

		$th_style = "padding-right: 10px;";
		$td_style = $th_style;

		$ret = '<table class="table-hover">';
		$ret .= '<tr>';
		$ret .= '<th style="'.$th_style.'">Hostname</th>';
		$ret .= '<th style="'.$th_style.'">MAC address</th>';
		$ret .= '<th style="'.$th_style.'">Configfile</th>';
		$ret .= '<th style="'.$th_style.'">Network access?</th>';
		$ret .= '</tr>';

		$ret .= '<tr>';
		$ret .= '<td style="'.$td_style.'"><a href="'.\URL::route("Modem.edit", array("Modem" => $modem->id)).'">'.$modem->hostname.'</a></td>';
		$ret .= '<td style="'.$td_style.'">'.$modem->mac.'</td>';
		$ret .= '<td style="'.$td_style.'">'.$modem->configfile->name.'</td>';
		$ret .= '<td style="'.$td_style.'">';
			($modem->network_access > 0 ? $ret.="✔" : $ret.="");
			$ret .'</td>';
		$ret .= '</tr>';

		$ret .= '</table>';

		return $ret;
	}


	/**
	 * Create table containing information about related phonenumbers
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_user_action_information_phonenumbers($phonenumbers){

		$th_style = "padding-right: 10px;";
		$td_style = $th_style;

		$ret = '<table class="table-hover">';
		$ret .= '<tr>';
		$ret .= '<th style="'.$th_style.'">Phonenumber</th>';
		$ret .= '<th style="'.$th_style.'">Activation target</th>';
		$ret .= '<th style="'.$th_style.'">Activation confirmed</th>';
		$ret .= '<th style="'.$th_style.'">Deactivation target</th>';
		$ret .= '<th style="'.$th_style.'">Deactivation confirmed</th>';
		$ret .= '</tr>';

		foreach ($phonenumbers as $phonenumber) {
			$phonenumbermanagement = $phonenumber->phonenumbermanagement;
			$ret .= '<tr>';
			$ret .= '<td style='.$td_style.'"><a href="'.\URL::route("PhonenumberManagement.edit", array("phonenumbermanagement" => $phonenumbermanagement->id)).'">'.$phonenumber->prefix_number.'/'.$phonenumber->number.'</a></td>';
			$ret .= '<td style="'.$td_style.'">'.(boolval($phonenumbermanagement->activation_date) ? $phonenumbermanagement->activation_date : "–").'</td>';
			$ret .= '<td style="'.$td_style.'">'.(boolval($phonenumbermanagement->external_activation_date) ? $phonenumbermanagement->external_activation_date : "–").'</td>';
			$ret .= '<td style="'.$td_style.'">'.(boolval($phonenumbermanagement->deactivation_date) ? $phonenumbermanagement->deactivation_date : "–").'</td>';
			$ret .= '<td style="'.$td_style.'">'.(boolval($phonenumbermanagement->external_deactivation_date) ? $phonenumbermanagement->external_deactivation_date : "–").'</td>';
			$ret .= '</tr>';
		}

		$ret .= '</table>';

		/* echo $ret; */
		/* d($item->product); */
		return $ret;
	}


	/**
	 * Get informations about necessary/possible user interaction.
	 * In this first step we only provide a link to mark this open order as (manually) solved.
	 * Later on we can provide hints what to do or even solve automagically
	 *
	 * @author Patrick Reichel
	 */
	public function get_user_action_information() {

		$user_actions = array();
		$user_actions['hints'] = array();
		$user_actions['links'] = array();


		if ($this->user_interaction_necessary()) {

			$contract = $this->contract;
			$user_actions['hints']['Contract (= Envia Customer)'] = $this->_get_user_action_information_contract($contract);

			$items = $contract->items;
			if ($items) {
				$user_actions['hints']['Items'] = $this->_get_user_action_information_items($items);
			};

			$modem = $this->modem;
			if ($modem) {
				$user_actions['hints']['Modem (= Envia Contract)'] = $this->_get_user_action_information_modem($modem);
			};

			$phonenumbers = array();
			if ($modem) {
				$mtas = $modem->mtas;
				if ($mtas) {
					foreach ($mtas as $mta) {
						foreach ($mta->phonenumbers as $phonenumber) {
							if ($phonenumber) {
								array_push($phonenumbers, $phonenumber);
							}
						}
					}
				}
			}

			if ($phonenumbers) {
				$user_actions['hints']['Phonenumbers (= Envia VoipAccounts)'] = $this->_get_user_action_information_phonenumbers($phonenumbers);
			}

			// finally add link to mark open order as solved
			$user_actions['links']['Mark as solved'] = \URL::route("EnviaOrder.marksolved", array('EnviaOrder' => $this->id));
		}

		return $user_actions;
	}

	/**
	 * Creates the mailto: links (ready-to-use) for generation of email templates.
	 *
	 * @author Patrick Reichel
	 */
	public function get_mailto_links() {

		$mailto_links = array();

		$user = \Auth::user();

		$line = str_repeat("-", 64);

		// prepare the mailto link
		$to = "Realisierung-Standard@enviatel.de";

		// create the subject
		$subject = "Frage zu Order ".$this->orderid;
		if ($this->ordertype)
			$subject .= " (".$this->ordertype.")";

		// create the mail body
		$body = "Sehr geehrte Damen und Herren,\n\n";
		$body .= "zu folgender Order hätte ich eine Frage:\n\n";
		$body .= "Order ID: ".$this->orderid."\n";
		if ($this->customerreference)
			$body .= "Customer reference: ".$this->customerreference."\n";
		if ($this->contractreference)
			$body .= "Contract reference: ".$this->contractreference."\n";
		if ($this->ordertype)
			$body .= "Order type: ".$this->ordertype."\n";
		$body .= "\n\n$line\n\n\n\n$line\n\n\nMit freundlichen Grüßen\n\n";

		// add the current logged in user's name (after the greeting)
		if ($user->first_name)
			$body .= $user->first_name;
		if ($user->last_name) {
			if ($user->first_name) {
				$body .= " ";
			}
			$body .= $user->last_name."\n";
		}

		// add to array; later on there can be multiple links to different mail addresses
		$mailto_links[$to] = "mailto:".$to."?Subject=".rawurlencode($subject)."&amp;Body=".rawurlencode($body);

		return $mailto_links;

	}


	/**
	 * Checks if user interaction is necessary.
	 *
	 * @author Patrick Reichel
	 *
	 * @todo add more rules for ordertype-orderstatus combinations that don't need user interaction
	 */
	public function user_interaction_necessary() {

		// first: if last user interaction was after last status update – nothing to do
		if ($this->last_user_interaction > $this->updated_at) {
			return false;
		}

		// if current state is “in Bearbeitung” then we have to do nothing
		// next action is to perform by Envia
		if ($this->orderstatus_id == 1000) {
			return false;
		}

		// default (be pessimistic): we have to perform action…
		return true;
	}


	/**
	 * Marks an open order as “solved“.
	 * That means: update the last_user_interaction to now without touching updated_at
	 *
	 * @author Patrick Reichel
	 */
	public function mark_as_solved() {

		// temporary disable refreshing of timestamps (= don't touch updated_at)
		$this->timestamps = false;

		// set last user interaction date to now
		$this->last_user_interaction = \Carbon\Carbon::now()->toDateTimeString();
		$this->save();
	}

}
