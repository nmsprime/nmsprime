<?php

namespace Modules\ProvVoipEnvia\Entities;

use Modules\ProvBase\Entities\Contract;

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
	 * Get all the data needed for this job
	 *
	 * @author Patrick Reichel
	 */
	protected function _get_model_data() {

		// entry point to database is contract
		$contract_id = \Input::get('contract_id', null);
		if (!is_null($contract_id)) {
			$this->contract = Contract::findOrFail($contract_id);
		}

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
				'subscriber_data',
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
			throw \Exception("localareacode has to be numeric");
		}

		// localareacode is valid: add filter
		$inner_xml = $this->xml->addChild('filter_data');
		$inner_xml->addChild('localareacode', $localareacode);

		if (is_null($baseno)) {
			return;
		}

		// TODO: error handling
		if (!is_numeric($baseno)) {
			throw \Exception("baseno has to be numeric");
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

		// mapping database to xml
		$fields = array(
			'salutation' => 'salutation',
			'firstname' => 'firstname',
			'lastname' => 'lastname',
			'street' => 'street',
			'house_number' => 'houseno',
			'zip' => 'zipcode',
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

		// for this values there (still) exists no db data => fill hardcoded…
		// TODO: this has to be changed!
		$defaults = array(
			'variation_id' => 'MISSING',
			'porting' => 'MISSING',
			'tariff' => 'MISSING',
			'phonebookentry_fax' => 0,
			'phonebookentry_reverse_search' => 1,
		);

		// mapping database to xml
		$fields_contract = array(
			'contract_start' => 'orderdate',
			'phonebook_entry' => 'phonebookentry_phone',
		);

		$this->_add_fields($inner_xml, $fields_contract, $this->contract, $defaults);

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
	 * Method to add  callnumbers
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_callnumbers() {

		$inner_xml = $this->xml->addChild('callnumbers');

		// TODO: this contains callnumber_single_data, callnumber_range_data or callnumber_new_data objects – format unknown…

	}


	/**
	 * Method to add fields to xml node
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_fields($xml, $fields, &$model, $defaults=array()) {

		// lambda function to add the data to xml
		$add = function($xml, $xml_field, $payload) {
			$cur_node = $xml->addChild($xml_field, $payload);
			if ((is_null($payload)) || ($payload === "")) {
				$cur_node->addAttribute('nil', 'true');
			};
		};


		// process db data
		foreach ($fields as $db_field => $xml_field) {

			$payload = $model->$db_field;
			$add($xml, $xml_field, $payload);
		}

		// process defaults (for fields not filled yet)
		foreach ($defaults as $xml_field => $payload) {
			if (array_search($xml_field, $fields) === False) {
				$add($xml, $xml_field, $payload);
			}
		}
	}

}
