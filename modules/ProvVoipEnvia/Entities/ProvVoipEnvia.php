<?php

namespace Modules\ProvVoipEnvia\Entities;

use Modules\ProvBase\Entities\Contract;
use Modules\ProvVoip\Entities\Phonenumber;
use Modules\ProvVoip\Entities\PhonenumberManagement;
use Modules\ProvVoip\Entities\PhonebookEntry;
use Modules\ProvVoip\Entities\CarrierCode;
use Modules\ProvVoip\Entities\EkpCode;
use Modules\ProvVoip\Entities\Mta;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvVoipEnvia\Entities\EnviaOrder;
use Modules\ProvVoipEnvia\Entities\EnviaOrderDocument;

// Model not found? execute composer dump-autoload in lara root dir
class ProvVoipEnvia extends \BaseModel {


	/**
	 * Constructor.
	 *
	 * @author Patrick Reichel
	 */
	public function __construct($attributes = array()) {

		// this has to be a float value to allow stable version compares
		$v = $_ENV['PROVVOIPENVIA__REST_API_VERSION'];
		if (!is_numeric($v)) {
			throw new \InvalidArgumentException('PROVVOIPENVIA__REST_API_VERSION in .env has to be a float value (e.g.: 1.4)');
		};
		$this->api_version = floatval($v);

		// call \BaseModel's constructor
		parent::__construct($attributes);

	}


	/**
	 * Helper method to fake XML returns.
	 * This will return a SimpleXML instance which can be used instead a real Envia answer.
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_xml_fake($xml_string) {

		return new \SimpleXMLElement($xml_string);
	}

	/**
	 * Helper to prettify xml for output on screen.
	 * Use e.g. for debugging.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $xml string containing xml data
	 * @param $hide_credentials don't show username/password if set to True
	 * @return string containing prettified xml
	 */
	public static function prettify_xml($xml, $hide_credentials=True) {

		$dom = new \DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml);

		// replace username and password by some hash signs
		// this replaces the former preg_replace variant which crashes on larger EnviaOrderDocument uploads.
		// also this is more elegant and should also be faster
		if ($hide_credentials) {
		$reseller_identifiers = $dom->getElementsByTagName('reseller_identifier');
			foreach ($reseller_identifiers as $reseller_identifier) {

				$users = $reseller_identifier->getElementsByTagName('username');
				foreach ($users as $user) {
					$user->nodeValue = "################";
				}

				$pws = $reseller_identifier->getElementsByTagName('password');
				foreach ($pws as $pw) {
					$pw->nodeValue = "################";
				}
			}
		}

		$pretty = htmlentities($dom->saveXML());
		$lines = explode("\n", $pretty);

		// extract declaration line
		$declaration = array_shift($lines);
		$declaration = '<span style="color: #0000ff; font-weight: normal">'.$declaration.'</span>';
		$output = array();

		// colorize output
		foreach ($lines as $line) {
			$pretty = $line;
			$pretty = str_replace('/', 'dummy_slash', $pretty);
			$pretty = str_replace('&quot; ', '</span>&quot; ', $pretty);
			$pretty = str_replace('&quot;/', '</span>&quot;/', $pretty);
			$pretty = str_replace('=&quot;', '=&quot;<span style="color: black; font-weight: bold">', $pretty);
			$pretty = str_replace('&lt;', '</span>&lt;<span style="color: #660000; font-weight: normal">', $pretty);
			$pretty = str_replace('&gt;', '</span>&gt;<span style="color: black; font-weight: bold">', $pretty);
			$pretty = str_replace('&lt;', '<span style="color: #0000ff; font-weight: normal">&lt;</span>', $pretty);
			$pretty = str_replace('&gt;', '<span style="color: #0000ff; font-weight: normal">&gt;</span>', $pretty);
			$pretty = str_replace('dummy_slash', '<span style="color: #0000ff; font-weight: normal">/</span>', $pretty);
			array_push($output, $pretty);
		}

		// reinsert declaration line
		array_unshift($output, $declaration);

		$pretty_xml = implode("\n", $output);

