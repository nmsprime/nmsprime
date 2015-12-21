<?php

namespace Modules\ProvVoipEnvia\Entities;

use Modules\ProvBase\Entities\Contract;
use Modules\ProvVoip\Entities\Phonenumber;
use Modules\ProvVoip\Entities\Mta;
use Modules\ProvBase\Entities\Modem;

// Model not found? execute composer dump-autoload in lara root dir
class ProvVoipEnvia extends \BaseModel {

	/**
	 * Generate the XML used for communication against Envia API
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job to do
	 * @data $data for which model, e.g. the xml should be build?
	 *
	 * @return XML
	 */
	public function get_xml($job) {

		$this->_get_model_data($job);

		$this->_create_base_xml_by_topic($job);
		$this->_create_final_xml_by_topic($job);

		return $this->xml->asXML();
	}


	/**
	 * Get all the data needed for this job.
	 * This will get the data for the current and all parent models (e.g. contract for phonenumber)
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_model_data() {

		$contract = null;
		$modem = null;
		$mta = null;
		$phonenumber = null;
		$phonenumbermanagement = null;

		// entry point to database is contract
		$contract_id = \Input::get('contract_id', null);
		if (!is_null($contract_id)) {
			$contract = Contract::findOrFail($contract_id);
		}

		// entry point to database is phonenumber
		$phonenumber_id = \Input::get('phonenumber_id', null);
		if (!is_null($phonenumber_id)) {
			$phonenumber = Phonenumber::findOrFail($phonenumber_id);
		}

		// get related models
		if (!is_null($phonenumber)) {
			$mta = $phonenumber->mta;
			$modem = $mta->modem;
			$contract = $modem->contract;
			$phonenumbermanagement = $phonenumber->phonenumbermanagement;
			crash!
		}

		// apply to class variables
		$this->contract = $contract;
		$this->mta = $mta;
		$this->modem = $modem;
		$this->phonenumber = $phonenumber;
		$this->phonenumbermanagement = $phonenumbermanagement;

	}

	/**
	 * Used to extract error messages from returned XML.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $xml XML to extract error information from
	 */
	public function get_error_messages($xml) {

		$data = array();

		$xml = new \SimpleXMLElement($xml);

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

		return $data;
	}


	/**
	 * Create a xml object containing only the top level element
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
				'variation_id' => 'MISSING',
				/* 'porting' => 'MISSING', */
				'tariff' => 'MISSING',
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
		$second_level_nodes = array(
			'blacklist_create_entry' => array(
				'reseller_identifier',
			),
			'blacklist_delete_entry' => array(
				'reseller_identifier',
			),
			'blacklist_get' => array(
				'reseller_identifier',
			),
			'calllog_delete' => array(
				'reseller_identifier',
			),
			'calllog_delete_entry' => array(
				'reseller_identifier',
			),
			'calllog_get' => array(
				'reseller_identifier',
			),
			'configuration_get' => array(
				'reseller_identifier',
			),
			'configuration_update' => array(
				'reseller_identifier',
			),
			'contract_change_method' => array(
				'reseller_identifier',
			),
			'contract_change_sla' => array(
				'reseller_identifier',
			),
			'contract_change_tariff' => array(
				'reseller_identifier',
			),
			'contract_change_variation' => array(
				'reseller_identifier',
			),
			'contract_create' => array(
				'reseller_identifier',
				'customer_identifier',
				'customer_data',
				'contract_data',
				// in this first step we do not create phonenumbers within
				// the contract
				/* 'subscriber_data', */
			),
			'contract_get_reference' => array(
				'reseller_identifier',
			),
			'contract_get_voice_data' => array(
				'reseller_identifier',
			),
			'contract_lock' => array(
				'reseller_identifier',
			),
			'contract_terminate' => array(
				'reseller_identifier',
			),
			'contract_unlock' => array(
				'reseller_identifier',
			),
			'customer_get_reference' => array(
				'reseller_identifier',
			),
			'customer_update' => array(
				'reseller_identifier',
			),
			'misc_get_free_numbers' => array(
				'reseller_identifier',
				'filter_data',
			),
			'misc_get_orders_csv' => array(
				'reseller_identifier',
			),
			'misc_get_usage_csv' => array(
				'reseller_identifier',
			),
			'misc_ping' => array(
				'reseller_identifier',
			),
			'order_add_mgcp_details' => array(
				'reseller_identifier',
			),
			'order_cancel' => array(
				'reseller_identifier',
			),
			'order_create_attachment' => array(
				'reseller_identifier',
			),
			'order_get_status' => array(
				'reseller_identifier',
			),
			'phonebookentry_create' => array(
				'reseller_identifier',
			),
			'phonebookentry_delete' => array(
				'reseller_identifier',
			),
			'phonebookentry_get' => array(
				'reseller_identifier',
			),
			'voip_account_create' => array(
				'reseller_identifier',
				'contract_identifier',
				'account_data',
				'subscriber_data',
			),
			'voip_account_terminate' => array(
				'reseller_identifier',
			),
			'voip_account_update' => array(
				'reseller_identifier',
			),
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
	 * @author Patrick Reichecl
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
	 * Method to add filter data.
	 *
	 * @author Patrick Reichel
	 *
	 */
	protected function _add_filter_data() {

		$localareacode = \Input::get('localareacode', null);
		$baseno = \Input::get('baseno', null);

		// no filters: do nothing
		if (is_null($localareacode)) {
			return;
		}

		// TODO: error handling
		if (!is_numeric($localareacode)) {
			throw \InvalidArgumentException("localareacode has to be numeric");
		}

		// localareacode is valid: add filter
		$inner_xml = $this->xml->addChild('filter_data');
		$inner_xml->addChild('localareacode', $localareacode);

		if (is_null($baseno)) {
			return;
		}

		// TODO: error handling
		if (!is_numeric($baseno)) {
			throw \InvalidArgumentException("baseno has to be numeric");
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
		$customerno = $this->contract->customer_number;

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

		// mapping xml to database
		$fields_contract = array(
			'orderdate' => 'contract_start',
			'phonebookentry_phone' => 'phonebook_entry',
		);

		$this->_add_fields($inner_xml, $fields_contract, $this->contract);

	}

	/**
	 * Method to add subscriber data
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_subscriber_data() {
		// TODO
		$inner_xml = $this->xml->addChild('subscriber_data');
	}


	/**
	 * Method to add account data
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_account_data() {

		$inner_xml = $this->xml->addChild('account_data');

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

		// TODO: this contains callnumber_single_data, callnumber_range_data or callnumber_new_data objects – format unknown…


		// we just add single numbers (and call this as often as needed)…
		$fields = array(
			'callnumber' => array('prefix_number', 'number', ''),
		);

		$this->_add_fields($inner_xml, $fields, $this->phonenumber);
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
	 * Method to add fields to xml node
	 *
	 * @author Patrick Reichel
	 *
	 * @param $xml SimpleXML to add fields to
	 * @param $fields mapping xml node to database field(s) (key is xml node, value is database field as string or array containing all database fields to use plus concatenator as last entry)
	 * @param &$model reference to model to use
	 */
	protected function _add_fields($xml, $fields, &$model) {

		// lambda function to add the data to xml
		$add = function($xml, $xml_field, $payload) {
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
			$add($xml, $xml_field, $payload);
		}

		// get the default values for the current node
		$defaults = $this->_get_defaults_by_topic($xml->getName());

		// process defaults (for fields not filled yet)
		foreach ($defaults as $xml_field => $payload) {
			if (array_search($xml_field, $fields) === False) {
				$add($xml, $xml_field, $payload);
			}
		}
	}

}