		return $pretty_xml;

	}

	/**
	 * Get some environmental data and set to global vars
	 *
	 * @author Patrick Reichel
	 */
	public function extract_environment($model, $view_level) {
		// check if a model is given
		if (is_null($model)) {
			return array();
		}

		$this->_get_model_data($view_level, $model);
		$phonenumber_id = $this->phonenumbermanagement->phonenumber_id;
		if (!is_null($this->phonenumbermanagement->phonebookentry)) {
			$phonebookentry_id = $this->phonenumbermanagement->phonebookentry->id;
		}
		$contract_id = $this->contract->id;

		/* // check for valid view_level and define get models to use */
		/* if ($view_level == 'phonenumbermanagement') { */

		/* 	// given model is a phonenumbermanagement object */
		/* 	$phonenumber_id = $phonenumbermanagement->phonenumber_id; */
		/* 	$contract_id = $contract->id; */
		/* } */
		/* elseif ($view_level == 'contract') { */

		/* 	// given model is a contract object */
		/* 	$phonenumber_id = null; */
		/* 	$contract_id = $contract->id; */
		/* } */
		/* else { */
		/* 	throw new \UnexpectedValueException('param $view_level has to be in [contract|phonenumbermanagement]'); */
		/* } */

		// set the variables
		if (is_null($this->contract->contract_ext_creation_date)) {
			$this->contract_created = False;
		}
		else {
			$this->contract_created = True;
		}

		if (is_null($this->contract->contract_ext_termination_date)) {
			$this->contract_terminated = False;
		}
		else {
			$this->contract_terminated = True;
		}

		if ($this->contract_created && !$this->contract_terminated) {
			$this->contract_available = True;
		}
		else {
			$this->contract_available = False;
		}

		if (is_null($this->phonenumbermanagement->voipaccount_ext_creation_date)) {
			$this->voipaccount_created = False;
		}
		else {
			$this->voipaccount_created = True;
		}

		if (is_null($this->phonenumbermanagement->voipaccount_ext_termination_date)) {
			$this->voipaccount_terminated = False;
		}
		else {
			$this->voipaccount_terminated = True;
		}

		if ($this->voipaccount_created && !$this->voipaccount_terminated) {
			$this->voipaccount_available = True;
		}
		else {
			$this->voipaccount_available = False;
		}

		if (is_null($this->phonebookentry->external_creation_date)) {
			$this->phonebookentry_created = False;
			$this->phonebookentry_available = False;
		}
		else {
			$this->phonebookentry_created = True;
			$this->phonebookentry_available = True;
		}


	}


	/**
	 * Get array with all jobs for given view.
	 * Currently in use in contract and phonenumbermanagement
	 *
	 * @author Patrick Reichel
	 *
	 * @param $phonenumbermanagement phonenumberManagement object
	 * @param $view_level depending on the view (contract, phonenumbermanagement) the result can be different
	 *
	 * @return array containing data for view
	 */
	public function get_jobs_for_view($model, $view_level) {

		$this->extract_environment($model, $view_level);

		// helpers
		$base = "/lara/admin/provvoipenvia/request/";
		if ($view_level == 'phonenumbermanagement') {
			$contract_id = $model->phonenumber->mta->modem->contract->id;
			$phonenumber_id = $model->phonenumber_id;
			$phonenumbermanagement_id = $model->id;
			if (!is_null($model->phonebookentry)) {
				$phonebookentry_id = $model->phonebookentry->id;
			}
		}
		elseif ($view_level == 'contract') {
			$contract_id = $model->id;
			$phonenumbermanagement_id = null;
			$phonenumber_id = null;
			$phonebookentry_id = null;
		}
		elseif ($view_level == 'phonenumber') {
			$contract_id = $model->mta->modem->contract->id;
			$phonenumber_id = $model->id;
			if (!is_null($model->phonenumbermanagement)) {
				$phonenumbermanagement_id = $model->phonenumbermanagement->id;
			}
			else {
				$phonenumbermanagement_id = null;
			}
			if (!is_null($model->phonenumbermanagement) && !is_null($model->phonenumbermanagement->phonebookentry)) {
				$phonebookentry_id = $model->phonenumbermanagement->phonebookentry->id;
			}
			else {
				$phonebookentry_id = null;
			}
		}
		elseif ($view_level == 'phonebookentry') {
			$contract_id = $model->phonenumbermanagement->phonenumber->mta->modem->contract->id;
			$phonenumber_id = $model->phonenumbermanagement->phonenumber_id;
			$phonenumbermanagement_id = $model->phonenumbermanagement->id;
			$phonebookentry_id = $model->id;
		}
		else {
			throw new \UnexpectedValueException('param $view_level has to be in [contract|phonenumber|phonenumbermanagement|phonebookentry]');
		}

		// add this to all actions that can be performed without extra confirmation
		$really = '&amp;really=True';

		// keep original URL
		$origin = '?origin='.urlencode(\Request::getUri());

		////////////////////////////////////////
		// misc jobs
		if (in_array($view_level, ['contract', 'phonenumber', 'phonenumbermanagement', 'phonebookentry'])) {
			$ret = array(
				array('class' => 'Misc'),
				array('linktext' => 'Ping Envia API', 'url' => $base.'misc_ping'.$origin.$really),
				array('linktext' => 'Get free numbers', 'url' => $base.'misc_get_free_numbers'.$origin.$really),
			);
		}


		////////////////////////////////////////
		// contract related jobs
		if (in_array($view_level, ['contract', 'phonenumbermanagement'])) {
			array_push($ret, array('class' => 'Contract'));

			// contract can be created if not yet created
			if (!$this->contract_created) {
				array_push($ret, array('linktext' => 'Create contract', 'url' => $base.'contract_create'.$origin.'&amp;contract_id='.$contract_id));
			}

			// contract can be terminated if is created and not yet terminated
			// not yet implemented ⇒ a contract will terminated automatically by termination of the last number
			/* if ($this->contract_available) { */
			/* 	array_push($ret, array('linktext' => 'Terminate contract', 'url' => $base.'contract_terminate'.$origin.'&amp;contract_id='.$contract_id)); */
			/* } */

			// customer data change possible if there is a contract
			if ($this->contract_available) {
				array_push($ret, array('linktext' => 'Update customer', 'url' => $base.'customer_update'.$origin.'&amp;contract_id='.$contract_id));
			}

			// can get contract related information if contract is available
			if ($this->contract_available) {
				array_push($ret, array('linktext' => 'Get voice data', 'url' => $base.'contract_get_voice_data'.$origin.'&amp;contract_id='.$contract_id.$really));
			}

			// tariff can only be changed if contract exists and a tariff change is wanted
			// TODO: implement checks for current change state; otherwise we get an error from Envia (change into the same tariff is not possible)
			if ($this->contract_available) {
				if (boolval($this->contract->next_voip_id)) {
					if ($this->contract->voip_id != $this->contract->next_voip_id) {
						array_push($ret, array('linktext' => 'Change tariff', 'url' => $base.'contract_change_tariff'.$origin.'&amp;contract_id='.$contract_id));
					}
				}
			}

			// variation can only be changed if contract exists and a variation change is wanted
			// TODO: implement checks for current change state; otherwise we get an error from Envia (change into the same variation is not possible)
			if ($this->contract_available) {
				if (boolval($this->contract->next_voip_id)) {
					if ($this->contract->voip_id != $this->contract->next_voip_id) {
						array_push($ret, array('linktext' => 'Change variation', 'url' => $base.'contract_change_variation'.$origin.'&amp;contract_id='.$contract_id));
					}
				}
			}

		}


		////////////////////////////////////////
		// voip account related jobs
		if (in_array($view_level, ['phonenumbermanagement'])) {
			array_push($ret, array('class' => 'VoIP account'));

			// voip account needs a contract
			if (!$this->voipaccount_created && $this->contract_available) {
				array_push($ret, array('linktext' => 'Create VoIP account', 'url' => $base.'voip_account_create'.$origin.'&amp;phonenumber_id='.$phonenumber_id,));
			}

			if ($this->voipaccount_available) {
				array_push($ret, array('linktext' => 'Terminate VoIP account', 'url' => $base.'voip_account_terminate'.$origin.'&amp;phonenumber_id='.$phonenumber_id));
			};

			if ($this->voipaccount_available) {
				array_push($ret, array('linktext' => 'Update VoIP account', 'url' => $base.'voip_account_update'.$origin.'&amp;phonenumber_id='.$phonenumber_id));
			};
		}



		////////////////////////////////////////
		// order related jobs
		if (in_array($view_level, ['contract', 'phonenumber', 'phonenumbermanagement'])) {
			array_push($ret, array('class' => 'Orders'));
			array_push($ret, array('linktext' => 'Get all phonenumber related orders', 'url' => $base.'misc_get_orders_csv'.$origin.$really));
			// order(s) exist if contract has been created
			if ($this->contract_created) {
				foreach (EnviaOrder::withTrashed()->where('contract_id', '=', $contract_id)->orderBy("created_at")->get() as $order) {

					// if in view phonenumber*: show only orders related to this phonenumber
					if (in_array($view_level, ['phonenumber', 'phonenumbermanagement'])) {
						if (boolval($order->phonenumber_id) && $order->phonenumber_id != $phonenumber_id) {
							continue;
						}
					}

					// process data
					$order_id = $order->orderid;
					$order_type = $order->ordertype;
					$order_status = $order->orderstatus;
					$linktext = $order_id.' – '.$order_type.': <i>'.$order_status.'</i>';
					// stroke soft deleted entries
					if (boolval($order->deleted_at)) {
						$linktext = '<s>'.$linktext.'</s>';
					}
					// add order (exept create_attachements)
					if ($order_type != 'order/create_attachment') {
						array_push($ret, array('linktext' => $linktext, 'url' => $base.'order_get_status'.$origin.'&amp;order_id='.$order_id.$really));
					}
				}
			}
		}


		////////////////////////////////////////
		// phonebookentry related jobs
		if (in_array($view_level, ['phonenumbermanagement', 'phonebookentry'])) {

			array_push($ret, array('class' => 'Phonebook entry'));

			array_push($ret, array('linktext' => 'Get phonebook entry', 'url' => $base.'phonebookentry_get'.$origin.'&amp;phonenumbermanagement_id='.$phonenumbermanagement_id));

			if ($view_level == 'phonebookentry') {
				array_push($ret, array('linktext' => 'Create/change phonebook entry', 'url' => $base.'phonebookentry_create'.$origin.'&amp;phonebookentry_id='.$phonebookentry_id));
			}

		}

		////////////////////////////////////////
		// configuration related stuff
		/* if (in_array($view_level, ['phonenumbermanagement'])) { */
		/* 	array_push($ret, array('class' => 'Configuration')); */

		/* 	if ($this->voipaccount_available) { */
		/* 		array_push($ret, array('linktext' => 'Get Configuration', 'url' => $base.'selfcare/configuration/get'.$origin.'&amp;phonenumber_id='.$phonenumber_id.'&amp;'.$really)); */
		/* 	} */
		/* } */


		////////////////////////////////////////
		// calllog related stuff
		/* if (in_array($view_level, ['phonenumbermanagement'])) { */
		/* 	array_push($ret, array('class' => 'Calllog')); */

		/* 	if ($this->voipaccount_available) { */
		/* 		array_push($ret, array('linktext' => 'Get calllog status', 'url' => $base.'selfcare/calllog/get_status'.$origin.'&amp;contract_id='.$contract_id.'&amp;'.$really)); */
		/* 	} */
		/* } */


		////////////////////////////////////////
		// blacklist related stuff
		/* if (in_array($view_level, ['phonenumbermanagement'])) { */
		/* 	array_push($ret, array('class' => 'Blacklist')); */

		/* 	if ($this->voipaccount_available) { */
		/* 		array_push($ret, array('linktext' => 'Get blacklist in', 'url' => $base.'selfcare/blacklist/get'.$origin.'&amp;phonenumber_id='.$phonenumber_id.'&amp;envia_blacklist_get_direction=in&amp;'.$really)); */
		/* 		array_push($ret, array('linktext' => 'Get blacklist out', 'url' => $base.'selfcare/blacklist/get'.$origin.'&amp;phonenumber_id='.$phonenumber_id.'&amp;envia_blacklist_get_direction=out&amp;'.$really)); */
		/* 	} */
		/* } */


		return $ret;
	}


	/**
	 * Generate the XML used for communication against Envia API
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job to do
	 *
	 * @return XML
	 */
	public function get_xml($job) {

		$this->_get_model_data();

		$this->_create_base_xml_by_topic($job);
		$this->_create_final_xml_by_topic($job);

		return $this->xml->asXML();
	}


	/**
	 * Get all the data needed for this job.
	 * This will get the data for the current and all parent models (e.g. contract for phonenumber) and store as cls variables
	 * To do so we have to differentiate in the job to do
	 *
	 * @author Patrick Reichel
	 *
	 * @param $level current level to work from
	 * @param $model the model to get related models from ($model is of type $level)
	 */
	protected function _get_model_data($level='', $model=null) {

		// defaults
		$this->contract = null;
		$this->modem = null;
		$this->mta = null;
		$this->phonenumber = null;
		$this->phonenumbermanagement = null;
		$this->phonebookentry = null;

		// level is irrelevant (e.g. for creating XML for a given contract_id
		// this means: the initial model comes from a database search
		if ($level == '') {
			// entry point to database is contract
			$contract_id = \Input::get('contract_id', null);
			if (!is_null($contract_id)) {
				$this->contract = Contract::findOrFail($contract_id);
			}

			// entry point to database is phonenumber
			$phonenumber_id = \Input::get('phonenumber_id', null);
			if (!is_null($phonenumber_id)) {
				$this->phonenumber = Phonenumber::findOrFail($phonenumber_id);
			}

			// get related models (if phonenumber model exists)
			// in other cases: there are no clear relations
			if (!is_null($this->phonenumber)) {
				$this->mta = $this->phonenumber->mta;
				$this->modem = $this->mta->modem;
				$this->contract = $this->modem->contract;
				$this->phonenumbermanagement = $this->phonenumber->phonenumbermanagement;
				$this->phonebookentry = $this->phonenumbermanagement->phonebookentry;
			}

			// entry point is phonenumbermanagement
			$phonenumbermanagement_id = \Input::get('phonenumbermanagement_id', null);
			if (!is_null($phonenumbermanagement_id)) {
				$this->phonenumbermanagement = PhonenumberManagement::findOrFail($phonenumbermanagement_id);
			}

			// get related models
			if (!is_null($this->phonenumbermanagement)) {
				$this->phonebookentry = $this->phonenumbermanagement->phonebookentry;
				$this->phonenumber= $this->phonenumbermanagement->phonenumber;
				$this->mta = $this->phonenumber->mta;
				$this->modem = $this->mta->modem;
				$this->contract = $this->modem->contract;
			}

			// entry point is phonebookentry
			$phonebookentry_id = \Input::get('phonebookentry_id', null);
			if (!is_null($phonebookentry_id)) {
				$this->phonebookentry = PhonebookEntry::findOrFail($phonebookentry_id);
			}

			// get related models
			if (!is_null($this->phonebookentry)) {
				$this->phonenumbermanagement = $this->phonebookentry->phonenumbermanagement;
				$this->phonenumber= $this->phonenumbermanagement->phonenumber;
				$this->mta = $this->phonenumber->mta;
				$this->modem = $this->mta->modem;
				$this->contract = $this->modem->contract;
			}

		}
		// build relations starting with model contract
		elseif (($level == 'contract') && (!is_null($model))) {
			$this->contract = $model;
			$this->mta = new Mta();
			$this->modem = new Modem();
			$this->phonenumbermanagement = new PhonenumberManagement();
			$this->phonenumber = new Phonenumber();
			$this->phonebookentry = new PhonebookEntry();
		}
		// build relations starting with model phonenumbermanagement
		elseif (($level == 'phonenumbermanagement') && !is_null($model)) {
			$this->phonenumbermanagement = $model;
			$this->phonenumber = $this->phonenumbermanagement->phonenumber;
			$this->phonebookentry = new PhonebookEntry();
			$this->mta = $this->phonenumber->mta;
			$this->modem = $this->mta->modem;
			$this->contract = $this->modem->contract;
		}
		// build relations starting with model phonenumber
		elseif (($level == 'phonenumber') && !is_null($model)) {
			$this->phonenumbermanagement = new PhonenumberManagement();
			$this->phonebookentry = new PhonebookEntry();
			$this->phonenumber = $model;
			$this->mta = $this->phonenumber->mta;
			$this->modem = $this->mta->modem;
			$this->contract = $this->modem->contract;
		}
		// build relations starting with model phonebookentry
		elseif (($level == 'phonebookentry') && !is_null($model)) {
			$this->phonebookentry = $model;
			$this->phonenumbermanagement = $this->phonebookentry->phonenumbermanagement;
			$this->phonenumber = $this->phonenumbermanagement->phonenumber;
			$this->mta = $this->phonenumber->mta;
			$this->modem = $this->mta->modem;
			$this->contract = $this->modem->contract;
		}
		// invalid params: this will cause a crash
		else {
			if (is_null($model)) {
				throw new \UnexpectedValueException('No model given');
			}
			else {
				throw new \UnexpectedValueException('Value '.$level.' not allowed for param $level');
			}
		}

	}

	/**
	 * Used to extract error messages from returned XML.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $xml XML to extract error information from
	 * @return error codes and messages in array
	 */
	public function get_error_messages($raw_xml) {

		$data = array();

		$xml = new \SimpleXMLElement($raw_xml);

		foreach ($xml->response_error as $response_error) {
			$error = array(
				'status' => (string) $response_error->status,
				'message' => (string) $response_error->message,
			);
			array_push($data, $error);
			foreach ($response_error->nested_errors as $nested_error) {
				$error = array(
					'status' => (string) $nested_error->status,
					'message' => (string) $nested_error->message
				);
				array_push($data, $error);
			}
		}

		// Workaround for malformed error xml (<hash><[status|error]></hash
		if (strpos($raw_xml, '<hash') !== False) {
			$error = array(
				'status' => $xml->status,
				'message' => $xml->error,
			);
			array_push($data, $error);
		}

		return $data;
	}


	/**
	 * Create a xml object containing only the top level element
	 * This is the skeleton for the final XML
	 *
	 * @param $job job to create xml for
	 */
	protected function _create_base_xml_by_topic($job) {

		// to create simplexml object we first need a string containing valid xml
		// also the prolog should be given; otherwise SimpleXML will not put the
		// attribute “encoding” in…
		$xml_prolog = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml_root = '<'.$job.' />';
		$initial_xml = $xml_prolog.$xml_root;

		// this is the basic xml object which will be extended by other methods
		$this->xml = new \SimpleXMLElement($initial_xml);

	}

	/**
	 * Set default values for each job
	 * This should later become obsolete or be filled from the database. For
	 * now we use hardcoded defaults
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job to do
	 *
	 * @return array with defaults for the current job
	 */
	protected function _get_defaults_by_topic($topic) {

		// set defaults if used by job
		$defaults = array(
			'contract_data' => array(
				/* 'porting' => 'MISSING', */
				'phonebookentry_fax' => 0,
				'phonebookentry_reverse_search' => 1,
			),
		);

		// return the defaults or empty array
		if (!array_key_exists($topic, $defaults)) {
			return array();
		}
		else {
			return $defaults[$topic];
		}
	}

	/**
	 * Build the xml extending the basic version.
	 * This will call a method for each second level node, depending on the
	 * given topic. The behavior is controlled by the array $second_level_nodes
	 * which is the mapping between the topic and the xml to create
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job to do
	 */
	protected function _create_final_xml_by_topic($job) {

		// these elements are used to group the information
		// e.g. in reseller_identifier man will put username and password for
		// authentication against the API
		$second_level_nodes = array();

			/* 'blacklist_create_entry' => array( */
			/* 	'reseller_identifier', */
			/* ), */

			/* 'blacklist_delete_entry' => array( */
			/* 	'reseller_identifier', */
			/* ), */

		$second_level_nodes['blacklist_get'] = array(
			'reseller_identifier',
			'callnumber_identifier',
			'blacklist_data',
		);

			/* 'calllog_delete' => array( */
			/* 	'reseller_identifier', */
			/* ), */

			/* 'calllog_delete_entry' => array( */
			/* 	'reseller_identifier', */
			/* ), */

			/* 'calllog_get' => array( */
			/* 	'reseller_identifier', */
			/* ), */

		$second_level_nodes['calllog_get_status'] = array(
			'reseller_identifier',
			'customer_identifier',
		);

		$second_level_nodes['configuration_get'] = array(
			'reseller_identifier',
			'customer_identifier',
			'callnumber_identifier',
		);

			/* 'configuration_update' => array( */
			/* 	'reseller_identifier', */
			/* ), */

			/* 'contract_change_method' => array( */
			/* 	'reseller_identifier', */
			/* ), */

			/* 'contract_change_sla' => array( */
			/* 	'reseller_identifier', */
			/* ), */

		$second_level_nodes['contract_change_tariff'] = array(
			'reseller_identifier',
			'contract_identifier',
			'tariff_data',
		);

		$second_level_nodes['contract_change_variation'] = array(
			'reseller_identifier',
			'contract_identifier',
			'variation_data',
		);

		$second_level_nodes['contract_create'] = array(
			'reseller_identifier',
			'customer_identifier',
			'customer_data',
			'contract_data',
			// in this first step we do not create phonenumbers within
			// the contract
			// instead: create each phonenumber in separate step (voipaccount_create)
			/* 'subscriber_data', */
		);
		if ($this->api_version >= 1.4) {
			array_push($second_level_nodes['contract_create'], 'installation_address_data');
		}

			/* 'contract_get_reference' => array( */
			/* 	'reseller_identifier', */
			/* ), */

		$second_level_nodes['contract_get_voice_data'] = array(
			'reseller_identifier',
			'contract_identifier',
		);

			/* 'contract_lock' => array( */
			/* 	'reseller_identifier', */
			/* ), */

		// not needed atm ⇒ if the last phonenumber is terminated the contract will automatically be deleted
		$second_level_nodes['contract_terminate'] = array(
			'reseller_identifier',
			'contract_identifier',
			'contract_termination_data',
		);

			/* 'contract_unlock' => array( */
			/* 	'reseller_identifier', */
			/* ), */

			/* 'customer_get_reference' => array( */
			/* 	'reseller_identifier', */
			/* ), */

		$second_level_nodes['customer_update'] = array(
			'reseller_identifier',
			'customer_identifier',
			'customer_data',
		);

		$second_level_nodes['misc_get_free_numbers'] = array(
			'reseller_identifier',
			'filter_data',
		);

		$second_level_nodes['misc_get_orders_csv'] = array(
			'reseller_identifier',
		);

		$second_level_nodes['misc_get_usage_csv'] = array(
			'reseller_identifier',
		);

		$second_level_nodes['misc_ping'] = array(
			'reseller_identifier',
		);

			/* 'order_add_mgcp_details' => array( */
			/* 	'reseller_identifier', */
			/* ), */

		$second_level_nodes['order_cancel'] = array(
			'reseller_identifier',
			'order_identifier',
		);

		$second_level_nodes['order_create_attachment'] = array(
			'reseller_identifier',
			'order_identifier',
			'attachment_data',
		);

		$second_level_nodes['order_get_status'] = array(
			'reseller_identifier',
			'order_identifier',
		);

		$second_level_nodes['phonebookentry_create'] = array(
			'reseller_identifier',
			'contract_identifier',
			'callnumber_identifier',
			'phonebookentry_data',
		);

		$second_level_nodes['phonebookentry_delete'] = array(
			'reseller_identifier',
			'contract_identifier',
			'callnumber_identifier',
		);

		$second_level_nodes['phonebookentry_get'] = array(
			'reseller_identifier',
			'contract_identifier',
			'callnumber_identifier',
		);

		$second_level_nodes['voip_account_create'] = array(
			'reseller_identifier',
			'contract_identifier',
			'account_data',
			'subscriber_data',
		);

		$second_level_nodes['voip_account_terminate'] = array(
			'reseller_identifier',
			'contract_identifier',
			'callnumber_identifier',
			'accounttermination_data',
		);

		$second_level_nodes['voip_account_update'] = array(
			'reseller_identifier',
			'contract_identifier',
			'callnumber_identifier',
			'callnumber_data',
		);


		// now call the specific method for each second level element
		foreach ($second_level_nodes[$job] as $node) {
			$method_name = "_add_".$node;
			$this->${"method_name"}();
		}
	}

	/**
	 * Adds the login data of the reseller to the xml
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_reseller_identifier() {

		// TODO: add error handling for not existing keys
		// after defining a project wide policy for this kind of problems
		$username = $_ENV['PROVVOIPENVIA__RESELLER_USERNAME'];
		$password = $_ENV['PROVVOIPENVIA__RESELLER_PASSWORD'];

		$inner_xml = $this->xml->addChild('reseller_identifier');
		$inner_xml->addChild('username', $username);
		$inner_xml->addChild('password', $password);
	}


	/**
	 * Adds an order ID to xml
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_order_identifier() {

		$order_id = \Input::get('order_id', null);
		if (!is_numeric($order_id)) {
			throw new \InvalidArgumentException("order_id has to be numeric");
		}

		$inner_xml = $this->xml->addChild('order_identifier');
		$inner_xml = $inner_xml->addChild('orderid', $order_id);

	}

	/**
	 * Method to add filter data.
	 * This doesn't use method _add_fields – data comes only from $_GET
	 *
	 * @author Patrick Reichel
	 *
	 */
	protected function _add_filter_data() {

		$localareacode = \Input::get('localareacode', null);
		$baseno = \Input::get('baseno', null);

		$inner_xml = $this->xml->addChild('filter_data');

		// no filters: add empty <localareacode /> – if not added there will be an error response from REST-API…
		if (is_null($localareacode)) {
			$inner_xml->addChild('localareacode');
			return;
		}

		// if given: localareacode has to be numeric
		// TODO: error handling
		if (!is_numeric($localareacode)) {
			throw new \InvalidArgumentException("localareacode has to be numeric");
		}

		// localareacode is valid: add filter
		$inner_xml->addChild('localareacode', $localareacode);

		if (is_null($baseno)) {
			return;
		}

		// if given: baseno has to be numeric
		// TODO: error handling
		if (!is_numeric($baseno)) {
			throw new \InvalidArgumentException("baseno has to be numeric");
		}

		// baseno is valid
		$inner_xml->addChild('baseno', $baseno);

	}


	/**
	 * Method to add customer identifier
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_customer_identifier() {

		// needed: our customer number
		$customerno = $this->contract->customer_number();

		$inner_xml = $this->xml->addChild('customer_identifier');
		$inner_xml->addChild('customerno', $customerno);

		$customerreference = $this->contract->customer_external_id;
		// optional: envia customer reference
		if (!is_null($customerreference) && ($customerreference != '')) {
			$inner_xml->addChild('customerreference', $customerreference);
		}

	}

	/**
	 * Method to add customer data
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_customer_data() {

		$inner_xml = $this->xml->addChild('customer_data');

		// mapping xml to database
		$fields = array(
			'salutation' => 'salutation',
			'firstname' => 'firstname',
			'lastname' => 'lastname',
			'street' => 'street',
			'houseno' => 'house_number',
			'zipcode' => 'zip',
			'city' => 'city',
			'birthday' => 'birthday',
			'company' => 'company',
		);

		$this->_add_fields($inner_xml, $fields, $this->contract);
	}

	/**
	 * Method to add contract data
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_contract_data() {

		$inner_xml = $this->xml->addChild('contract_data');

		// add startdate for contract (default: today – there are no costs without phone numbers)
		$inner_xml->addChild('orderdate', date('Y-m-d'));
		$inner_xml->addChild('variation_id', $this->contract->phonetariff_purchase->external_identifier);
		$inner_xml->addChild('tariff', $this->contract->phonetariff_sale->external_identifier);

	}


	/**
	 * Method to add tariff data
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_tariff_data() {

		$inner_xml = $this->xml->addChild('tariff_data');

		// TODO: get dat from Contract->Item (after merging with Nino)
		$inner_xml->addChild('orderdate', date('Y-m-d', strtotime('first day of next month')));

		$inner_xml->addChild('tariff', $this->contract->phonetariff_sale_next->external_identifier);


	}


	/**
	 * Method to add variation data
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_variation_data() {

		$inner_xml = $this->xml->addChild('variation_data');

		$inner_xml->addChild('variation_id', $this->contract->phonetariff_purchase_next->external_identifier);


	}


	/**
	 * Method to add contract termination
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_contract_termination_data() {

		$inner_xml = $this->xml->addChild('contract_termination_data');

		// mapping xml to database
		$fields_contract = array(
			'orderdate' => 'voip_contract_end',
			// TODO: this has to be taken from phonenumbermanagenent
			'carriercode' => null,
		);

		$this->_add_fields($inner_xml, $fields_contract, $this->contract);

	}


	/**
	 * Method to add subscriber data
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_subscriber_data() {

		// subscriber data contains the current “owner” of the number ⇒ this tag is only needed if a phonenumber shall be ported
		$porting = boolval($this->phonenumbermanagement->porting_in);
		if (!$porting) {
			return;
		}

		$inner_xml = $this->xml->addChild('subscriber_data');

		// mapping xml to database
		$fields_subscriber = array(
			'company' => 'subscriber_company',
			'department' => 'subscriber_department',
			'salutation' => 'subscriber_salutation',
			'firstname' => 'subscriber_firstname',
			'lastname' => 'subscriber_lastname',
			'street' => 'subscriber_street',
			'zipcode' => 'subscriber_zip',
			'city' => 'subscriber_city',
		);

		$this->_add_fields($inner_xml, $fields_subscriber, $this->phonenumbermanagement);

	}


	/**
	 * Method to add account data
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_account_data() {

		$inner_xml = $this->xml->addChild('account_data');

		$fields_account = array(
			'porting' => 'porting_in',
			'orderdate' => 'activation_date',
		);

		$this->_add_fields($inner_xml, $fields_account, $this->phonenumbermanagement);
		// add callnumbers
		$this->_add_callnumbers($inner_xml);

	}


	/**
	 * Method to add  callnumbers
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_callnumbers($xml) {

		$inner_xml = $xml->addChild('callnumbers');

		// TODO: this contains callnumber_single_data, callnumber_range_data or callnumber_new_data objects
		// in this first step we only implement callnumber_single_data
		$this->_add_callnumber_single_data($inner_xml);

	}


	/**
	 * Method to add data for a single callnumber
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_callnumber_single_data($xml) {

		$inner_xml = $xml->addChild('callnumber_single_data');

		$fields = array(
			'localareacode' => 'prefix_number',
			'baseno' => 'number',
		);

		$this->_add_fields($inner_xml, $fields, $this->phonenumber);

		// special handling of trc_class needed (comes from external table)
		$trc_class = TRCClass::find($this->phonenumbermanagement->trcclass)->trc_id;
		$inner_xml->addChild('trc_class', $trc_class);

		// special handling for incoming porting needed (comes from external table)
		$carrier_in = CarrierCode::find($this->phonenumbermanagement->carrier_in)->carrier_code;
		// on porting: check if valid CarrierIn chosen
		if (boolval($this->phonenumbermanagement->porting_in)) {
			if (!CarrierCode::is_valid($carrier_in)) {
				throw new \InvalidArgumentException('ERROR: '.$carrier_code.' is not a valid carrier_code');
			}
			$inner_xml->addChild('carriercode', $carrier_in);
		}
		// if no porting (new number): CarrierIn has to be D057 (EnviaTEL) (API 1.4 and higher)
		else {
			if ($this->api_version >= 1.4) {
				if ($carrier_in != 'D057') {
					throw new \InvalidArgumentException('ERROR: If no incoming porting: Carriercode has to be D057 (EnviaTEL)');
				}
				$carrier_in = 'D057';
				$inner_xml->addChild('carriercode', $carrier_in);
			}
		}

		// in API 1.4 and higher we also need the EKP code for incoming porting
		if ($this->api_version >= 1.4) {

			if (boolval($this->phonenumbermanagement->porting_in)) {
				$ekp_in = EkpCode::find($this->phonenumbermanagement->ekp_in)->ekp_code;
				$inner_xml->addChild('ekp_code', $ekp_in);
			}
		}

		$this->_add_sip_data($inner_xml->addChild('method'));
	}


	/**
	* Method to add data for a callnumber.
	* This is different from _add_callnumber_single_data – so we have to implement again…
	*
	* @author Patrick Reichel
	*/
	protected function _add_callnumber_data() {

		$inner_xml = $this->xml->addChild('callnumber_data');

		// TODO: change to date selection instead of performing changes today?
		$inner_xml->addChild('orderdate', date("Y-m-d"));

		// special handling of trc_class needed (comes from external table)
		$trc_class = TRCClass::find($this->phonenumbermanagement->trcclass)->trc_id;
		$inner_xml->addChild('trc_class', $trc_class);

		$this->_add_sip_data($inner_xml->addChild('method'));
	}

	/**
	 * Method to add sip data.
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_sip_data($xml) {

		$inner_xml = $xml->addChild('sip_data');

		$fields = array(
			'username' => 'username',
			'password' => 'password',
		);

		// Envia API throws error if <sipdomain nil="true" /> is given…
		if (boolval($this->phonenumber->sipdomain)) {
			$fields['sipdomain'] = 'sipdomain';
		}

		$this->_add_fields($inner_xml, $fields, $this->phonenumber);
	}


	/**
	 * Method to add  callnumber identifier
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_callnumber_identifier() {

		$inner_xml = $this->xml->addChild('callnumber_identifier');

		$fields = array(
			'localareacode' => 'prefix_number',
			'baseno' => 'number',
		);

		$this->_add_fields($inner_xml, $fields, $this->phonenumber);
	}


	/**
	 * Method to add account termination data
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_accounttermination_data() {

		$inner_xml = $this->xml->addChild('accounttermination_data');

		$fields = array(
			'orderdate' => 'deactivation_date',
		);

		$this->_add_fields($inner_xml, $fields, $this->phonenumbermanagement);

		// handle outgoing porting
		if (boolval($this->phonenumbermanagement->porting_out)) {
			$carrier_out = CarrierCode::find($this->phonenumbermanagement->carrier_out)->carrier_code;
			if (CarrierCode::is_valid($carrier_out)) {
				$inner_xml->addChild('carriercode', $carrier_out);
			}
			else {
				throw new \InvalidArgumentException('ERROR: '.$carrier_code.' is not a valid carrier_code');
			}
		}
		else {
			$inner_xml->addChild('carriercode');
		}
	}


	/**
	 * Method to add blacklist data
	 * This is a special case as the direction for the request is not coming from database but from GET!
	 *
	 * @author Patrick Reichel
	 *
	 * @throws UnexpectedValueException if GET param envia_blacklist_get_direction is not in [in|out]
	 */
	protected function _add_blacklist_data() {

		$direction = strtolower(\Input::get('envia_blacklist_get_direction'));
		$valid_directions = ['in', 'out'];

		if (!in_array($direction, $valid_directions)) {
			throw new \UnexpectedValueException('envia_blacklist_get_direction has to be in ['.implode('|', $valid_directions).']');
		}

		$inner_xml = $this->xml->addChild('blacklist_data');
		$inner_xml->addChild('direction', $direction);
	}


	/**
	 * Method to add contract identifier
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_contract_identifier() {

		$inner_xml = $this->xml->addChild('contract_identifier');

		// mapping xml to database
		$fields_contract_identifier = array(
			'contractreference' => 'contract_external_id',
		);

		$this->_add_fields($inner_xml, $fields_contract_identifier, $this->contract);
	}


	/**
	 * Method to add attachment
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_attachment_data() {

		$enviaorderdocument_id = \Input::get('enviaorderdocument_id');
		$enviaorder_id = \Input::get('order_id');

		$enviaorderdocument = EnviaOrderDocument::findOrFail($enviaorderdocument_id);

		if ($enviaorderdocument->enviaorder->orderid != $enviaorder_id) {
			throw new \InvalidArgumentException('Given order_id ('.$enviaorder_id.') not correct for given enviaorderdocument');
		}
		if (boolval($enviaorderdocument->upload_order_id)) {
			throw new \InvalidArgumentException('Given document has aleady been uploaded');
		}

		$filename = $enviaorderdocument->filename;
		$basepath = EnviaOrderDocument::$document_base_path;
		$contract_id = $enviaorderdocument->enviaorder->contract_id;

		$filepath = $basepath.'/'.$contract_id.'/'.$filename;

		$file_content_raw = \Storage::get($filepath);

		$file_content_base64 = base64_encode($file_content_raw);

		// get MIME type
		$mime_type = $enviaorderdocument->mime_type;


		$inner_xml = $this->xml->addChild('attachment_data');

		$inner_xml->addChild('contenttype', $mime_type);
		$inner_xml->addChild('documenttype', $enviaorderdocument->document_type);
		$inner_xml->addChild('content', $file_content_base64);

	}


	/**
	 * Method to add phonebookentry data
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_phonebookentry_data() {

		$inner_xml = $this->xml->addChild('phonebookentry_data');

		$fields= array(
			'lastname' => 'lastname',
			'firstname' => 'firstname',
			'company' => 'company',
			'noble_rank' => 'noble_rank',
			'nobiliary_particle' => 'nobiliary_particle',
			'academic_degree' => 'academic_degree',
			'other_name_suffix' => 'other_name_suffix',
			'business' => 'business',
			'street' => 'street',
			'houseno' => 'houseno',
			'zipcode' => 'zipcode',
			'city' => 'city',
			'urban_district' => 'urban_district',
			'usage' => 'number_usage',
			'publish_in_print_media' => 'publish_in_print_media',
			'publish_in_electronic_media' => 'publish_in_electronic_media',
			'directory_assistance' => 'directory_assistance',
			'entry_type' => 'entry_type',
			'reverse_search' => 'reverse_search',
			'publish_address' => 'publish_address',
			'tag' => 'tag',
		);

		$this->_add_fields($inner_xml, $fields, $this->phonebookentry);
	}

	/**
	 * Method to add fields to xml node
	 *
	 * @author Patrick Reichel
	 *
	 * @param $xml SimpleXML to add fields to
	 * @param $fields mapping xml node to database field(s) (key is xml node, value is database field as string or array containing all database fields to use plus concatenator as last entry)
	 * @param &$model reference to model to use
	 */
	protected function _add_fields($xml, $fields, &$model) {

		// lambda func to add the data to xml
		$add_func = function($xml, $xml_field, $payload) {
			$cur_node = $xml->addChild($xml_field, $payload);
			if ((is_null($payload)) || ($payload === "")) {
				$cur_node->addAttribute('nil', 'true');
			};
		};

		// process db data
		foreach ($fields as $xml_field => $db_field) {
			// single database field
			if (is_string($db_field)) {
				$payload = $model->$db_field;
			}
			// concated fields; last element is the string used to concat fields
			elseif (is_array($db_field)) {
				$concatenator = array_pop($db_field);
				$tmp = array();
				foreach ($db_field as $tmp_field) {
					array_push($tmp, $model->$tmp_field);
				}
				$payload = implode($concatenator, $tmp);
			}
			else {
				throw new \UnexpectedValueException('$db_field needs to be string or array, '.gettype($db_field).' given');
			}
			$add_func($xml, $xml_field, $payload);
		}

		// get the default values for the current node
		$defaults = $this->_get_defaults_by_topic($xml->getName());

		// process defaults (for fields not filled yet)
		foreach ($defaults as $xml_field => $payload) {
			if (array_search($xml_field, $fields) === False) {
				$add_func($xml, $xml_field, $payload);
			}
		}
	}


	/**
	 * This handles xml data returned by successfully performed API requests.
	 * Action to do depends on the chosen job
	 *
	 * @author Patrick Reichel
	 */
	public function process_envia_data($job, $data) {

		// special header for order_get_status 404 response
		if (($job == 'order_get_status') && ($data['status'] == 404)) {
			$out = '<h4>Error (HTTP status is '.$data['status'].')</h4>';
		}
		else {
			$out = '<h4>Success (HTTP status is '.$data['status'].')</h4>';
		}

		$raw_xml = $data['xml'];
		$xml = new \SimpleXMLElement($raw_xml);

		$method = '_process_'.$job.'_response';
		$out = $this->${"method"}($xml, $data, $out);

		return $out;
	}


	/**
	 * Ping successful message.
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_misc_ping_response($xml, $data, $out) {

		if ($xml->pong == "pong") {
			$out .= "<h5>All works fine</h5>";
		}
		else {
			$out .= "Something went wrong'";
		}

		return $out;

	}


	/**
	 * Extract free numbers and show them
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_misc_get_free_numbers_response($xml, $data, $out) {

		$out .= "<h5>Free numbers";

		// localareacode filter set?
		if ($local_filter = \Input::get('localareacode', False)) {
			$out .= " using filter ".$local_filter."/";

			// show basenumber filter if set
			$baseno_filter = \Input::get('baseno', "");
			$out .= $baseno_filter."*";
		}


		$out .= "</h5>";

		$free_numbers = array();
		foreach ($xml->numbers->number as $number) {
			array_push($free_numbers, $number->localareacode.'/'.$number->baseno);
		}
		sort($free_numbers, SORT_NATURAL);

		$out .= implode('<br>', $free_numbers);

		return $out;
	}


	/**
	 * Process data after successful contract creation
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_contract_create_response($xml, $data, $out) {

		// update contract
		$this->contract->customer_external_id = $xml->customerreference;
		$this->contract->contract_external_id = $xml->contractreference;
		$this->contract->contract_ext_creation_date = date('Y-m-d H:i:s');
		$this->contract->save();


		// create enviaorder
		$order_data = array();

		$order_data['orderid'] = $xml->orderid;
		$order_data['customerreference'] = $xml->customerreference;
		$order_data['contractreference'] = $xml->contractreference;
		$order_data['contract_id'] = $this->contract->id;
		$order_data['ordertype'] = 'contract/create';
		$order_data['orderstatus'] = 'initializing';

		$enviaOrder = EnviaOrder::create($order_data);

		// view data
		$out .= "<h5>Contract created (order ID: ".$xml->orderid.")</h5>";

		return $out;
	}


	/**
	 * Process voice data for contract
	 *
	 * @author Patrick Reichel
	 *
	 * @todo: this method will be used to update phonenumber related data (as sip username and password)
	 * @todo: this will be used to update TRCClass – needs testing (not possible ATM because there are no active phonenumbers)
	 */
	protected function _process_contract_get_voice_data_response($xml, $data, $out) {

		$out = "<h5>Voice data for contract</h5>";

		$out .= "Contained callnumber informations:<br>";
		$out .= "<pre>";
		$out .= $this->prettify_xml($data['xml']);
		$out .= "</pre>";

		// extract data
		$callnumbers = $xml->callnumbers;

		foreach ($callnumbers->children() as $type=>$entry) {

			// process single number
			if ($type == 'callnumber_single_data') {

				// find phonenumber object for given phonenumber
				$where_stmt = "prefix_number=".$entry->localareacode." AND number=".$entry->baseno;
				$phonenumber = Phonenumber::whereRaw($where_stmt)->first();

				$phonenumbermanagement = $phonenumber->phonenumbermanagement;

				// update TRCClass
				if (is_numeric($entry)) {
					// remember: trcclass.id != trclass.trc_id (first is local key, second is Envia Id!)
					$trcclass = TRCClass::where('trc_id', $entry['trc_class'])->first();
					$phonenumbermanagement['trcclass'] = $trcclass->id;
					$phonenumbermanagement->save();
				}

				$method = $entry->method;

				// process SIP data
				if (boolval($method->sip_data)) {
					$sip_data = $method->sip_data;

					// update database
					$phonenumber['username'] = $sip_data->username;
					$phonenumber['password'] = $sip_data->password;
					$phonenumber['sipdomain'] = $sip_data->sipdomain;
					$phonenumber->save();
				}
				// process packet cable data
				elseif (boolval($method->mgcp_data)) {

					// TODO: process data for packet cable
					$out .= "<b>TODO: packet cable not yet implemented</b>";
				}
			}
			elseif ($type == 'callnumber_range_data') {

				// TODO: not yet implemented
				$out .= "<b>TODO: handling of callnumber_range_data not yet implemented</b>";
			}
		}

		$out .= "Done.";

		return $out;
	}

	/**
	 * Process data after successful tariff change
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_contract_change_tariff_response($xml, $data, $out) {

		// create enviaorder
		$order_data = array();

		$order_data['orderid'] = $xml->orderid;
		$order_data['contract_id'] = $this->contract->id;
		$order_data['ordertype'] = 'contract/change_tariff';
		$order_data['orderstatus'] = 'initializing';

		$enviaOrder = EnviaOrder::create($order_data);

		// view data
		$out .= "<h5>Tariff change successful (order ID: ".$xml->orderid.")</h5>";

		return $out;
	}

	/**
	 * Process data after successful variation change
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_contract_change_variation_response($xml, $data, $out) {

		// create enviaorder
		$order_data = array();

		$order_data['orderid'] = $xml->orderid;
		$order_data['contract_id'] = $this->contract->id;
		$order_data['ordertype'] = 'contract/change_variation';
		$order_data['orderstatus'] = 'initializing';

		$enviaOrder = EnviaOrder::create($order_data);

		// view data
		$out .= "<h5>Variation change successful (order ID: ".$xml->orderid.")</h5>";

		return $out;
	}

	/**
	 * Process data after successful customer update
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_customer_update_response($xml, $data, $out) {

		// create enviaorder
		$order_data = array();

		$order_data['orderid'] = $xml->orderid;
		$order_data['contract_id'] = $this->contract->id;
		$order_data['ordertype'] = 'customer/update';
		$order_data['orderstatus'] = 'initializing';

		$enviaOrder = EnviaOrder::create($order_data);

		// view data
		$out .= "<h5>Customer updated (order ID: ".$xml->orderid.")</h5>";

		return $out;
	}

	/**
	 * Extract and process order csv.
	 *
	 * According to Envia's Wienecke this method is only for debugging – the answer will only contain voipaccount related orders. Nevertheless we should use this – e.g. for nightly cron checks to detect manually created orders (at least according to a phonenumber).
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_misc_get_orders_csv_response($xml, $data, $out) {

		// result is base64 encoded csv
		$b64 = $xml->data;
		$csv = base64_decode($b64);

		// csv fieldnames are the first line
		$lines = explode("\n", $csv);
		$csv_headers = str_getcsv(array_shift($lines));

		// array for converted data
		$results = array();

		// process Envia CSV line by line; attach orders to $result array
		foreach ($lines as $result_csv) {
			// check if current line contains data => empty lines will crash at array_combine
			if (boolval($result_csv)) {
				$result = str_getcsv($result_csv);
				$entry = array_combine($csv_headers, $result);
				array_push($results, $entry);
			}
		}

		$out = "";

		foreach ($results as $result) {

			$order_id = $result['orderid'];
			$order = EnviaOrder::where('orderid', $order_id)->first();

			// if order already exists: do nothing (will be updated by order_get_status
			if (!is_null($order)) {
				$out .= '<br>Order '.$order_id.' already exists in database. Skipping.';
				continue;
			}

			// get phonenumber_id and contract_id, add to model instance
			$phonenumber = Phonenumber::whereRaw('prefix_number = '.$result['localareacode'].' AND number = '.$result['baseno'])->first();
			if (is_null($phonenumber)) {
				$tmp = 'Error processing get_orders_csv_response: Phonenumber '.$result['localareacode'].'/'.$result['baseno'].' does not exist. Skipping order '.$order_id;
				\Log::warning($tmp);
				$out .= '<br>'.$tmp;
				continue;
			}
			$result['phonenumber_id'] = $phonenumber->id;
			$result['contract_id'] = $phonenumber->mta->modem->contract->id;

			// create a new Order, add given data to model instance
			$order = EnviaOrder::create($result);

			$out .= '<br>Order '.$order_id.' created.';
		}


		// return different output on cron jobs.
		if ($data['entry_method'] == 'cron') {
			return 'Database updated.';
		}
		else {
			$out .= "<br><br><pre>".$csv."</pre>";
			return $out;
		}
	}


	/**
	 * Extract and process usage csv.
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_misc_get_usage_csv_response($xml, $data, $out) {

		// result is base64 encoded csv
		$b64 = $xml->data;
		$csv = base64_decode($b64);

		// csv fieldnames are the first line
		$lines = explode("\n", $csv);
		$csv_headers = str_getcsv(array_shift($lines));

		// array for converted data
		$results = array();

		// process Envia CSV line by line; attach orders to $result array
		foreach ($lines as $result_csv) {
			// check if current line contains data => empty lines will crash at array_combine
			if (boolval($result_csv)) {
				$result = str_getcsv($result_csv);
				$entry = array_combine($csv_headers, $result);
				array_push($results, $entry);
			}
		}

		$out = "";

		echo "<h1>Not yet implemented in ".__METHOD__."</h1>Check ".__FILE__." (line ".__LINE__.").<h2>Returned csv is:</h2><pre>".$csv."</pre><h2>Extracted data is:</h2>";
		dd($results);

	}


	/**
	 * Process data for a single order.
	 *
	 * This means showing the returned data on screen and updating the database.
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_order_get_status_response($xml, $data, $out) {

		$order_id = \Input::get('order_id');
		$order = EnviaOrder::withTrashed()->where('orderid', '=', $order_id)->first();

		// something went wrong! There is no database entry for the given orderID
		if (is_null($order)) {
			throw new \Exception('ERROR: There is no order with order_id '.$order_id.' in table enviaorders');
		}

		// check status: if 404 then the order doesn't exist at envia ⇒ delete from database
		if ($data['status'] == 404) {

			// check if current order has been manually created
			// if so: hard delete (this order has never been existant)
			if (!boolval($order->ordertype)) {
				$order->forceDelete();
			}
			// else do soft delete (to keep history of orders)
			else {
				$order->delete();
			}

			$out .= "<h5>Order ".$order_id." does not exist:</h5>";
			$out .= "Order has been deleted in database";

			return $out;
		}

		$out = "<h5>Status for order ".$order_id.":</h5>";

		$out .= "<table>";

		if (boolval(sprintf($xml->ordertype_id))) {
			$order->ordertype_id = $xml->ordertype_id;
			$out .= "<tr><td>Ordertype ID: </td><td>".$xml->ordertype_id."</td></tr>";
		}
		else {
			$order->ordertype_id = null;
		}

		if (boolval(sprintf($xml->ordertype))) {
			$order->ordertype = $xml->ordertype;
			$out .= "<tr><td>Ordertype: </td><td>".$xml->ordertype."</td></tr>";
		}
		else {
			$order->ordertype = null;
		}

		if (boolval(sprintf($xml->orderstatus_id))) {
			$order->orderstatus_id = $xml->orderstatus_id;
			$out .= "<tr><td>Orderstatus ID: </td><td>".$xml->orderstatus_id."</td></tr>";
		}
		else {
			$order->orderstatus_id = null;
		}

		if (boolval(sprintf($xml->orderstatus))) {
			$order->orderstatus = $xml->orderstatus;
			$out .= "<tr><td>Orderstatus: </td><td>".$xml->orderstatus."</td></tr>";
		}
		else {
			$order->orderstatus = null;
		}

		if (boolval(sprintf($xml->ordercomment))) {
			$order->ordercomment = $xml->ordercomment;
			$out .= "<tr><td>Ordercomment: </td><td>".$xml->ordercomment."</td></tr>";
		}
		else {
			$order->ordercomment = null;
		}

		if (boolval(sprintf($xml->customerreference))) {
			$order->customerreference = $xml->customerreference;
			$out .= "<tr><td>Customerreference: </td><td>".$xml->customerreference."</td></tr>";
		}
		else {
			$order->customerreference = null;
		}

		if (boolval(sprintf($xml->contractreference))) {
			$order->contractreference = $xml->contractreference;
			$out .= "<tr><td>Contractreference: </td><td>".$xml->contractreference."</td></tr>";
		}
		else {
			$order->contractreference = null;
		}

		if (boolval(sprintf($xml->orderdate))) {
			$order->orderdate = $xml->orderdate;
			// TODO: do we need to store the orderdate in other tables (contract, phonnumber??)
			$out .= "<tr><td>Orderdate: </td><td>".\Str::limit($xml->orderdate, 10,  '')."</td></tr>";
		}
		else {
			$order->orderdate = null;
		}

		$out .= "</table><br>";

		$order->save();

		$out .= "<b>Database updated</b>";
		return $out;
	}

	/**
	 * Process data after successful voipaccount creation
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_voip_account_create_response($xml, $data, $out) {

		// update phonenumbermanagement
		$this->phonenumbermanagement->voipaccount_ext_creation_date = date('Y-m-d H:i:s');
		$this->phonenumbermanagement->save();

		// create enviaorder
		$order_data = array();

		$order_data['orderid'] = $xml->orderid;
		$order_data['contract_id'] = $this->contract->id;
		$order_data['phonenumber_id'] = $this->phonenumber->id;
		$order_data['ordertype'] = 'voip_account/create';
		$order_data['orderstatus'] = 'initializing';

		$enviaOrder = EnviaOrder::create($order_data);

		// view data
		$out .= "<h5>VoIP account created (order ID: ".$xml->orderid.")</h5>";

		return $out;

	}


	/**
	 * Process data after successful voipaccount termination
	 *
	 * @author Patrick Reichel
	 * @todo: This has to be testet – currently there are no accounts we could terminate
	 */
	protected function _process_voip_account_termination_response($xml, $data, $out) {

		// set deletion date (voipaccount_ext_termination_date)

		// check if last account at this contract => if so, set end voip_contract_end

		$out .= "<h5>Attention: Noting happened! Feature still not implemented</h5>";

		return $out;
	}


	/**
	 * Process data after successful order cancel.
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_order_cancel_response($xml, $data, $out) {

		$canceled_enviaorder_id = \Input::get('order_id');

		// get canceled order
		$canceled_enviaorder = EnviaOrder::where('orderid', '=', $canceled_enviaorder_id)->firstOrFail();

		// store cancel order id in database
		$order_data = array();

		$order_data['orderid'] = $xml->orderid;
		$order_data['contract_id'] = $canceled_enviaorder->contract_id;
		$order_data['phonenumber_id'] = $canceled_enviaorder->phonenumber_id;
		$order_data['ordertype'] = 'Stornierung eines Auftrags';
		$order_data['orderstatus'] = 'in Bearbeitung';
		$order_data['related_order_id'] = $canceled_enviaorder_id;
		$order_data['customerreference'] = $canceled_enviaorder->customerreference;
		$order_data['contractreference'] = $canceled_enviaorder->contractreference;

		$enviaOrder = EnviaOrder::create($order_data);

		// delete canceled order
		EnviaOrder::where('orderid', '=', $canceled_enviaorder_id)->delete();

	}


	/**
	 * Process data after successful upload of a file to envia
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_order_create_attachment_response($xml, $data, $out) {

		$enviaorder_id = \Input::get('order_id');
		$related_enviaorder = EnviaOrder::where('orderid', '=', $enviaorder_id)->firstOrFail();
		$related_order_id = $related_enviaorder->id;

		// create new enviaorder
		// the result of sending an attachement related to an order is – right – a new order…
		$order_data = array();

		$order_data['orderid'] = $xml->orderid;
		$order_data['contract_id'] = $related_enviaorder->contract_id;
		$order_data['phonenumber_id'] = $related_enviaorder->phonenumber_id;
		$order_data['ordertype'] = 'order/create_attachment';
		$order_data['orderstatus'] = 'successful';
		$order_data['related_order_id'] = $related_order_id;
		$order_data['customerreference'] = $related_enviaorder->customerreference;
		$order_data['contractreference'] = $related_enviaorder->contractreference;

		$enviaOrder = EnviaOrder::create($order_data);

		// and instantly (soft)delete this order – trying to get order/get_status for the current order results in a 404…
		// I love this API!!
		EnviaOrder::where('orderid', '=', $xml->orderid)->delete();

		// update enviaordertables => store id of order id of upload
		$enviaorderdocument = EnviaOrderDocument::findOrFail(\Input::get('enviaorderdocument_id', null));
		$enviaorderdocument['upload_order_id'] = $xml->orderid;
		$enviaorderdocument->save();

		$out .= "<h5>File uploaded successfully.</h5>";

		return $out;

	}


	/**
	 * Process data after successful creation/change of a phonebook entry
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_phonebookentry_create_response($xml, $data, $out) {

		$out = "";

		echo "<h1>Not yet implemented in ".__METHOD__."</h1>Check ".__FILE__." (line ".__LINE__.")<h2>Returned XML is:</h2>";
		dd($xml);
	}


	/**
	 * Process data after successful deletion of a phonebook entry
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_phonebookentry_delete_response($xml, $data, $out) {

		$out = "";

		echo "<h1>Not yet implemented in ".__METHOD__."</h1>Check ".__FILE__." (line ".__LINE__.")<h2>Returned XML is:</h2>";
		dd($xml);
	}


	/**
	 * Process data after successful creation/change of a phonebook entry
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_phonebookentry_get_response($xml, $data, $out) {

		$out = "";

		echo "<h1>Not yet implemented in ".__METHOD__."</h1>Check ".__FILE__." (line ".__LINE__.")<h2>Use returned data to create new or update existing phonebookentry</h2><h2>Returned XML is:</h2>";
		dd($xml);
	}
}
