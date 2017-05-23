<?php

namespace Modules\ProvVoipEnvia\Entities;

use Log;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvBase\Entities\VoipRelatedDataUpdaterByEnvia;
use Modules\ProvVoip\Entities\Phonenumber;
use Modules\ProvVoip\Entities\PhonenumberManagement;
use Modules\ProvVoip\Entities\PhonebookEntry;
use Modules\ProvVoip\Entities\CarrierCode;
use Modules\ProvVoip\Entities\EkpCode;
use Modules\ProvVoip\Entities\Mta;
use Modules\ProvVoip\Entities\TRCClass;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvVoipEnvia\Entities\EnviaContract;
use Modules\ProvVoipEnvia\Entities\EnviaOrder;
use Modules\ProvVoipEnvia\Entities\EnviaOrderDocument;
use Modules\ProvVoipEnvia\Exceptions\XmlCreationError;
use Modules\ProvVoipEnvia\Http\Controllers\ProvVoipEnviaController;
use App\Exceptions\NotImplementedException;

// Model not found? execute composer dump-autoload in lara root dir
class ProvVoipEnvia extends \BaseModel {


	/**
	 * Constructor.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	public function __construct($attributes = array()) {

		// if not available in .env: set to -1 to not break e.g. “php artisan” command ⇒ thas has to be caught later on
		$v = getenv('PROVVOIPENVIA__REST_API_VERSION');
		if ($v === False) {
			$v = "-1";
		}

		$this->api_version_string = $v;


		// check if sent and received XML shall be stored
		if (array_key_exists('PROVVOIPENVIA__STORE_XML', $_ENV)) {
			$this->xml_storing_enabled = boolval($_ENV['PROVVOIPENVIA__STORE_XML']);
		}
		else {
			$this->xml_storing_enabled = false;
		}

		// this has to be a float value to allow stable version compares ⇒ make some basic tests
		if (!is_numeric($v)) {
			throw new \InvalidArgumentException('PROVVOIPENVIA__REST_API_VERSION in .env has to be a float value (e.g.: 1.4)');
		};

		$this->api_version = $this->_version_string_to_array($this->api_version_string);

		// call \BaseModel's constructor
		parent::__construct($attributes);

	}

	/**
	 * Helper to convert a version string to array
	 * Necessary to compare version numbers properly (e.g. "1.4" < "1.10")!
	 *
	 * @return array similar to Python's sys.version_info (containing three keys: major, minor, micro)
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _version_string_to_array($version) {

		$version = explode('.', $version);
		$version_major = intval($version[0]);

		if (count($version) >= 2) {
			$version_minor = intval($version[1]);
		}
		else {
			$version_minor = 0;
		}

		// level micro is not used ATM ⇒ set to -1 if not given…
		if (count($version) >= 3) {
			$version_micro = intval($version[2]);
		}
		else {
			$version_micro = 0;
		}

		return [
			'major' => $version_major,
			'minor' => $version_minor,
			'micro' => $version_micro,
			];
	}

	/**
	 * Helper to determine the compare level for version numbers depending on the precision of the given param.
	 *
	 * @param $version string containing a version number
	 *
	 * @return 'major' for strings without dots, 'minor' for strings containing one dot, 'micro' else
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _get_api_version_compare_level($version) {

		$dot_count = substr_count($version, ".");

		if ($dot_count == 0) {
			return 'major';
		}

		if ($dot_count == 1) {
			return 'minor';
		}

		// fallback level – there can be no version like 1.4.3.1
		return 'micro';
	}


	/**
	 * Helper to compare a given integer, float or string to the currently used API version
	 *
	 * @return integer
	 *			-1: given version is less than currently used one
	 *			 0: given version equals currently used one
	 *			-1: given version is greater than currently used one
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _compare_to_api_version($version) {

		// cast to string expicitely – later logic expects strings!
		$version = strval($version);

		// get the level to which level we have to compare
		$level = $this->_get_api_version_compare_level($version);

		$version_to_compare = $this->_version_string_to_array($version);

		// in each case compare the major number
		if ($version_to_compare['major'] > $this->api_version['major']) {
			return 1;
		}
		elseif ($version_to_compare['major'] < $this->api_version['major']) {
			return -1;
		}

		// if level is less than major: compare minor number, too
		if (($level == 'minor') || ($level == 'micro')) {
			if ($version_to_compare['minor'] > $this->api_version['minor']) {
				return 1;
			}
			elseif ($version_to_compare['minor'] < $this->api_version['minor']) {
				return -1;
			}
		}

		// if level is micro: compare the micro integers, too
		if ($level == 'micro') {
			if ($version_to_compare['micro'] > $this->api_version['micro']) {
				return 1;
			}
			elseif ($version_to_compare['micro'] < $this->api_version['micro']) {
				return -1;
			}
		}

		// if we end up here we have a match (version numbers are equal to the given level)
		return 0;
	}


	/**
	 * Helper to check if API version equals a given value.
	 *
	 * @param $version number as integer, float or string (e.g. "1.4")
	 * @return bool
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	public function api_version_equals($version) {

		return ($this->_compare_to_api_version($version) == 0);
	}


	/**
	 * Helper to check if API version equals a given value.
	 *
	 * @param $version number as integer, float or string (e.g. "1.4")
	 * @return bool
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	public function api_version_less_than($version) {

		return ($this->_compare_to_api_version($version) == 1);
	}


	/**
	 * Helper to check if API version equals a given value.
	 *
	 * @param $version number as integer, float or string (e.g. "1.4")
	 * @return bool
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	public function api_version_greater_than($version) {

		return ($this->_compare_to_api_version($version) == -1);
	}


	/**
	 * Helper to check if API version equals a given value.
	 *
	 * @param $version number as integer, float or string (e.g. "1.4")
	 * @return bool
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	public function api_version_less_or_equal($version) {

		return ($this->api_version_equals($version) || $this->api_version_less_than($version));
	}


	/**
	 * Helper to check if API version equals a given value.
	 *
	 * @param $version number as integer, float or string (e.g. "1.4")
	 * @return bool
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	public function api_version_greater_or_equal($version) {

		return ($this->api_version_equals($version) || $this->api_version_greater_than($version));
	}


	/**
	 * Helper method to fake XML returns.
	 * This will return a SimpleXML instance which can be used instead a real Envia answer.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _get_xml_fake($xml_string) {

		return new \SimpleXMLElement($xml_string);
	}

	/**
	 * Helper to prettify xml for output on screen.
	 * Use e.g. for debugging.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
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
	 * Get some environmental data and set to instance variables
	 * Mainly this are helper flags that describe the state of the current model instance stack –
	 * e.g describing if there is an active contract or phonenumber…
	 *
	 * @author Patrick Reichel
	 */
	public function extract_environment($model, $view_level) {

		// check if a model is given – if not there is no environment
		if (is_null($model)) {
			return array();
		}

		$this->set_model_data($view_level, $model);

		$phonenumber_id = $this->phonenumber->id;

		if (!is_null($this->phonenumbermanagement) && !is_null($this->phonenumbermanagement->phonebookentry)) {
			$phonebookentry_id = $this->phonenumbermanagement->phonebookentry->id;
		}

		$modem_id = $this->modem->id;
		$contract_id = $this->contract->id;

		// get all phonenumbers related to $this
		if ($this->phonenumber->exists) {
			$phonenumbers = [$this->phonenumber];
		}
		elseif ($this->modem->exists) {
			$phonenumbers = $this->modem->related_phonenumbers();
		}
		elseif ($this->contract->exists) {
			$phonenumbers = $this->contract->related_phonenumbers();
		}
		else {
			// should never happen
			$phonenumbers = [];
		}

		// count the contracts created/terminated
		$contracts_created = [];
		$contracts_terminated = [];
		foreach ($phonenumbers as $_phonenumber) {
			$_ = $_phonenumber->envia_contract_created();
			if ($_) {
				array_push($contracts_created, $_);
			}
			$_ = $_phonenumber->envia_contract_terminated();
			if ($_) {
				array_push($contracts_terminated, $_);
			}
		}
		$contracts_created = array_unique($contracts_created);
		$contracts_terminated = array_unique($contracts_terminated);

		/* d($contracts_created, $contracts_terminated, $phonenumbers); */
		// set the variables
		if (empty($contracts_created)) {
			$this->contract_created = False;
			$this->at_least_one_contract_created = False;
		}
		else {
			$this->contract_created = True;
			$this->at_least_one_contract_created = True;
		}

		if (is_null($this->modem->contract_ext_termination_date)) {
			$this->contract_terminated = False;
		}
		else {
			$this->contract_terminated = True;
		}

		if (count($contracts_created) > count($contracts_terminated)) {
			$this->contract_available = True;
			$this->at_least_one_contract_available = True;
		}
		else {
			$this->contract_available = False;
			$this->at_least_one_contract_available = False;
		}

		if (is_null($this->phonenumber->contract_external_id)) {
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
	 * Get array with all available jobs for given view.
	 * This depends on the view level (e.g. we get no phonenumber related jobs on contract level)
	 * and the  current state of related models (e.g. we only show
	 * job to create a contract if there is no created contract)
	 *
	 * @author Patrick Reichel
	 *
	 * @param $model model instance to get jobs for
	 * @param $view_level depending on the view (contract, phonenumbermanagement, etc.) the result can be different
	 *
	 * @return array containing data for view
	 */
	public function get_jobs_for_view($model, $view_level) {

		$this->extract_environment($model, $view_level);

		// helpers (the model IDs will be appended to most jobs as get params)
		$base = "/lara/admin/provvoipenvia/request/";
		if ($view_level == 'phonenumbermanagement') {
			$contract_id = $model->phonenumber->mta->modem->contract->id;
			$modem_id = $model->phonenumber->mta->modem->id;
			$phonenumber_id = $model->phonenumber_id;
			$phonenumbermanagement_id = $model->id;
			if (!is_null($model->phonebookentry)) {
				$phonebookentry_id = $model->phonebookentry->id;
			}
		}
		elseif ($view_level == 'contract') {
			$contract_id = $model->id;
			$modem_id = null;
			$phonenumbermanagement_id = null;
			$phonenumber_id = null;
			$phonebookentry_id = null;
		}
		elseif ($view_level == 'modem') {
			$contract_id = $model->contract->id;
			$modem_id = $model->id;
			$phonenumbermanagement_id = null;
			$phonenumber_id = null;
			$phonebookentry_id = null;
		}
		elseif ($view_level == 'phonenumber') {
			$contract_id = $model->mta->modem->contract->id;
			$modem_id = $model->mta->modem->id;
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
			$modem_id = $model->phonenumbermanagement->phonenumber->mta->modem->id;
			$phonenumber_id = $model->phonenumbermanagement->phonenumber_id;
			$phonenumbermanagement_id = $model->phonenumbermanagement->id;
			$phonebookentry_id = $model->id;
		}
		else {
			throw new \UnexpectedValueException('param $view_level has to be in [contract|modem|phonenumber|phonenumbermanagement|phonebookentry]');
		}

		// keep original URL ⇒ so we can offer a link to the calling URL (even if there are some redirects in between)
		// we add this as first GET param to each job – this also relieves us from checking if we have to use ? or & in all following params ;-)
		$origin = '?origin='.urlencode(\Request::getUri());

		// add this to all actions that can be performed without extra confirmation
		// can be used for jobs that do not change anything at Envia
		// in other cases this flag will be added to the confirmation link
		$really = '&amp;really=True';


		////////////////////////////////////////
		// misc jobs – available on all levels and without any preconditions
		if (in_array($view_level, ['contract', 'modem', 'phonenumber', 'phonenumbermanagement', 'phonebookentry'])) {
			$ret = array(
				array('class' => 'Misc'),
				array(
					'linktext' => 'Ping Envia API',
					'url' => $base.'misc_ping'.$origin.$really,
					'help' => "Checks if Envia API is reachable and running.",
				),
				array(
					'linktext' => 'Get free numbers',
					'url' => $base.'misc_get_free_numbers'.$origin.$really,
					'help' => "Gets all currently unused numbers from Envia.",
				)
			);

			if ($this->api_version_greater_or_equal("1.7")) {
				array_push($ret, array(
					'linktext' => 'Get values for use in other methods',
					'url' => $base.'misc_get_keys'.$origin.'&amp;keyname=index'.$really,
					'help' => "This method gets e.g. EKP codes, carrier codes, phonebook entry related data, …",
				));
			}

		}


		////////////////////////////////////////
		// contract related jobs
		if (in_array($view_level, ['contract', 'modem', 'phonenumbermanagement'])) {
			array_push($ret, array('class' => 'Customer'));

			// customer data change possible if there is an active contract for this user
			if ($this->at_least_one_contract_available) {
				array_push($ret, array(
					'linktext' => 'Get Envia customer reference',
					'url' => $base.'customer_get_reference'.$origin.'&amp;contract_id='.$contract_id,
					'help' => "Tries to get the Envia ID for this customer"
				));
				array_push($ret, array(
					'linktext' => 'Update customer',
					'url' => $base.'customer_update'.$origin.'&amp;contract_id='.$contract_id,
					'help' => "Pushes changes on customer data to Envia.\nChanges of modem installation address have to be sent separately (using “Relocate contract”)!"
				));
			}
		}

		////////////////////////////////////////
		// modem related jobs
		if (in_array($view_level, ['modem', 'phonenumbermanagement'])) {
			array_push($ret, array('class' => 'Telephone connection (= Envia contract)'));

			// special case contract reference – now stored in phonenumber instead of modem
			if (in_array($view_level, ['phonenumbermanagement'])) {
				// can get reference if phonenumber exists at Envia
				array_push($ret, array(
					'linktext' => 'Get Envia contract reference',
					'url' => $base.'contract_get_reference'.$origin.'&amp;phonenumber_id='.$phonenumber_id,
					'help' => "You can get the Envia reference for a contract using a phonenumber related to this contract",
				));
			};

			// “normal“ jobs
			$phonenumbers_to_create = '&amp;phonenumbers_to_create=';
			array_push($ret, array(
				'linktext' => 'Create contract',
				'url' => $base.'contract_create'.$origin.'&amp;modem_id='.$modem_id.$phonenumbers_to_create,
				'help' => "Creates a Envia contract (= telephone connection)",
			));

			// contract can be relocated if created; available with Envia API version 1.4
			if ($this->contract_created) {
				if ($this->api_version_greater_or_equal("1.4")) {
					array_push($ret, array(
						'linktext' => 'Relocate contract',
						'url' => $base.'contract_relocate'.$origin.'&amp;modem_id='.$modem_id,
						'help' => "Changes (physical) installation address of this modem.\n\nATTENTION: Changes of customer address have to be sent separately (using “Update customer”)!",
					));
				}
			}

			// contract can be terminated if is created and not yet terminated
			// not yet implemented ⇒ a contract will terminated automatically by termination of the last number
			// also this is the more secure way to end a contract: man has explicitely to handle the numbers one by one
			// (this can be important if one number shall be ported and the other not)
			/* if ($this->contract_available) { */
			/* 	array_push($ret, array('linktext' => 'Terminate contract', 'url' => $base.'contract_terminate'.$origin.'&amp;contract_id='.$contract_id)); */
			/* } */

			// can get contract related information if contract is available
			if ($this->contract_available) {
				// here we have to distinct between origin modem and phonenumber
				// ATM we only can handle one contract_id per request – to update multiple contracts per modem we have to be at least in level phonenumber
				if ($this->phonenumber->exists) {
					$id = "phonenumber_id=$phonenumber_id";
				}
				else {
					$id = "modem_id=$modem_id";
				}
				array_push($ret, array(
					'linktext' => 'Get voice data (EXPERIMENTAL – can have unexpected side effects)',
					'url' => $base.'contract_get_voice_data'.$origin.'&amp;'.$id.$really,
					'help' => "Get all phonenumbers and sip data for this modem.",
				));
			}

			// tariff can only be changed if contract exists and a tariff change is wanted
			// TODO: implement checks for current change state; otherwise we get an error from Envia (change into the same tariff is not possible)
			// TODO: this has to be done for each envia contract – this needs to be implemented
			if ($this->contract_available) {
				if (boolval($this->contract->next_voip_id) && boolval($this->contract->voip_id)) {
					if ($this->contract->voip_id != $this->contract->next_voip_id) {
						array_push($ret, array(
							'linktext' => 'Change tariff (EXPERIMENTAL)',
							'url' => $base.'contract_change_tariff'.$origin.'&amp;modem_id='.$modem_id,
							'help' => "Changes the VoIP sales tariff for this modem (=Envia contract).\n\nATTENTION: Has also to be changed for all other modems related to this customer!",
						));
					}
				}
			}

			// variation can only be changed if contract exists and a variation change is wanted
			// TODO: implement checks for current change state; otherwise we get an error from Envia (change into the same variation is not possible)
			// TODO: this has to be done for each envia contract – this needs to be implemented
			if ($this->contract_available) {
				if (boolval($this->contract->next_purchase_tariff) && boolval($this->contract->purchase_tariff)) {
					if ($this->contract->purchase_tariff != $this->contract->next_purchase_tariff) {
						array_push($ret, array(
							'linktext' => 'Change variation (EXPERIMENTAL)',
							'url' => $base.'contract_change_variation'.$origin.'&amp;modem_id='.$modem_id,
							'help' => "Changes the VoIP purchase tariff for this modem (=Envia contract).\n\nATTENTION: Has also to be changed for all other modems related to this customer!",
						));
					}
				}
			}

		}


		////////////////////////////////////////
		// voip account related jobs
		if (in_array($view_level, ['phonenumbermanagement'])) {
			array_push($ret, array('class' => 'Phonenumber (= Envia VoIP account)'));

			// voip account needs a contract
			if (!$this->voipaccount_created && $this->contract_available) {
				array_push($ret, array(
					'linktext' => 'Create VoIP account',
					'url' => $base.'voip_account_create'.$origin.'&amp;phonenumber_id='.$phonenumber_id,
					'help' => "Creates the phonenumber at Envia",
				));
			}

			if ($this->voipaccount_available) {
				array_push($ret, array(
					'linktext' => 'Terminate VoIP account',
					'url' => $base.'voip_account_terminate'.$origin.'&amp;phonenumber_id='.$phonenumber_id,
					'help' => "Terminates the phonenumber at Envia",
				));
			};

			if ($this->voipaccount_available) {
				array_push($ret, array(
					'linktext' => 'Update VoIP account',
					'url' => $base.'voip_account_update'.$origin.'&amp;phonenumber_id='.$phonenumber_id,
					'help' => "Updates phonenumber related data (TRC class, SIP data) at Envia",
				));
			};
		}


		////////////////////////////////////////
		// phonebookentry related jobs
		if (in_array($view_level, ['phonenumbermanagement', 'phonebookentry'])) {

			array_push($ret, array('class' => 'Phonebook entry'));

			// only if there is a phonenumber to add the entry to
			if ($this->voipaccount_available) {
				array_push($ret, array(
					'linktext' => 'Get phonebook entry',
					'url' => $base.'phonebookentry_get'.$origin.'&amp;phonenumbermanagement_id='.$phonenumbermanagement_id,
					'help' => "Gets the current phonebook entry for this phonenumber.",
				));

				if ($view_level == 'phonebookentry') {
					array_push($ret, array(
						'linktext' => 'Create/change phonebook entry (EXPERIMENTAL)',
						'url' => $base.'phonebookentry_create'.$origin.'&amp;phonebookentry_id='.$phonebookentry_id,
						'help' => "Creates a new or updates an existing phonebook entry for this phonenumber."
					));
				}

				if ($view_level == 'phonebookentry') {
					array_push($ret, array(
						'linktext' => 'Delete phonebook entry (EXPERIMENTAL)',
						'url' => $base.'phonebookentry_delete'.$origin.'&amp;phonebookentry_id='.$phonebookentry_id,
						'help' => "Deletes an existing phonebook entry for this phonenumber."
					));
				}
			}

		}


		////////////////////////////////////////
		// order related jobs
		if (in_array($view_level, ['contract', 'modem', 'phonenumber', 'phonenumbermanagement'])) {
			array_push($ret, array('class' => 'Orders'));
			array_push($ret, array(
				'linktext' => 'Get all phonenumber related orders',
				'url' => $base.'misc_get_orders_csv'.$origin.$really,
				'help' => "Fetches all phonenumber related orders from Envia.\n\nATTENTION: This will not include orders for e.g. changing addresses or tariffs!",
			));

			// order(s) exist if at least one contract has been created
			if ($this->at_least_one_contract_created) {
				array_push($ret, array('class' => 'Related orders (click to get status update)'));
				foreach (EnviaOrder::withTrashed()->where('contract_id', '=', $contract_id)->orderBy("created_at")->get() as $order) {

					// if in view modem: don't show orders for other than the current modem (=Envia contract)
					if (in_array($view_level, ['modem'])) {
						if (boolval($order->modem_id) && $order->modem_id != $modem_id) {
							continue;
						}
					}

					// if in view phonenumber*: don't show phonenumber related orders for other than the current phonenumber
					if (in_array($view_level, ['phonenumber', 'phonenumbermanagement'])) {
						$order_phonenumbers = $order->phonenumbers;
						if (($order_phonenumbers->count() > 0) && (!$order_phonenumbers->contains($phonenumber_id))) {
							continue;
						}
					}

					// create link for this order
					$order_id = $order->orderid;
					$order_type = $order->ordertype;
					$order_status = $order->orderstatus;
					$linktext = $order_id.' – '.$order_type.': <i>'.$order_status.'</i>';
					// stroke soft deleted entries
					// we want to see the whole history (including canceled orders)
					if (boolval($order->deleted_at)) {
						$linktext = '<s>'.$linktext.'</s>';
					}
					// add order (except create_attachements)
					if ($order_type != 'order/create_attachment') {
						// if order is not in final state: add link to get current status
						if (!EnviaOrder::orderstate_is_final($order)) {
							$url = $base.'order_get_status'.$origin.'&amp;order_id='.$order_id.$really;
						}
						else {
							$url = "";
						}
						array_push($ret, array(
							'linktext' => $linktext,
							'url' => $url,
							'help' => "Gets the current state of this order from Envia (if orderstate is not final).",
						));
					}
				}
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
	 * @version 2017-05-10
	 *
	 * @param $job job to do
	 * @param $store created XML (used to deactivate the method e.g. for XML created to be shown only)
	 *
	 * @return XML
	 */
	public function get_xml($job, $store=True) {

		$this->_create_base_xml_by_topic($job);
		$this->_create_final_xml_by_topic($job);

		if ($store) {
			$this->store_xml($job, $this->xml);
		}

		return $this->xml->asXML();
	}

	/**
	 * Helper to save all sent and received XML to HDD for later debugging.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	public function store_xml($context, $xml) {

		// first check if we want to store the xml
		if (!$this->xml_storing_enabled) {
			return;
		}

		// make xml more human readable
		// so later man can faster understand the content; also grepping will be easier
		$dom = new \DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml->asXML());
		$filecontent = $dom->saveXML();


		// create filename (use current datetime as ISO like string with microseconds to avoid filename conflicts)
		// therefore we have to use microtime instead of date('u') (which in every case returns 000000 μs)
		$microseconds = explode(' ', microtime(false))[0];
		$microseconds = str_replace('0.', '-', $microseconds);
		$now = date('Y-m-d__H-i-s').$microseconds;
		$filename = strtolower($now.'____'.$context).'.xml';

		// move uploaded file to document_path (after making directories)
		$path = 'data/provvoipenvia/XML/'.substr($now, 0, 7);
		$filename = $path.'/'.$filename;
		\Storage::makeDirectory($path);
		\Storage::put($filename, $filecontent);
		$absfile = storage_path().'/'.$filename;
		chmod(storage_path().'/app/'.$filename, 0640);

	}


	/**
	 * Get all the data (all related models) needed for this job.
	 * This will get the data for the current and all parent models (e.g. contract for phonenumber) and store as instance variables
	 * To do so we have to differentiate in the job to do
	 *
	 * @author Patrick Reichel
	 *
	 * @param $level current level to work from
	 * @param $model the model to get related models from ($model is of type $level)
	 */
	public function set_model_data($level='', $model=null) {

		// defaults => can be overwritten if there are “real” models in this context
		$this->contract = null;
		$this->modem = null;
		$this->mta = null;
		$this->phonenumber = null;
		$this->phonenumbermanagement = null;
		$this->phonebookentry = null;

		// level is irrelevant (e.g. for creating XML for a given contract_id)
		// this means: the initial model comes from a database search using IDs given by GET/POST/WHATEVER
		// depending on the found model we try to get all clearly related (so to say “parental”) model instances
		// e.g. we can get the related contract for a modem ⇒ use this to overwrite the defaults
		if ($level == '') {

			// entry point to database is contract
			$contract_id = \Input::get('contract_id', null);
			if (!is_null($contract_id)) {
				$this->contract = Contract::findOrFail($contract_id);
			}

			// entry point to database is modem
			$modem_id = \Input::get('modem_id', null);
			if (!is_null($modem_id)) {
				$this->modem = Modem::findOrFail($modem_id);
			}
			// get related models (if modem model exists)
			// in other cases: there are no clear relations
			if (!is_null($this->modem)) {
				$this->contract = $this->modem->contract;
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
		// build relations starting with model modem
		elseif (($level == 'modem') && (!is_null($model))) {
			$this->modem = $model;
			$this->contract = $this->modem->contract;
			$this->mta = new Mta();
			$this->phonenumbermanagement = new PhonenumberManagement();
			$this->phonenumber = new Phonenumber();
			$this->phonebookentry = new PhonebookEntry();
		}
		// build relations starting with model phonenumber
		elseif (($level == 'phonenumber') && !is_null($model)) {
			$this->phonenumber = $model;
			$this->mta = $this->phonenumber->mta;
			$this->modem = $this->mta->modem;
			$this->contract = $this->modem->contract;
			$this->phonenumbermanagement = new PhonenumberManagement();
			$this->phonebookentry = new PhonebookEntry();
		}
		// build relations starting with model phonenumbermanagement
		elseif (($level == 'phonenumbermanagement') && !is_null($model)) {
			$this->phonenumbermanagement = $model;
			$this->phonenumber = $this->phonenumbermanagement->phonenumber;
			$this->mta = $this->phonenumber->mta;
			$this->modem = $this->mta->modem;
			$this->contract = $this->modem->contract;
			$this->phonebookentry = new PhonebookEntry();
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
	 * To be sure we extract all error messages from returned error XML we have to visit each node.
	 * There can be nested_errors in nested_errors in …
	 *
	 * Will change the given $errors array in place
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 *
	 * @param $xml SimpleXMLElement to be investigated
	 * @param &$errors container array to collect all extracted errors in
	 */
	protected function _get_error_messages_recurse($xml, &$errors) {

		// if current node is an error: process data
		if (
			($xml->getName() == 'response_error')
			||
			($xml->getName() == 'nested_error')
		) {
			$error = array(
				'status' => (string) $xml->status?((string) $xml->status):'n/a',
				'message' => (string) $xml->message?((string) $xml->message):'n/a'
			);
			array_push($errors, $error);
		}

		// Workaround for malformed error xml (<hash><[status|error]></hash>)
		if ($xml->getName() == 'hash') {
			$error = array(
				'status' => (string) $xml->status?((string) $xml->status):'n/a',
				'message' => '',
			);
			if ($xml->message) {
				$error['message'] .= (string) $xml->message;
			}
			if ($xml->error) {
				$error['message'] .= (string) $xml->error;
			}
			if (!$error['message']) {
				$error['message'] == 'n/a';
			}
			array_push($data, $error);
		}

		// stop condition: no more children == leaf node
		if (!$xml->count()) {
			return;
		}

		// call this method for all children
		foreach ($xml as $child) {
			$this->_get_error_messages_recurse($child, $errors);
		}
	}

	/**
	 * Used to extract error messages from returned XML.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 *
	 * @param $raw_xml XML to extract error information from
	 * @return error codes and messages in array
	 */
	public function get_error_messages($raw_xml) {

		$data = array();
		$xml = new \SimpleXMLElement($raw_xml);

		// extract all error messages from XML
		$this->_get_error_messages_recurse($xml, $data);

		return $data;
	}


	/**
	 * Generates array containing all numbers related to current modem.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	public function get_numbers_related_to_modem_for_contract_create() {

		$key_no_mgmt = 'No PhonenumberManagement';
		$key_new_number = 'New number';

		$phonenumbers_on_modem = array(
			$key_no_mgmt => array(),
			$key_new_number => array(),
		);

		foreach ($this->modem->mtas as $mta) {
			foreach ($mta->phonenumbers as $phonenumber) {

				// exclude numbers with contract_external_id ⇒ they are already created
				if (!is_null($phonenumber->contract_external_id)) {
					continue;
				}

				$phonenumbermanagement = $phonenumber->phonenumbermanagement;

				// handle missing management
				if (!$phonenumbermanagement) {
					if (!array_key_exists('–', $phonenumbers_on_modem[$key_no_mgmt])) {
						$phonenumbers_on_modem[$key_no_mgmt]['–'] = array();
					}
					array_push($phonenumbers_on_modem[$key_no_mgmt]['–'], $phonenumber);
					continue;
				}

				$activation_date = $phonenumbermanagement->activation_date ? : "n/a";

				// handle numbers not to be ported (= new number from Envia pool)
				if (!$phonenumbermanagement->porting_in) {
					if (!array_key_exists($activation_date, $phonenumbers_on_modem[$key_new_number])) {
						$phonenumbers_on_modem[$key_new_number][$activation_date] = array();
					}
					array_push($phonenumbers_on_modem[$key_new_number][$activation_date], $phonenumber);
					continue;
				}

				// handle numbers to be ported
				$ekp_code = EkpCode::findOrFail($phonenumbermanagement->ekp_in);
				$ekp_code = 'From '.$ekp_code->company;

				if (!array_key_exists($ekp_code, $phonenumbers_on_modem)) {
					$phonenumbers_on_modem[$ekp_code] = array();
				}

				if (!array_key_exists($activation_date, $phonenumbers_on_modem[$ekp_code])) {
					$phonenumbers_on_modem[$ekp_code][$activation_date] = array();
				}
				array_push($phonenumbers_on_modem[$ekp_code][$activation_date], $phonenumber);
			}
		}

		// bring array in wanted order for display
		$phonenumbers_on_modem = array_reverse($phonenumbers_on_modem);

		return $phonenumbers_on_modem;
	}


	/**
	 * Create a xml object containing only the top level element
	 * This is the skeleton for the final XML
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
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
	 * @version 2017-05-10
	 *
	 * @param $topic job to do
	 *
	 * @return array with defaults for the current job
	 */
	protected function _get_defaults_by_topic($topic) {

		// set defaults if used by job
		$defaults = array(
			'contract_data' => array(
				// set phonebookentry to no by default ⇒ this later can be overwritten by excplicitely creating a phonebookentry
				'phonebookentry_phone' => 0,
				'phonebookentry_fax' => 0,
				'phonebookentry_reverse_search' => 0,
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
	 * @version 2017-05-10
	 *
	 * @param $job job to do
	 */
	protected function _create_final_xml_by_topic($job) {

		// set as instance variable; this is later used to place xml nodes on different positions
		$this->job = $job;

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
		);
		if ($this->api_version_greater_or_equal("1.4")) {
			array_push($second_level_nodes['contract_create'], 'installation_address_data');
		}

		$second_level_nodes['contract_get_reference'] = array(
			'reseller_identifier',
			'callnumber_contract_identifier',
		);

		$second_level_nodes['contract_get_voice_data'] = array(
			'reseller_identifier',
			'contract_identifier',
		);

		/* 'contract_lock' => array( */
		/* 	'reseller_identifier', */
		/* ), */

		$second_level_nodes['contract_relocate'] = array(
			'reseller_identifier',
			'contract_identifier',
			'contract_relocation_data',
		);

		// not needed atm ⇒ if the last phonenumber is terminated the contract will automatically be deleted
		/* $second_level_nodes['contract_terminate'] = array( */
		/* 	'reseller_identifier', */
		/* 	'contract_identifier', */
		/* 	'contract_termination_data', */
		/* ); */

		/* 'contract_unlock' => array( */
		/* 	'reseller_identifier', */
		/* ), */


		$second_level_nodes['customer_get_reference'] = array(
			'reseller_identifier',
			'customer_identifier',
		);

		$second_level_nodes['customer_update'] = array(
			'reseller_identifier',
			'customer_identifier',
			'customer_data',
		);


		$second_level_nodes['misc_get_free_numbers'] = array(
			'reseller_identifier',
			'filter_data',
		);

		$second_level_nodes['misc_get_keys'] = array(
			'reseller_identifier',
			'key_data',
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
	 * @version 2017-05-10
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
	 * @version 2017-05-10
	 */
	protected function _add_order_identifier() {

		$order_id = \Input::get('order_id', null);
		if (!is_numeric($order_id)) {
			throw new XmlCreationError("order_id has to be numeric");
		}

		$inner_xml = $this->xml->addChild('order_identifier');
		$inner_xml = $inner_xml->addChild('orderid', $order_id);

	}


	/**
	 * Method to add filter data.
	 * This doesn't use method _add_fields – data comes only from $_GET
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
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
			throw new XmlCreationError("localareacode has to be numeric");
		}

		// localareacode is valid: add filter
		$inner_xml->addChild('localareacode', $localareacode);

		if (is_null($baseno)) {
			return;
		}

		// if given: baseno has to be numeric
		// TODO: error handling
		if (!is_numeric($baseno)) {
			throw new XmlCreationError("baseno has to be numeric");
		}

		// baseno is valid
		$inner_xml->addChild('baseno', $baseno);

	}


	/**
	 * Method to add key data.
	 * This specifies the data to be caught from Envia.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _add_key_data() {

		// the keyname for the data to catch; default is showing all available methods.
		$keyname = \Input::get('keyname', 'index');

		$inner_xml = $this->xml->addChild('key_data');

		$inner_xml->addChild('keyname', $keyname);
	}


	/**
	 * Method to add customer identifier
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _add_customer_identifier() {

		$inner_xml = $this->xml->addChild('customer_identifier');

		// if set: use customerreference (prefered by Envia)
		// but not in getting the customer's reference – here in each case we have to use the contract number
		$customerreference = $this->contract->customer_external_id;
		if (
			($this->job != 'customer_get_reference')
			&&
			boolval($customerreference)
			&&
			($customerreference != 'n/a')
		) {
			$inner_xml->addChild('customerreference', $customerreference);
		}
		// else – e.g. on the first contract_create for a customer – use customernumber
		else {

			// if we have a legacy customer number: use this
			$customerno_legacy = $this->contract->customer_number_legacy();
			if (boolval($customerno_legacy) && ($customerno_legacy != 'n/a')) {
				$inner_xml->addChild('customerno', $customerno_legacy);
			}
			// else choose the customer number
			else {
				$customerno = $this->contract->customer_number();
				$inner_xml->addChild('customerno', $customerno);
			}
		}

	}


	/**
	 * Method to add customer data
	 *
	 * This data is attached on:
	 *	– contract/create for new customers
	 *	– customer/update
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _add_customer_data() {

		// if in method contract/create: check if customer already exist at Envia ⇒ if so: don't add <customer_data>
		if (
			($this->job == 'contract_create')
			&&
			(boolval($this->contract->customer_external_id))
		) {
			return;
		}

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
			'district' => 'district',
			'birthday' => 'birthday',
			'company' => 'company',
			'department' => 'department',
		);

		$this->_add_fields($inner_xml, $fields, $this->contract);
	}


	/**
	 * Method to add installation address.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _add_installation_address_data() {

		if ($this->job == 'contract_create') {
			$inner_xml = $this->xml->contract_data->addChild('installation_address_data');
		}
		elseif ($this->job == 'contract_relocate') {
			$inner_xml = $this->xml->contract_relocation_data->addChild('installation_address_data');
		}

		// mapping xml to database
		$fields = array(
			'salutation' => 'salutation',
			'firstname' => 'firstname',
			'lastname' => 'lastname',
			'street' => 'street',
			'houseno' => 'house_number',
			'zipcode' => 'zip',
			'city' => 'city',
			'district' => 'district',
			'birthday' => 'birthday',
			'company' => 'company',
			'department' => 'department',
		);

		$this->_add_fields($inner_xml, $fields, $this->modem);
	}


	/**
	 * Method to add contract data.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _add_contract_data() {

		// check if there are missing values (e.g. they are missing if billing is enabled but man forgot to add voip item before calling this
		$value_missing = False;

		// as we ATM only allow one variation per user we can safely take this data out of contract
		// TODO: this has to be changed if someday we want to allow different variations on multiple modems
		// therefore we also have to update Contract::daily_conversion()!
		if (!boolval($this->contract->phonetariff_purchase_next)) {
			$value_missing = True;
			$msg = 'next_purchase_tariff not set in contract '.$this->contract->id;
			if (\PPModule::is_active('billingbase')) {
				$msg .= ' – maybe you have to create a Voip item with future start date?';
			}
		}

		// as we ATM only allow one tariff per user we can safely take this data out of contract
		// TODO: this has to be changed if someday we want to allow different tariffs on multiple modems
		// therefore we also have to update Contract::daily_conversion()!
		if (!boolval($this->contract->phonetariff_sale_next)) {
			$value_missing = True;
			$msg = 'next_voip_id not set in contract '.$this->contract->id;
			if (\PPModule::is_active('billingbase')) {
				$msg .= ' – maybe you have to create a Voip item with future start date?';
			}
		}

		// check if at least one phonenumber is given
		$phonenumbers_to_create = \Input::get('phonenumbers_to_create', []);
		if (!$phonenumbers_to_create) {
			$msg = 'Can only create contract with at least one phonenumber, but none given';
			$value_missing = True;
		}
		else {
			$numbers_on_modem = $this->get_numbers_related_to_modem_for_contract_create();
			$numbers_on_modem_rearranged = array();
			foreach ($numbers_on_modem as $nr_origin => $tmp_outer) {
				foreach ($tmp_outer as $nr_date => $tmp_inner) {
					foreach ($tmp_inner as $nr) {
						$numbers_on_modem_rearranged[$nr->id] = ['type' => $nr_origin."  ".$nr_date, 'nr' => $nr];
					}
				}
			}

			// check all given numbers for validity
			$porting = null;
			$ekp_in = null;
			$orderdate = null;
			$last_mgmt = null;

			$subscriber_data_keys = [
				'subscriber_company',
				'subscriber_department',
				'subscriber_salutation',
				'subscriber_firstname',
				'subscriber_lastname',
				'subscriber_street',
				'subscriber_house_number',
				'subscriber_zip',
				'subscriber_city',
				'subscriber_district',
				];

			foreach ($phonenumbers_to_create as $nr_id) {

				// check if number belongs to current modem
				if (!array_key_exists($nr_id, $numbers_on_modem_rearranged)) {
					$msg = "Phonenumber $nr_id does not belong to modem";
					$value_missing = True;
					break;
				}

				$mgmt = $numbers_on_modem_rearranged[$nr_id]['nr']->phonenumbermanagement;

				// check if cur number has management
				if (is_null($mgmt)) {
					$msg = "Chosen phonenumber $nr_id has no management.";
					$value_missing = True;
					break;
				}

				// check if activation date set
				if (is_null($mgmt->activation_date)) {
					$msg = "No activation date set for number $nr_id";
					$value_missing = True;
					break;
				}

				// check if activation  dates of all numbers are identical
				$orderdate = $mgmt->activation_date;
				if (!is_null($last_mgmt) && ($last_mgmt->activation_date != $orderdate)) {
					$msg = "Given numbers have different activation dates ($orderdate, $mgmt->activation_date)";
					$value_missing = True;
					break;
				}

				// check if all numbers have identical porting information
				$porting = $mgmt->porting_in;
				if (!is_null($last_mgmt) && ($last_mgmt->porting_in != $porting)) {
					$msg = "Either all given numbers have to be ported or none – mixing is not allowed";
					$value_missing = True;
					break;
				}

				if ($porting) {

					// if number has to be ported: check if incoming EKP codes are identical
					$ekp_in = $mgmt->ekp_in;
					if (!is_null($last_mgmt) && ($last_mgmt->ekp_in != $ekp_in)) {
						$msg = "All numbers to be created have to have the same incoming EKP code";
						$value_missing = True;
						break;
					}

					// compare subscriber data
					if (!is_null($last_mgmt)) {
						foreach ($subscriber_data_keys as $key) {
							if (trim($last_mgmt->{$key}) != trim($mgmt->{$key})) {
								$value_missing = True;
								$msg = "Differences in subscriber data ($last_mgmt->{$key} != $mgmt->{$key})";
								break; // the inner foreach
							}
						}
						if ($value_missing) {
							break;	// the outer foreach
						}
					}
				}

				// store currend management for comparing values with next number
				$last_mgmt = $mgmt;
			}
		}

		if ($value_missing) {
			throw new XmlCreationError($msg);
		}

		// begin to build the xml
		$inner_xml = $this->xml->addChild('contract_data');

		// set porting flag if numbers have to be ported
		if ($porting) {
			$inner_xml->addChild('porting', 1);
		}

		// add startdate for contract (default: today – there are no costs without phone numbers)
		$inner_xml->addChild('orderdate', $orderdate);

		// the data exists: now we can safely get the external identifiers without raising an Exception
		$inner_xml->addChild('variation_id', $this->contract->phonetariff_purchase_next->external_identifier);
		$inner_xml->addChild('tariff', $this->contract->phonetariff_sale_next->external_identifier);

		// add the phonenumbers
		// before adding: build array containing instances of all phonenumbers to be created
		$phonenumbers_to_create = array_flip($phonenumbers_to_create);
		foreach ($phonenumbers_to_create as $id => $_) {
			$phonenumbers_to_create[$id] = $numbers_on_modem_rearranged[$id]['nr'];
		}
		$this->_add_callnumbers($inner_xml, $phonenumbers_to_create);

		// add the default values
		$defaults = $this->_get_defaults_by_topic('contract_data');
		foreach ($defaults as $xml_field => $payload) {
			$inner_xml->addChild($xml_field, $payload);
		}

		// if number(s) have to be ported: add subscriber data
		if ($porting) {
			$this->_add_subscriber_data($mgmt);
		}

	}


	/**
	 * Method to add tariff data
	 *
	 * @author Patrick Reichel
	 */
	protected function _add_tariff_data() {

		$inner_xml = $this->xml->addChild('tariff_data');

		// TODO: get date from Contract->Item (after merging with Nino)
		$inner_xml->addChild('orderdate', date('Y-m-d', strtotime('first day of next month')));

		// as we ATM only allow one tariff per user we can safely take this data out of contract
		// TODO: this has to be changed if someday we want to allow different tariffs on multiple modems
		// therefore we also have to update Contract::daily_conversion()!
		$inner_xml->addChild('tariff', $this->contract->phonetariff_sale_next->external_identifier);


	}


	/**
	 * Method to add variation data
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _add_variation_data() {

		$inner_xml = $this->xml->addChild('variation_data');

		// no date to be given ⇒ changed automatically on 1st of next month

		// as we ATM only allow one variation per user we can safely take this data out of contract
		// TODO: this has to be changed if someday we want to allow different variations on multiple modems
		// therefore we also have to update Contract::daily_conversion()!
		$inner_xml->addChild('variation_id', $this->contract->phonetariff_purchase_next->external_identifier);


	}


	/**
	 * Method to add contract termination
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
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
	 * @param: $phonenumbermanagement; if not given: use $this->phonenumbermanagement
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _add_subscriber_data($phonenumbermanagement=null) {

		if (is_null($phonenumbermanagement)) {
			$phonenumbermanagement = $this->phonenumbermanagement;
		}

		// subscriber data contains the current “owner” of the number ⇒ this tag is only needed if a phonenumber shall be ported
		$porting = boolval($phonenumbermanagement->porting_in);
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
			'houseno' => 'subscriber_house_number',
			'zipcode' => 'subscriber_zip',
			'city' => 'subscriber_city',
			'district' => 'subscriber_district',
		);

		$this->_add_fields($inner_xml, $fields_subscriber, $phonenumbermanagement);

	}


	/**
	 * Method to add account data
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
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
	 * @version 2017-05-10
	 */
	protected function _add_callnumbers($xml, $phonenumbers=[]) {

		$inner_xml = $xml->addChild('callnumbers');

		// TODO: this contains callnumber_single_data, callnumber_range_data or callnumber_new_data objects
		// in this first step we only implement callnumber_single_data
		if (!$phonenumbers) {
			$this->_add_callnumber_single_data($inner_xml);
		}
		else {
			foreach ($phonenumbers as $nr) {
				$this->_add_callnumber_single_data($inner_xml, $nr);
			}
		}

	}


	/**
	 * Method to add data for a single callnumber
	 *
	 * @param phonenumber: if not given use $this->phonenumber
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _add_callnumber_single_data($xml, $phonenumber=null) {

		if (is_null($phonenumber)) {
			$phonenumber = $this->phonenumber;
		}
		$phonenumbermanagement = $phonenumber->phonenumbermanagement;

		$inner_xml = $xml->addChild('callnumber_single_data');

		$fields = array(
			'localareacode' => 'prefix_number',
			'baseno' => 'number',
		);

		$this->_add_fields($inner_xml, $fields, $phonenumber);

		// special handling of trc_class needed (comes from external table)
		$trc_class = TRCClass::find($phonenumbermanagement->trcclass)->trc_id;
		$inner_xml->addChild('trc_class', $trc_class);

		// carrier code not needed in version 1.10 and above
		if ($this->api_version_less_than("1.10")) {
			// special handling for incoming porting needed (comes from external table)
			$carrier_in = CarrierCode::find($phonenumbermanagement->carrier_in)->carrier_code;

			// on porting: check if valid CarrierIn chosen
			if (boolval($phonenumbermanagement->porting_in)) {
				if (!CarrierCode::is_valid($carrier_in)) {
					throw new XmlCreationError('ERROR: '.$carrier_code.' is not a valid carrier_code');
				}
				$inner_xml->addChild('carriercode', $carrier_in);
			}
			// if no porting (new number): CarrierIn has to be D057 (EnviaTEL) (API 1.4 and higher)
			else {
				if ($this->api_version_greater_or_equal("1.4")) {
					if ($carrier_in != 'D057') {
						throw new XmlCreationError('ERROR: If no incoming porting: Carriercode has to be D057 (EnviaTEL)');
					}
					$inner_xml->addChild('carriercode', $carrier_in);
				}
			}
		}

		// in API 1.4 and higher we also need the EKP code for incoming porting
		if ($this->api_version_greater_or_equal("1.4")) {
			if (boolval($phonenumbermanagement->porting_in)) {
				$ekp_in = EkpCode::find($phonenumbermanagement->ekp_in)->ekp_code;
				$inner_xml->addChild('ekp_code', $ekp_in);
			}
		}

		$this->_add_sip_data($inner_xml->addChild('method'), $phonenumber);
	}


	/**
	 * Adds phonenumber to be used to get a contract reference.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _add_callnumber_contract_identifier() {

		$inner_xml = $this->xml->addChild('callnumber_contract_identifier');

		$fields = array(
			'localareacode' => 'prefix_number',
			'baseno' => 'number',
		);

		$this->_add_fields($inner_xml, $fields, $this->phonenumber);
	}

	/**
	* Method to add data for a callnumber.
	* This is different from _add_callnumber_single_data – so we have to implement again…
	*
	* @author Patrick Reichel
	 * @version 2017-05-10
	*/
	protected function _add_callnumber_data() {

		$inner_xml = $this->xml->addChild('callnumber_data');

		// TODO: change to date selection instead of performing changes today?
		$inner_xml->addChild('orderdate', date("Y-m-d"));

		// special handling of trc_class needed (comes from external table)
		$trc_class = TRCClass::find($this->phonenumbermanagement->trcclass);
		if (is_null($trc_class)) {
			throw new XmlCreationError("TRCclass not set.<br>Set TRCclass and save the PhonenumberManagement.");
		}
		$trc_id = $trc_class->trc_id;
		$inner_xml->addChild('trc_class', $trc_id);

		$this->_add_sip_data($inner_xml->addChild('method'));
	}

	/**
	 * Method to add sip data.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _add_sip_data($xml, $phonenumber=null) {

		if (is_null($phonenumber)) {
			$phonenumber = $this->phonenumber;
		}

		$inner_xml = $xml->addChild('sip_data');

		$fields = array(
			'username' => 'username',
			'password' => 'password',
		);

		// Envia API throws error if <sipdomain nil="true" /> is given…
		if (boolval($phonenumber->sipdomain)) {
			$fields['sipdomain'] = 'sipdomain';
		}

		$this->_add_fields($inner_xml, $fields, $phonenumber);
	}


	/**
	 * Method to add callnumber identifier
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
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
	 * @version 2017-05-10
	 */
	protected function _add_accounttermination_data() {

		$inner_xml = $this->xml->addChild('accounttermination_data');

		$fields = array(
			'orderdate' => 'deactivation_date',
		);

		if (!boolval($this->phonenumbermanagement->deactivation_date)) {
			throw new XmlCreationError('ERROR: PhonenumberManagement::deactivation_date needs to be set');
		}

		$this->_add_fields($inner_xml, $fields, $this->phonenumbermanagement);

		// handle outgoing porting
		if (boolval($this->phonenumbermanagement->porting_out)) {
			$carrier_out = CarrierCode::find($this->phonenumbermanagement->carrier_out)->carrier_code;
			if (CarrierCode::is_valid($carrier_out)) {
				$inner_xml->addChild('carriercode', $carrier_out);
			}
			else {
				throw new XmlCreationError('ERROR: '.$carrier_code.' is not a valid carrier_code');
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
	 * @version 2017-05-10
	 *
	 * @throws XmlCreationError if GET param envia_blacklist_get_direction is not in [in|out]
	 */
	protected function _add_blacklist_data() {

		$direction = strtolower(\Input::get('envia_blacklist_get_direction'));
		$valid_directions = ['in', 'out'];

		if (!in_array($direction, $valid_directions)) {
			throw new XmlCreationError('envia_blacklist_get_direction has to be in ['.implode('|', $valid_directions).']');
		}

		$inner_xml = $this->xml->addChild('blacklist_data');
		$inner_xml->addChild('direction', $direction);
	}


	/**
	 * Method to add contract identifier.
	 * In Envia speech a contract is phone connection (“Anschluss”). There can be multiple ones per modem.
	 * This is especially important to support different installation addresses on multiple modems per user.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-16
	 */
	protected function _add_contract_identifier() {

		$inner_xml = $this->xml->addChild('contract_identifier');

		$external_contract_references = [];

		// depending on the job to do we have to get the EnviaContract references
		// especially we have to distinct jobs related to modems (e.g. contract_relocate) from those related to phonenumbers
		if (in_array(
				$this->job,
				[
					'contract_change_tariff',
					'contract_change_variation',
					'contract_relocate',
				])
				||
				(
					// check if contract/get_voice_data has been called from modem level
					// in this case we use the modem as source for external contract id
					// else we use the phonenumber
					($this->job == 'contract_get_voice_data')
					&&
					(!$this->phonenumber->exists)
				)
		) {
			// this are the cases where more than one external contract can exist and we have to decide which to use (or to use all)
			//
			// get all contract references attached to this modem
			foreach ($this->modem->mtas as $mta) {
				foreach ($mta->phonenumbers as $phonenumber) {
					if (
						($phonenumber->contract_external_id)
						&&
						(!in_array($phonenumber->contract_external_id, $external_contract_references))
					) {
						array_push($external_contract_references, $phonenumber->contract_external_id);
					}
				}
			}

			// no reference found
			if (!$external_contract_references) {
				throw new XmlCreationError('No EnviaOrder ID (contract_external_id) found. Cannot proceed.');
			}

			// TODO: implement logic to relocate more than one contract attached to the current modem!!
			if (count($external_contract_references) > 1) {
				throw new XmlCreationError('There is more than one EnviaContract used on this modem ('.(implode(', ', $external_contract_references)).'. Processing this is not yet implemented – please use the Envia Web API and inform Patrick.');
			}
			else {
				$external_contract_reference = $external_contract_references[0];
			}
		}
		else {
			// default: taking external contract reference from phonenumber
			$external_contract_reference = $this->phonenumber->contract_external_id;
		}

		if (!$external_contract_reference) {
			throw new XmlCreationError('No EnviaOrder ID (contract_external_id) found. Cannot proceed.');
		}

		$inner_xml->addChild('contractreference', $external_contract_reference);

	}


	/**
	 * Method to add contract relocation data (used to change installation address of modem).
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _add_contract_relocation_data() {

		$inner_xml = $this->xml->addChild('contract_relocation_data');

		if (is_null($this->modem->installation_address_change_date)) {
			throw new XmlCreationError('ERROR: Date of installation address change has to be set.');
		}

		$inner_xml->addChild('orderdate', $this->modem->installation_address_change_date);

		$this->_add_installation_address_data();

		// necessary in version 1.4, in 1.5 removed again
		if ($this->api_version_equals("1.4")) {
			$inner_xml->addChild('apply_to_customer', 0);
		}

	}


	/**
	 * Method to add attachment
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _add_attachment_data() {

		$enviaorderdocument_id = \Input::get('enviaorderdocument_id');
		$enviaorder_id = \Input::get('order_id');

		$enviaorderdocument = EnviaOrderDocument::findOrFail($enviaorderdocument_id);

		if ($enviaorderdocument->enviaorder->orderid != $enviaorder_id) {
			throw new XmlCreationError('Given order_id ('.$enviaorder_id.') not correct for given enviaorderdocument');
		}
		if (boolval($enviaorderdocument->upload_order_id)) {
			throw new XmlCreationError('Given document has aleady been uploaded');
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
	 * @version 2017-05-10
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
	 * @version 2017-05-10
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

				// special case salutation: Envia expects Herrn instead of Herr…
				if ($xml_field == 'salutation') {
					if ($payload == 'Herr') {
						$payload = 'Herrn';
					}
				}
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
	 * @version 2017-05-10
	 */
	public function process_envia_data($job, $data) {

		Log::debug(__METHOD__." started for job ".$job);

		// special header for order_get_status 404 response
		if (($job == 'order_get_status') && ($data['status'] == 404)) {
			$out = '<h4>Error (HTTP status is '.$data['status'].')</h4>';
		}
		else {
			$out = '<h4>Success (HTTP status is '.$data['status'].')</h4>';
		}

		$raw_xml = $data['xml'];
		$xml = new \SimpleXMLElement($raw_xml);

		// check if we want to store the xml
		$this->store_xml($job.'_response', $xml);

		$method = '_process_'.$job.'_response';
		$out = $this->${"method"}($xml, $data, $out);

		return $out;
	}


	/**
	 * Ping successful message.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
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
	 * @version 2017-05-10
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
	 * Show the result for get_keys.
	 *
	 * TODO: Update our data with this response (database, files, etc.). This could then be run
	 * as a cron job (e.g. weekly)
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _process_misc_get_keys_response($xml, $data, $out) {


		$keyname = \Input::get('keyname', 'index');

		if ($keyname == 'index') {
			$out .= '<h5>Available keys</h5>';
			$out .= '<h5 style="color: red">Attention: Data for this keys should be downloaded max. once per day. This will later be done by a cron job</h5>';
		}
		else {
			// process the data according to the key
			// TODO: implement the missing methods
			if ($keyname == 'carriercode') {
				$out = $this->_process_misc_get_keys_response_carriercode($xml, $data, $out);
			}
			elseif ($keyname == 'ekp_code') {
				$out = $this->_process_misc_get_keys_response_ekp_code($xml, $data, $out);
			}
			elseif ($keyname == 'trc_class') {
				$out = $this->_process_misc_get_keys_response_trc_class($xml, $data, $out);
			}
			else {
				$out .= '<h4 style="color: red">Attention: ATM the following data is not used to update database/files!</h4>';
			}
			$out .= '<h5>Data send for key '.$keyname.'</h5>';
		}

		$out .= '<table class="table table-striped table-hover">';
		$out .= '<thead><tr><th>ID</th><th>Description</th></tr></thead>';
		$out .= '<tbody>';
		foreach ($xml->keys->key as $key) {
			$out .= '<tr>';
			$out .= '<td>';
			if ($keyname == 'index') {
				$href = \URL::route('ProvVoipEnvia.request', array('job' => 'misc_get_keys', 'keyname' => ((string) $key->id), 'really' => 'True'));
				$out .= '<a href="'.$href.'" target="_self">'.$key->id.'</a>';
			}
			else {
				$out .= $key->id;
			}
			$out .= '</td><td>'.$key->description.'</td>';
			$out .= '</tr>';
		}
		$out .= '</tbody>';
		$out .= '</table>';


		return $out;
	}


	/**
	 * Update the database table carriercode using data delivered by Envia API
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _process_misc_get_keys_response_carriercode($xml, $data, $out) {

		// first: get all currently existing ids – we need them later on to delete removed carriercodes
		// in my opinion this should never be the case – but who knows…
		// we also have to take care to prevent database ids from changing!
		$existing_carriercodes = CarrierCode::all();
		$existing_ids = array();
		foreach ($existing_carriercodes as $code) {
			if ($code->carrier_code != '0') {
				// add all but the dummy
				$existing_ids[$code->id] = $code->carrier_code;
			}
		}

		// process the returned data and perform restore, add and change actions
		foreach ($xml->keys->key as $entry) {

			// Envia partially sends data with trailing 0xa0 (=NO-BREAK SPACE) – we have to trim this explicitely!
			$id = trim($entry->id, " \t\n\r\0\x0B\xC2\xA0");
			$description = trim($entry->description, " \t\n\r\0\x0B\xC2\xA0");

			$carriercode = CarrierCode::withTrashed()->firstOrNew(['carrier_code' => $id]);
			$changed = False;

			$methods = array();

			// restore soft deleted entry
			if ($carriercode->trashed()) {
				$carriercode->restore();
				array_push($methods, 'Restoring');
				$changed = True;
			}

			// new: add the carrier code
			if (!$carriercode->exists) {
				$carriercode->carrier_code = $id;
				$carriercode->company = $description;
				array_push($methods, 'Adding');
				$changed = True;
			}

			// company changed? update database
			if ($carriercode->company != $description) {
				$carriercode->company = $description;
				array_push($methods, 'Updating');
				$changed = True;
			}

			// change the changes (if some)
			if ($changed) {
				$msg = implode('/', $methods).' '.$carriercode->carrier_code.' ('.$carriercode->company.')';
				$out .= $msg.'<br>';
				\Log::info($msg);
				$carriercode->save();
			}

			// remove from existing array
			$idx = array_search($id, $existing_ids);
			if ($idx !== False) {
				unset($existing_ids[$idx]);
			}

		}

		// remove the remaining carriercodes (those that are not within the Envia response) from database
		foreach ($existing_ids as $id => $code) {
			$cc = CarrierCode::find($id);
			$msg = 'Deleting carriercode with ID '.$id.' ('.$code.': '.$cc->company.')';
			$out .= $msg.'<br>';
			\Log::warning($msg);
			$cc->delete();
		}

		return $out;
	}


	/**
	 * Update the database table ekpekpcode using data delivered by Envia API
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _process_misc_get_keys_response_ekp_code($xml, $data, $out) {

		// first: get all currently existing ids – we need them later on to delete removed ekpcodes
		// in my opinion this should never be the case – but who knows…
		// we also have to take care to prevent database ids from changing!
		$existing_ekpcodes = EkpCode::all();
		$existing_ids = array();
		foreach ($existing_ekpcodes as $code) {
			$existing_ids[$code->id] = $code->ekp_code;
		}

		// process the returned data
		// as some “IDs” are sent more than once we first have to combine them
		// to avoid incomplete database entries and log pollution
		$prepared_envia_data = array();
		foreach ($xml->keys->key as $entry) {

			// Envia partially sends data with trailing “0xc2 0xa0” (=NO-BREAK SPACE) – we have to trim this explicitely!
			$id = trim($entry->id, " \t\n\r\0\x0B\xC2\xA0");
			$description = trim($entry->description, " \t\n\r\0\x0B\xC2\xA0");

			if (!array_key_exists($id, $prepared_envia_data)) {
				$prepared_envia_data[$id] = $description;
			}
			else {
				$prepared_envia_data[$id] .= ', '.$description;
			}
		}

		// now check for changes and update database
		foreach ($prepared_envia_data as $id => $description) {

			$ekpcode = EkpCode::withTrashed()->firstOrNew(['ekp_code' => $id]);
			$changed = False;

			$methods = array();

			// restore soft deleted entry
			if ($ekpcode->trashed()) {
				$ekpcode->restore();
				array_push($methods, 'Restoring');
				$changed = True;
			}

			// new: add the ekp code
			if (!$ekpcode->exists) {
				$ekpcode->ekp_code = $id;
				$ekpcode->company = $description;
				array_push($methods, 'Adding');
				$changed = True;
			}

			// company changed? update database
			if ($ekpcode->company != $description) {
				$ekpcode->company = $description;
				array_push($methods, 'Updating');
				$changed = True;
			}

			// change the changes (if some)
			if ($changed) {
				$msg = implode('/', $methods).' '.$ekpcode->ekp_code.' ('.$ekpcode->company.')';
				$out .= $msg.'<br>';
				\Log::info($msg);
				$ekpcode->save();
			}

			// remove from existing array
			$idx = array_search($id, $existing_ids);
			if ($idx !== False) {
				unset($existing_ids[$idx]);
			}

		}

		// remove the remaining ekpcodes (those that are not within the Envia response) from database
		foreach ($existing_ids as $id => $code) {
			$ec = EkpCode::find($id);
			$msg = 'Deleting ekpcode with ID '.$id.' ('.$code.': '.$ec->company.')';
			$out .= $msg.'<br>';
			\Log::warning($msg);
			$ec->delete();
		}

		return $out;

	}


	/**
	 * Update the database table trc_code using data delivered by Envia API
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _process_misc_get_keys_response_trc_class($xml, $data, $out) {

		// first: get all currently existing ids – we need them later on to delete removed carriercodes
		// in my opinion this should never be the case – but who knows…
		// we also have to take care to prevent database ids from changing!
		$existing_trcclasses = TRCClass::all();
		$existing_ids = array();
		foreach ($existing_trcclasses as $class) {
			$existing_ids[$class->id] = $class->trc_id;
		}

		// process the returned data
		foreach ($xml->keys->key as $entry) {

			// Envia partially sends data with trailing 0xa0 (=NO-BREAK SPACE) – we have to trim this explicitely!
			$id = trim($entry->id, " \t\n\r\0\x0B\xC2\xA0");
			$tmp = explode(' ', trim($entry->description, " \t\n\r\0\x0B\xC2\xA0"));
			$short = trim($tmp[0]);
			$description = trim(implode(' ', array_slice($tmp, 2)), " \t\n\r\0\x0B\xC2\xA0");

			$trcclass = TRCClass::withTrashed()->firstOrNew(['trc_id' => $id]);
			$changed = False;

			$methods = array();

			// restore soft deleted entry
			if ($trcclass->trashed()) {
				$trcclass->restore();
				array_push($methods, 'Restoring');
				$changed = True;
			}

			// new: add the trc class
			if (!$trcclass->exists) {
				$trcclass->trc_id = $id;
				$trcclass->trc_short = $short;
				$trcclass->trc_description = $description;
				array_push($methods, 'Adding');
				$changed = True;
			}

			// class changed? update database
			if (
				($trcclass->trc_short != $short)
				||
				($trcclass->trc_description != $description)
			) {
				$trcclass->trc_short = $short;
				$trcclass->trc_description = $description;
				array_push($methods, 'Updating');
				$changed = True;
			}

			// change the changes (if some)
			if ($changed) {
				$msg = implode('/', $methods).' '.$trcclass->trc_id.' ('.$trcclass->trc_short.' – '.$trcclass->trc_description.')';
				$out .= $msg.'<br>';
				\Log::info($msg);
				$trcclass->save();
			}

			// remove from existing array
			$idx = array_search($id, $existing_ids);
			if ($idx !== False) {
				unset($existing_ids[$idx]);
			}

		}

		// remove the remaining trc classes (those that are not within the Envia response) from database
		foreach ($existing_ids as $id => $class) {
			$tc = TRCClass::find($id);
			$msg = 'Deleting trc class with ID '.$id.' ('.$tc->trc_id.': '.$tc->trc_short.' – '.$tc->trc_description.')';
			$out .= $msg.'<br>';
			\Log::warning($msg);
			$tc->delete();
		}

		return $out;

	}


	/**
	 * Process data after successful contract creation
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _process_contract_create_response($xml, $data, $out) {

		// update contract
		if (
			($this->contract->customer_external_id)
			&&
			($this->contract->customer_external_id != $xml->customerreference)
		) {
			$msg = "Error in processing contract_create response (order ID: ".$xml->orderid."): Existing customer_external_id (".$this->contract->customer_external_id.") different from received one (".$xml->customerreference.")";
			$out .= "<h5>$msg</h5>";
			\Log::error($msg);
		}
		else {
			$this->contract->customer_external_id = $xml->customerreference;
			$this->contract->save();
		}

		// update modem
		/* $this->modem->contract_external_id = $xml->contractreference; */
		// TODO: remove this when detection of active contracts is refactored to use data from enviacontract
		$this->modem->contract_ext_creation_date = date('Y-m-d H:i:s');
		$this->modem->save();

		// create enviaorder
		$order_data = array();
		$order_data['orderid'] = $xml->orderid;
		$order_data['method'] = 'contract/create';
		$order_data['customerreference'] = $xml->customerreference;
		$order_data['contractreference'] = $xml->contractreference;
		$order_data['contract_id'] = $this->contract->id;
		$order_data['modem_id'] = $this->modem->id;
		$order_data['ordertype'] = 'contract/create';
		$order_data['orderstatus'] = 'initializing';

		$enviaOrder = EnviaOrder::create($order_data);

		// create enviacontract
		$enviacontract_data = [
			'external_creation_date' => date('Y-m-d H:i:s'),
			'envia_customer_reference' => $xml->customerreference,
			'envia_contract_reference' => $xml->contractreference,
			'contract_id' => $this->contract->id,
			'modem_id' => $this->modem->id,
		];
		$enviacontract = EnviaContract::create($enviacontract_data);

		// check if there are also phonenumbers created
		$created_phonenumbers = \Input::get('phonenumbers_to_create', []);

		// create some implicite data:
		//   - relation between Envia order and phonenumber
		//   - current timestamp as external creation date in managements
		foreach ($created_phonenumbers as $phonenumber_id) {

			// each given number should exist – if not there is a major problem!
			$phonenumber = Phonenumber::findOrFail($phonenumber_id);

			// add entry to pivot table – there can only be one for this method
			$enviaOrder->phonenumbers()->attach($phonenumber_id);

			// we create a new contract here – so it is save to overwrite potentially exsting data in phonenumbers
			$ret = $this->_update_envia_contract_reference_on_phonenumber($phonenumber, $xml->contractreference, True, False);
			if ($ret) {
				$out .= "<br>$ret";
			}

			// set current timestamp as external creation date and add link between management and enviacontract
			$mgmt = $phonenumber->phonenumbermanagement;
			$mgmt->voipaccount_ext_creation_date = date('Y-m-d H:i:s');
			$mgmt->enviacontract_id = $enviacontract->id;
			$mgmt->save();
		}

		// view data
		$out .= "<h5>Envia contract created (contract reference: $xml->contractreference, order ID: $xml->orderid)</h5>";

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

		$out .= "<h5>Voice data for modem ".$this->modem->id."</h5>";
		$out .= "<h5 style='color: red'>UNTESTED: This needs to be tested when <u>real data</u> is available<br>";
		$out .= "IMPORTANT: Double check changes and new settings!!</h5>";

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
				$phonenumber = Phonenumber::where('prefix_number', '=', $entry->localareacode)->where('number', '=', $entry->baseno)->first();

				$phonenumbermanagement = $phonenumber->phonenumbermanagement;

				// update TRCClass
				// remember: trcclass.id != trclass.trc_id (first is local key, second is Envia Id!)
				if (!$phonenumbermanagement) {
					$msg = "No phonenumbermanagement found for phonenumber $phonenumber->id. Cannot set TRC class";
					$out .= "<b>$msg</b><br>";
					Log::warning($msg);
				}
				else {
					$trcclass = TRCClass::where('trc_id', '=', intval($entry->trc_class))->first();
					if ($phonenumbermanagement['trcclass'] != $trcclass->id) {
						$phonenumbermanagement['trcclass'] = $trcclass->id;
						$phonenumbermanagement->save();
						$msg = "Changed TRC class for phonenumber $phonenumber->id.";
						$out .= "$msg<br>";
						Log::info($msg);
					}
				}

				$method = $entry->method;

				// process SIP data
				if (boolval($method->sip_data)) {
					$sip_data = $method->sip_data;

					// update database
					$changed = False;
					if ($phonenumber['username'] != $sip_data->username) {
						$phonenumber['username'] = $sip_data->username;
						$changed = True;
					}
					if ($phonenumber['password'] != $sip_data->password) {
						$phonenumber['password'] = $sip_data->password;
						$changed = True;
					}
					if ($phonenumber['sipdomain'] != $sip_data->sipdomain) {
						$phonenumber['sipdomain'] = $sip_data->sipdomain;
						$changed = True;
					}
					if ($changed) {
						$phonenumber->save();
						$msg = "Changed SIP data for phonenumber $phonenumber->id";
						$out .= "$msg<br>";
						Log::info($msg);
					}
				}
				// process packet cable data
				elseif (boolval($method->mgcp_data)) {

					// TODO: process data for packet cable
					$msg .= "TODO: packet cable not yet implemented";
					$out .= "<b>$msg</b><br>";
					Log::error($msg);
				}
			}
			elseif ($type == 'callnumber_range_data') {

				// TODO: not yet implemented
				$msg .= "TODO: handling of callnumber_range_data not yet implemented";
				$out .= "<b>$msg</b><br>";
				Log::error($msg);
			}
		}

		$out .= "Done.";

		return $out;
	}


	/**
	 * Sets (or possibly) overwrites envia contract reference in phonenumber table
	 *
	 * @param $phonenumber to be updated
	 * @param $contractreference Envia contract ID
	 * @param $overwrite Flag to allow changing of existing IDs (Default: Do not change)
	 * @param $verbose Flag to return debug messages (Default: False)
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _update_envia_contract_reference_on_phonenumber($phonenumber, $contractreference, $overwrite=False, $verbose=False) {

		// if there is no phonenumber something went wrong
		if (is_null($phonenumber)) {

			$msg = "Phonenumber does not exist";
			\Log::error($msg);
			$msg = "ERROR: $msg";
			return $msg;
		}

		$changed = False;
		if (is_null($phonenumber->contract_external_id)) {
			// store the given Envia contract reference
			$phonenumber->contract_external_id = $contractreference;
			$changed = True;
			$msg = "Envia contract reference not set at phonenumber ".$phonenumber->id." – set to ".$contractreference;
			\Log::info($msg);
		}
		elseif ($phonenumber->contract_external_id != $contractreference) {
			if ($overwrite) {
				// update Envia contract reference in phonenumber
				$phonenumber->contract_external_id = $contractreference;
				$changed = True;
				$msg = "Stored Envia contract reference in ".$phonenumber->id." (".$phonenumber->contract_external_id.") does not match returned value ".$contractreference.". Overwriting.";
				\Log::warning($msg);
			}
		}
		else {
			$msg = "Envia contract reference for phonenumber ".$phonenumber->id." is ".$contractreference;
			\Log::debug($msg);
			if (!$verbose) {
				$msg = '';
			}
		}

		if ($changed) {
			$phonenumber->save();
		}

		return $msg;
	}

	/**
	 * Process data after requesting a contract reference by phonenumber
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _process_contract_get_reference_response($xml, $data, $out) {

		$phonenumber_id = \Input::get('phonenumber_id', null);

		if (is_null($phonenumber_id)) {
			$phonenumber = null;
			$msg = "No phonenumber given";
			\Log::error($msg);
		}
		else {
			$phonenumber = Phonenumber::find($phonenumber_id);
			// response of method contract/get_reference contains the currently used Envia contract reference
			// so it is save to overwrite the data in phonenumber
			$msg = $this->_update_envia_contract_reference_on_phonenumber($phonenumber, $xml->contractreference, True, True);

			// create enviacontract if not existing
			$enviacontract = EnviaContract::where("envia_contract_reference", "=", $xml->contractreference)->first();
			if (!$enviacontract) {
				$data = [
					'envia_contract_reference' => (string) $xml->contractreference,
					'modem_id' => $phonenumber->mta->modem->id,
					'contract_id' => $phonenumber->mta->modem->contract->id,
				];
				$enviacontract = EnviaContract::create($data);
				$msg .= "<br>Created EnviaContract $enviacontract->id";
			}

			// update management if existing; else show warning
			$phonenumbermanagement = $phonenumber->phonenumbermanagement;
			if (!$phonenumbermanagement) {
				$txt = "There is no PhonenumberManagement for $phonenumber->id";
				\Log::warning($txt);
				$msg .= "<h5>WARNING: $txt</h5>";
			}
			elseif ($phonenumbermanagement->enviacontract_id != $enviacontract->id) {
				$phonenumbermanagement->enviacontract_id = $enviacontract->id;
				$phonenumbermanagement->save();
				$msg .= "<br>Updated PhonenumberManagement $phonenumbermanagement->id";
			}

		}

		$out .= "<h5>$msg</h5>";
		return $out;

	}

	/**
	 * Process data after successful tariff change
	 *
	 * @author Patrick Reichel
	 * @todo: updating data in enviacontract has to be done in daily conversion!!
	 */
	protected function _process_contract_change_tariff_response($xml, $data, $out) {

		// create enviaorder
		$order_data = array();
		$order_data['orderid'] = $xml->orderid;
		$order_data['method'] = 'contract/change_tariff';
		$order_data['contract_id'] = $this->contract->id;
		$order_data['modem_id'] = $this->modem->id;
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
	 * @todo: updating data in enviacontract has to be done in daily conversion!!
	 */
	protected function _process_contract_change_variation_response($xml, $data, $out) {

		// create enviaorder
		$order_data = array();

		$order_data['orderid'] = $xml->orderid;
		$order_data['method'] = 'contract/change_variation';
		$order_data['contract_id'] = $this->contract->id;
		$order_data['modem_id'] = $this->modem->id;
		$order_data['ordertype'] = 'contract/change_variation';
		$order_data['orderstatus'] = 'initializing';

		$enviaOrder = EnviaOrder::create($order_data);

		// view data
		$out .= "<h5>Variation change successful (order ID: ".$xml->orderid.")</h5>";

		return $out;
	}

	/**
	 * Process data after successful change of installation address
	 *
	 * @author Patrick Reichel
	 * @todo update all enviacontract related stuff (dates, prev/next id in console job!)
	 */
	protected function _process_contract_relocate_response($xml, $data, $out) {

		// create enviaorder
		$order_data = array();

		$order_data['orderid'] = $xml->orderid;
		$order_data['method'] = 'contract/relocate';
		$order_data['contract_id'] = $this->contract->id;
		$order_data['modem_id'] = $this->modem->id;
		$order_data['ordertype'] = 'contract/relocate';
		$order_data['orderstatus'] = 'initializing';

		$enviaOrder = EnviaOrder::create($order_data);

		// view data
		$out .= "<h5>Installation address change successful (order ID: ".$xml->orderid.")</h5>";

		return $out;
	}


	/**
	 * Process data after requesting customer reference.
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _process_customer_get_reference_response($xml, $data, $out) {

		$update_envia_contract = False;

		if(!boolval($this->contract->customer_external_id)) {
			$this->contract->customer_external_id = $xml->customerreference;
			$this->contract->save();
			$msg = "Setting external customer id for contract ".$this->contract->id." to ".$xml->customerreference;
			$out .= "<b>$msg</b>";
			Log::info($msg);
			$update_envia_contract = True;
		}
		elseif ($this->contract->customer_external_id == $xml->customerreference) {
			$out .= "<b>Envia customer ID is ".$xml->customerreference."</b>";
			$update_envia_contract = True;
		}
		else {
			$msg = "Returned Envia customer reference for contract ".$this->contract->id." (".$xml->customerreference.") does not match our database entry (".$this->contract->customer_external_id.")";
			Log::error($msg);
			$out .= "<b>ERROR: $msg</b>";
		}

		// update data in enviacontract if this seems to be save
		if ($update_envia_contract) {
			$contract_id = $this->contract->id;
			$enviacontracts = EnviaContract::where("contract_id", "=", $contract_id)->get();
			foreach ($enviacontracts as $enviacontract) {
				if ($enviacontract->envia_customer_reference != $this->contract->customer_external_id) {
					$enviacontract->envia_customer_reference = $this->contract->customer_external_id;
					$enviacontract->save();
					$msg = "Updating envia_customer_reference in enviacontract $enviacontract->id to $enviacontract->envia_customer_reference";
					Log::info($msg);
					$out .= "<br><b>$msg</b>";
				}
			}
		}

		return $out;
	}


	/**
	 * Process data after successful customer update
	 *
	 * @author Patrick Reichel
	 * @version 2017-05-10
	 */
	protected function _process_customer_update_response($xml, $data, $out) {

		// create enviaorder
		$order_data = array();

		$order_data['orderid'] = $xml->orderid;
		$order_data['method'] = 'customer/update';
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
	 * According to Envia's Wienecke this method is only for debugging – the answer will only contain
	 * recent voipaccount related orders.
	 * Nevertheless we should use this – e.g. for nightly cron checks to detect manually created
	 * orders (at least according to a phonenumber).
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_misc_get_orders_csv_response($xml, $data, $out) {

		// Envia switched to “;” as delimiter to avoid problems with comma containing orderstatus
		$csv_delimiter = ";";

		// result is base64 encoded csv
		$b64 = $xml->data;
		$csv = base64_decode($b64);

		// csv fieldnames are the first line
		$lines = explode("\n", $csv);
		$csv_headers = str_getcsv(array_shift($lines), $csv_delimiter);

		// array for converted data
		$results = array();
		$errors = array();

		// process Envia CSV line by line; attach orders to $results (or $errors) array
		foreach ($lines as $result_csv) {
			// check if current line contains data => empty lines will crash at array_combine
			if (boolval($result_csv)) {
				$result = str_getcsv($result_csv, $csv_delimiter);

				// check for invalid CSV lines
				// e.g. Envia sent orderstatus: “Fehlgeschlagen, Details siehe Bemerkung” – without enclosing it
				if (count($csv_headers) != count($result)) {
					// we add the raw csv line for later error output/logging
					array_push($errors, $result_csv);
				}
				else {
					$entry = array_combine($csv_headers, $result);
					array_push($results, $entry);
				}
			}
		}

		$out = "";

		// show and log invalid CSV lines
		if ($errors) {
			$out .= "<h5>There are invalid lines in returned CSV:</h5>";
			foreach ($errors as $e) {
				$out .= $e."<br><br>";
				\Log::error('Invalid CSV line processing misc_get_orders_csv_response: '.$e);
			}
			$out .= "<hr>";
		}

		// process the valid CSV lines
		foreach ($results as $result) {

			$order_id = $result['orderid'];

			$out .= "<br>";

			$phonenumbers = Phonenumber::where('prefix_number', '=', $result['localareacode'])->where('number', '=', $result['baseno'])->get();

			// check for edge cases (no number found, more than one number found)
			// the number we look for should exist once and only once!
			$phonenumber_count = $phonenumbers->count();
			if ($phonenumber_count == 0) {
				$msg = 'Error processing get_orders_csv_response: Phonenumber '.$result['localareacode'].'/'.$result['baseno'].' does not exist. Skipping order '.$order_id;
				\Log::warning($msg);
				$out .= '<br><span style="color: red">'.$msg.'</span>';
				continue;
			}
			elseif ($phonenumber_count > 1) {
				$msg = 'Error processing get_orders_csv_response: Phonenumber '.$result['localareacode'].'/'.$result['baseno'].' exists '.$phonenumber_count.' times. Clean your database! Skipping order '.$order_id;
				\Log::warning($msg);
				$out .= '<br><span style="color: red">'.$msg.'</span>';
				continue;
			}

			$phonenumber = $phonenumbers->first();

			$result['phonenumber_id'] = $phonenumber->id;
			$result['modem_id'] = $phonenumber->mta->modem->id;
			$result['contract_id'] = $phonenumber->mta->modem->contract->id;

			$order = EnviaOrder::where('orderid', $order_id)->first();

			// check if there is an Envia contract for the returned contract_reference
			// this is save here because within the CSV there are only phonenumber related orders (and e.g. no contract/relocate)
			$enviacontract = EnviaContract::where("envia_contract_reference", "=", $result['contractreference'])->first();
			if (!$enviacontract) {
				// if not: create
				$data = [
					'envia_customer_reference' => $result['customerreference'],
					'envia_contract_reference' => $result['contractreference'],
					'modem_id' => $phonenumber->mta->modem->id,
					'contract_id' => $phonenumber->mta->modem->contract->id,
				];
				$enviacontract = EnviaContract::create($data);
				$msg = "Created EnviaContract $enviacontract->id";
				Log::info($msg);
				$out .= '<br>'.$msg;
			}

			// add envia contract id to result array ⇒ used to check relation between phonenumbermanagement and envia
			// contract in _update_phonenumbermanagement_with_envia_data() (called later via _update_phonenumber_related_data()
			$result['enviacontract_id'] = $enviacontract->id;



			// check if this order already exists within the database
			if (!is_null($order)) {

				$order_changed = False;

				// ordertype_id is not given by order_get_status: we have to set it here if there are any changes
				if ($order->ordertype_id != $result['ordertype_id']) {
					$order->ordertype_id = $result['ordertype_id'];
					$order->save();
					$msg = 'Updated ordertype_id in for existing order '.$order_id;
					Log::info($msg);
					$out .= '<br>'.$msg;
					$order_changed = true;
				}

				// as an order can be related to more than one phonenumber we
				// have to check if the current relation exists
				if (!$order->phonenumbers->contains($phonenumber->id)) {
					$order->phonenumbers()->attach($phonenumber->id);
					$msg = 'Added relation between existing enviaorder '.$order_id.' and phonenumber '.$phonenumber->id;
					Log::info($msg);
					$out .= '<br>'.$msg;
					$order_changed = true;

					// check if contract, modem and/or phonenumbermanagement need updates, too
					$out = $this->_update_phonenumber_related_data($result, $out);
				}

				// if nothing happened related to the current order: inform the user
				// updating other informations related to this order will be done in method order_get_status
				if (!$order_changed) {
					$out .= '<br>Order '.$order_id.' already exists in database and nothing to do. Skipping.';
				}

				continue;
			}

			// create a new Order, add given data to model instance
			$order = EnviaOrder::create($result);

			$out .= '<br>Order '.$order_id.' created.';

			// if we end up here we have an order that not existed yet – so we have to check if
			// contract, modem and/or phonenumbermanagement need updates, too
			$out = $this->_update_phonenumber_related_data($result, $out);
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
	 * This is used to update several inforamtion in contract, modem and phonenumbermanagement.
	 * Has to be done on misc_get_orders_csv and contract_get_reference because this values can be missing (e.g. voip_account has been created before activation of envia module)
	 *
	 * @author Patrick Reichel
	 */
	protected function _update_phonenumber_related_data($data, $out='') {

		Log::debug(__METHOD__." started");

		if (isset($data['contract_id'])) {
			$out = $this->_update_contract_with_envia_data($data, $out);
		}

		if (isset($data['modem_id'])) {
			$out = $this->_update_modem_with_envia_data($data, $out);
		}

		if (isset($data['phonenumber_id']) || isset($data['phonenumbermanagement_id'])) {
			$out = $this->_update_phonenumber_with_envia_data($data, $out);
			$out = $this->_update_phonenumbermanagement_with_envia_data($data, $out);
		}

		$out .= "<br><pre>".print_r($data)."</pre>";

		return $out;
	}


	/**
	 * Updates contract with order's data.
	 *
	 * @author Patrick Reichel
	 */
	protected function _update_contract_with_envia_data($data, $out='') {

		Log::debug(__METHOD__." started");

		if (!isset($data['contract_id'])) {
			Log::warning("No contract_id given");
			$out .= '<br> ⇒ Warning: No contract_id given';
			return $out;
		}

		Log::debug("contract_id is ".$data['contract_id']);
		$contract = Contract::findOrFail($data['contract_id']);

		if (!isset($data['customerreference'])) {
			return $out;
		}

		// set customerreference or check for integrity (this reference should never change!)
		if (is_null($contract->customer_external_id)) {
			$contract->customer_external_id = $data['customerreference'];
			$contract->save();
			$out .= '<br> ⇒ Contract->customer_external_id set to '.$data['customerreference'];
		}
		elseif ($contract->customer_external_id != $data['customerreference']) {
			$out .= '<br> ⇒ <span style="color: red">error: contract->customer_external_id ('.$contract->customer_external_id.') != enviaorder->customerreference ('.$data['customerreference'].')!!</span>';
		}

		return $out;
	}


	/**
	 * Updates modem with order's data.
	 *
	 * @author Patrick Reichel
	 */
	protected function _update_modem_with_envia_data($data, $out='') {

		Log::debug(__METHOD__." started");

		if (!isset($data['modem_id'])) {
			Log::warning("No modem_id given");
			$out .= '<br> ⇒ Warning: No modem_id given';
			return $out;
		}

		Log::debug("modem_id is ".$data['modem_id']);
		$modem = Modem::findOrFail($data['modem_id']);

		// try to get related contract (if id not given) and update it
		if (!isset($data['contract_id'])) {
			$data['contract_id'] = $modem->contract->id;
			$out = _update_contract_with_envia_data($data, $out);
		}

		$modem_changed = false;

		if (isset($data['contractreference'])) {

			// TODO: check if we want to use this field in future
			// Commented out by par – there can be more than one Envia contract at a modem.
			/* // set contractreference and check for integrity */
			/* if (is_null($modem->contract_external_id)) { */
			/* 	$modem->contract_external_id = $data['contractreference']; */
			/* 	$modem_changed = true; */
			/* 	$out .= '<br> ⇒ Modem->contract_external_id set to '.$data['contractreference']; */
			/* } */
			/* if ($modem->contract_external_id != $data['contractreference']) { */
			/* 	$out .= '<br> ⇒ <span style="color: red">ERROR: Modem->contract_external_id ('.$modem->contract_external_id.') != EnviaOrder->contractreference ('.$data['contractreference'].')!!</span>'; */
			/* } */

			// if there is a contract reference at Envia we can be sure that this contract has been created :-)
			if (is_null($modem->contract_ext_creation_date)) {

				// prepare the date
				if (isset($data['orderdate'])) {
					$date_to_set = min(date('Y-m-d'), $data['orderdate']);
				}
				else {
					$date_to_set = date('Y-m-d');
				}

				// set the date
				$modem->contract_ext_creation_date = $date_to_set;
				$modem_changed = true;
				$out .= '<br> ⇒ Modem->contract_ext_creation_date set to '.$date_to_set;
			}
			// if we got an orderdate: we also can update modem if orderdate is less than the currently stored date
			elseif (
				isset($data['orderdate']) &&
				($modem->contract_ext_creation_date > $data['orderdate'])
			) {
				$modem->contract_ext_creation_date = $data['orderdate'];
				$modem_changed = true;
				$out .= '<br> ⇒ Modem->contract_ext_creation_date set to '.$data['orderdate'];
			}
		}

		if ($modem_changed) {
			$modem->save();
		}

		return $out;
	}


	/**
	 * Writes external contract reference to phonenumbers.
	 *
	 * @author Patrick Reichel
	 */
	protected function _update_phonenumber_with_envia_data($data, $out='') {

		Log::debug(__METHOD__." started");

		if (!isset($data['phonenumber_id'])) {
			Log::warning("No phonenumber_id given");
			$out .= '<br> ⇒ Warning: No phonenumber_id given';
			return $out;
		}

		Log::debug("phonenumber_id is ".$data['phonenumber_id']);
		$phonenumber = Phonenumber::findOrFail($data['phonenumber_id']);

		if (isset($data['contractreference'])) {
			$ret = $this->_update_envia_contract_reference_on_phonenumber($phonenumber, $data['contractreference'], False, False);
			if ($ret) {
				$out .= "<br>$msg";
			}
		}

		return $out;
	}


	/**
	 * Creates nonexisting and updates phonenumbermanagement with order's data.
	 *
	 * @author Patrick Reichel
	 */
	protected function _update_phonenumbermanagement_with_envia_data($data, $out='') {

		Log::debug(__METHOD__." started");

		$phonenumbermanagement_changed = False;

		if (!isset($data['phonenumber_id']) && !isset($data['phonenumbermanagement_id'])) {
			Log::warning('Neither phonenumber_id nor phonenumbermanagement_id given');
			$out .= '<br> ⇒ Warning: Neither phonenumber_id nor phonenumbermanagement_id given';
			return $out;
		}

		if (isset($data['phonenumbermanagement_id'])) {
			$phonenumbermanagement = PhonenumberManagement::findOrFail($data['phonenumbermanagement_id']);
			$phonenumber = $phonenumbermanagement->phonenumber;
		}
		else {
			$phonenumber = Phonenumber::findOrFail($data['phonenumber_id']);
			$phonenumbermanagement = $phonenumber->phonenumbermanagement;
		}

		if (!isset($data['contract_id'])) {
			$data['contract_id'] = $phonenumber->mta->modem->contract->id;
			$out = _update_contract_with_envia_data($data, $out);
		}

		if (!isset($data['modem_id'])) {
			$data['modem_id'] = $phonenumber->mta->modem->id;
			$out = _update_modem_with_envia_data($data, $out);
		}

		if (isset($data['orderid'])) {
			$order = EnviaOrder::where('orderid', '=', intval($data['orderid']))->firstOrFail();

			// if there is no existing management: create and bundle with phonenumber
			if (
				(is_null($phonenumbermanagement))
				&&
				(
					(EnviaOrder::order_creates_voip_account($order))
					||
					(EnviaOrder::order_possibly_creates_voip_account($order) && boolval($data['baseno']))
					||
					(EnviaOrder::order_terminates_voip_account($order))
					||
					(EnviaOrder::order_possibly_terminates_voip_account($order) && boolval($data['baseno']))
				)
			) {
				$phonenumbermanagement = new PhonenumberManagement();
				$out .= '<br> ⇒ No PhonenumberManagement found. Creating new one – you have to set some values manually!';

				// set the correlating phonenumber id
				$phonenumbermanagement->phonenumber_id = $phonenumber->id;

				// set some default values
				$phonenumbermanagement->trcclass = 0;
				$phonenumbermanagement->porting_in = 0;
				$phonenumbermanagement->carrier_in = 0;
				$phonenumbermanagement->ekp_in = 0;
				$phonenumbermanagement->porting_out = 0;
				$phonenumbermanagement->carrier_out = 0;
				$phonenumbermanagement->ekp_out = 0;
				$phonenumbermanagement->autogenerated = 1;

				$phonenumbermanagement_changed = True;
			}

			if (
				(EnviaOrder::order_creates_voip_account($order))
				||
				(EnviaOrder::order_possibly_creates_voip_account($order) && boolval($data['baseno']))
			) {
				if (is_null($phonenumbermanagement->voipaccount_ext_creation_date)) {
					$phonenumbermanagement->voipaccount_ext_creation_date = $order->orderdate;
					$out .= '<br> ⇒ PhonenumberManagement->voipaccount_ext_creation_date set to '.$order->orderdate;
					$phonenumbermanagement_changed = True;
				}
				if (is_null($phonenumbermanagement->activation_date)) {
					$phonenumbermanagement->activation_date = $order->orderdate;
					$out .= '<br> ⇒ PhonenumberManagement->activation_date set to '.$order->orderdate;
					$phonenumbermanagement_changed = True;
				}
				if (is_null($phonenumbermanagement->external_activation_date)) {
					$phonenumbermanagement->external_activation_date = $order->orderdate;
					$out .= '<br> ⇒ PhonenumberManagement->external_activation_date set to '.$order->orderdate;
					$phonenumbermanagement_changed = True;
				}
			}
			elseif (
				(EnviaOrder::order_terminates_voip_account($order))
				||
				(EnviaOrder::order_possibly_terminates_voip_account($order) && boolval($data['baseno']))
			) {
				if (is_null($phonenumbermanagement->voipaccount_ext_termination_date)) {
					$phonenumbermanagement->voipaccount_ext_termination_date = $order->orderdate;
					$out .= '<br> ⇒ PhonenumberManagement->voipaccount_ext_termination_date set to '.$order->orderdate;
					$phonenumbermanagement_changed = True;
				}
				if (is_null($phonenumbermanagement->deactivation_date)) {
					$phonenumbermanagement->deactivation_date = $order->orderdate;
					$out .= '<br> ⇒ PhonenumberManagement->deactivation_date set to '.$order->orderdate;
					$phonenumbermanagement_changed = True;
				}
				if (is_null($phonenumbermanagement->external_deactivation_date)) {
					$phonenumbermanagement->external_deactivation_date = $order->orderdate;
					$out .= '<br> ⇒ PhonenumberManagement->external_deactivation_date set to '.$order->orderdate;
					$phonenumbermanagement_changed = True;
				}
			}
		}

		if (isset($data['enviacontract_id'])) {

			// check if phonenumber is related to given envia contract
			$phonenumbermanagement = $phonenumber->phonenumbermanagement;
			if (($phonenumbermanagement)
				&&
				($phonenumbermanagement->enviacontract_id != $enviacontract->id)
			) {
				$phonenumbermanagement->enviacontract_id = $enviacontract->id;
				$phonenumbermanagement_changed = True;
				$out .= '<br>'.$msg;
			}
		}

		// finally: check if management has been changed and save if so
		if ($phonenumbermanagement_changed) {
			$phonenumbermanagement->save();
			$msg = "Updated PhonenumberManagement $phonenumbermanagement->id";
			Log::info($msg);
		}

		return $out;
	}


	/**
	 * Extract and process usage csv.
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_misc_get_usage_csv_response($xml, $data, $out) {

		$csv_delimiter = ";";

		// result is base64 encoded csv
		$b64 = $xml->data;
		$csv = base64_decode($b64);

		// csv fieldnames are the first line
		$lines = explode("\n", $csv);
		$csv_headers = str_getcsv(array_shift($lines), $csv_delimiter);

		// array for converted data
		$results = array();

		// process Envia CSV line by line; attach orders to $result array
		foreach ($lines as $result_csv) {
			// check if current line contains data => empty lines will crash at array_combine
			if (boolval($result_csv)) {
				$result = str_getcsv($result_csv, $csv_delimiter);
				$entry = array_combine($csv_headers, $result);
				array_push($results, $entry);
			}
		}

		$out = "";

		echo "<h1>Not yet implemented in ".__METHOD__."</h1>Check ".__FILE__." (line ".__LINE__.").<h2>Returned csv is:</h2><pre>".$csv."</pre><h2>Extracted data is:</h2>";
		d($results);

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

		$out .= "<h5>Status for order ".$order_id.":</h5>";

		$out .= "<table>";

		// flag to detect if an order has to be saved or not
		$order_changed = False;

		// for each database field:
		//   - check if related data in XML is given
		//   - if so: check if data has changed to this in database
		//   - if so: change in order object and set changed flag
		//   - print the current value
		if (boolval(sprintf($xml->ordertype_id))) {
			if ($order->ordertype_id != $xml->ordertype_id) {
				$order->ordertype_id = $xml->ordertype_id;
				$order_changed = True;
			}
			$out .= "<tr><td>Ordertype ID: </td><td>".$xml->ordertype_id."</td></tr>";
		}

		if (boolval(sprintf($xml->ordertype))) {
			if ($order->ordertype != $xml->ordertype) {
				$order->ordertype = $xml->ordertype;
				$order_changed = True;
			}
			$out .= "<tr><td>Ordertype: </td><td>".$xml->ordertype."</td></tr>";
		}

		if (boolval(sprintf($xml->orderstatus_id))) {
			if ($order->orderstatus_id != $xml->orderstatus_id) {
				$order->orderstatus_id = $xml->orderstatus_id;
				$order_changed = True;
			}
			$out .= "<tr><td>Orderstatus ID: </td><td>".$xml->orderstatus_id."</td></tr>";
		}

		if (boolval(sprintf($xml->orderstatus))) {
			if ($order->orderstatus != $xml->orderstatus) {
				$order->orderstatus = $xml->orderstatus;
				$order_changed = True;
			}
			$out .= "<tr><td>Orderstatus: </td><td>".$xml->orderstatus."</td></tr>";
		}

		if (boolval(sprintf($xml->ordercomment))) {
			if ($order->ordercomment != $xml->ordercomment) {
				$order->ordercomment = $xml->ordercomment;
				$order_changed = True;
			}
			$out .= "<tr><td>Ordercomment: </td><td>".$xml->ordercomment."</td></tr>";
		}

		if (boolval(sprintf($xml->customerreference))) {
			if ($order->customerreference != $xml->customerreference) {
				$order->customerreference = $xml->customerreference;
				$order_changed = True;
			}
			$out .= "<tr><td>Customerreference: </td><td>".$xml->customerreference."</td></tr>";
		}

		if (boolval(sprintf($xml->contractreference))) {
			if ($order->contractreference != $xml->contractreference) {
				$order->contractreference = $xml->contractreference;
				$order_changed = True;
			}
			$out .= "<tr><td>Contractreference: </td><td>".$xml->contractreference."</td></tr>";
		}

		if (boolval(sprintf($xml->orderdate))) {
			if ($order->orderdate != \Str::limit($xml->orderdate, 10, '')) {
				$order->orderdate = $xml->orderdate;
				$order_changed = True;
			}
			// TODO: do we need to store the orderdate in other tables (contract, phonnumber??)
			$out .= "<tr><td>Orderdate: </td><td>".\Str::limit($xml->orderdate, 10,  '')."</td></tr>";
		}

		$out .= "</table><br>";

		// check if we have to write object to database: do so to store the date of the last update
		if ($order_changed) {
			$order->save();
			Log::info('Database table enviaorder updated for order with id '.$order_id);
			$out .= "<br><b>Table EnviaOrder updated</b>";
		}

		// update related tables if order has changed
		if ($order_changed) {

			// special case: current order was sent to cancel another order
			if (EnviaOrder::order_cancels_other_order($order)) {
				if (EnviaOrder::order_failed($order)) {
					// if cancelation of an order failed: restore the original order
					$out = $this->_process_order_get_status_response_for_cancelation_failed($order, $out);
				}
				elseif (EnviaOrder::order_successful($order)) {
					// if cancelation was successful: clear the cancelled database entry (e.g. to be able to create a contract again)
					$out = $this->_process_order_get_status_response_for_cancellation_successful($order, $out);
				}
			}

			// only use data from not deleted orders
			if (!$order->deleted_at) {

				// update enviacontract
				$out = $this->_process_order_get_status_response_for_enviacontract($order, $out);

				// update contract
				$out = $this->_process_order_get_status_response_for_contract($order, $out);

				// update modem
				$out = $this->_process_order_get_status_response_for_modem($order, $out);

				// update phonenumber
				$out = $this->_process_order_get_status_response_for_phonenumber($order, $out);

				// update phonenumbermanagement
				$out = $this->_process_order_get_status_response_for_phonenumbermanagement($order, $out);
			}
		}

		return $out;
	}


	/**
	 * Apply order changes to phonenumber.
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_order_get_status_response_for_phonenumber($order, $out) {

		$order_phonenumbers = $order->phonenumbers;
		if ($order_phonenumbers->count() == 0) {
			Log::debug('Order '.$order->id.' has no related phonenumber');
			return $out;
		}

		foreach ($order_phonenumbers as $phonenumber) {
			if ($order->contractreference) {
				// check if envia contract reference has to be set
				// do not change an existing entry – this should only be happening on contract/relocate
				// which is processed using console command provvoipenvia:process_envia_orders
				$ret = $this->_update_envia_contract_reference_on_phonenumber($phonenumber, $order->contractreference, False, False);
				if ($ret) {
					$out .= '<br>'.$ret;
				}
			}
		}

		return $out;

	}


	/**
	 * Apply order changes to phonenumbermanagement.
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_order_get_status_response_for_phonenumbermanagement($order, $out) {

		// phonenumber entry can be missing on order (e.g. on manually created orders); this information will be added by the nightly cron job – so we can stop here
		$order_phonenumbers = $order->phonenumbers;
		if ($order_phonenumbers->count() == 0) {
			Log::debug('Order '.$order->id.' has no related phonenumber');
			return $out;
		}

		foreach ($order_phonenumbers as $phonenumber) {

			$phonenumbermanagement_changed = False;

			$phonenumbermanagement = $phonenumber->PhonenumberManagement;

			// actions to perform if order handles creation of voip account
			if (EnviaOrder::order_creates_voip_account($order)) {

				// we got a new target date
	// TODO: check if this should be re-enabled (if Envia sends correct dates in orderdate)
	// as Sebastian Wiencke told me the orderdate correlates with the activation_date – but in reality this seems not to be the case
	// I think the orderdate holds the date of the last status change of the order ⇒ so for now we have to update activation_date manually…
				/* if (!\Str::startsWith($phonenumbermanagement->activation_date, $order->orderdate)) { */
				/* 	$phonenumbermanagement->activation_date = $order->orderdate; */
				/* 	Log::info('New target date for activation ('.$order->orderdate.') set in phonenumbermanagement with id '.$phonenumbermanagement->id); */
				/* 	$phonenumbermanagement_changed = True; */
				/* } */
				// all is fine: fix the activation date
				if (EnviaOrder::order_successful($order)) {
					if (!\Str::startsWith($phonenumbermanagement->external_activation_date, $order->orderdate)) {
						$phonenumbermanagement->external_activation_date = $order->orderdate;
						/* Log::info('Creation of voip account successful; will be activated on '.$order->orderdate.' (phonenumbermanagement with id '.$phonenumbermanagement->id.')'); */
						Log::info('Creation of voip account successful (phonenumbermanagement with id '.$phonenumbermanagement->id.')');
						$phonenumbermanagement_changed = True;
					}
				}
			}

			// actions to perform if order handles termination of voip account
			if (EnviaOrder::order_terminates_voip_account($order)) {
				// we got a new target date
	// TODO: check if this should be re-enabled (if Envia sends correct dates in orderdate)
	// as Sebastian Wiencke told me the orderdate correlates with the deactivation_date – but in reality this seems not to be the case
	// I think the orderdate holds the date of the last status change of the order ⇒ so for now we have to update deactivation_date manually…
				/* if (!\Str::startsWith($phonenumbermanagement->deactivation_date, $order->orderdate)) { */
				/* 	$phonenumbermanagement->deactivation_date = $order->orderdate; */
				/* 	Log::info('New target date for deactivation ('.$order->orderdate.') set in phonenumbermanagement with id '.$phonenumbermanagement->id); */
				/* 	$phonenumbermanagement_changed = True; */
				/* } */

				// all is fine: fix the deactivation date
				if (EnviaOrder::order_successful($order)) {
					if (!\Str::startsWith($phonenumbermanagement->external_deactivation_date, $order->orderdate)) {
						$phonenumbermanagement->external_deactivation_date = $order->orderdate;
						/* Log::info('Termination of voip account successful; will be deactivated on '.$order->orderdate.' (phonenumbermanagement with id '.$phonenumbermanagement->id.')'); */
						Log::info('Termination of voip account successful (phonenumbermanagement with id '.$phonenumbermanagement->id.')');
						$phonenumbermanagement_changed = True;
					}
				}
			}

			if ($phonenumbermanagement_changed) {
				$phonenumbermanagement->save();
				Log::info('Database table phonenumbermanagement updated for phonenumbermanagement with id '.$phonenumbermanagement->id);
				$out .= "<br><b>PhonenumberManagement table updated for id ".$phonenumbermanagement->id."</b>";
			};

		}

		return $out;
	}


	/**
	 * Apply order changes to enviacontract
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_order_get_status_response_for_enviacontract($order, $out) {

		$out .= "<h5>ATTENTION: processing updated order data for enviacontract not yet implemented</h5>";

		return $out;
	}


	/**
	 * Apply order changes to contract (and mayby to items)
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_order_get_status_response_for_contract($order, $out) {

		// get the related contract to check if external identifier are set
		$contract = Contract::findOrFail($order->contract_id);
		$contract_changed = False;

		// check external identifier:
		//   if not set (e.g. not known at manual creation time: update
		//   if set to different values: something went wrong!
		if (!boolval($contract->customer_external_id)) {
			$contract->customer_external_id = $order->customerreference;
			$contract_changed = True;
		}
		if ($order->customerreference != $contract->customer_external_id) {
			$msg = 'Error: Customer reference in order ('.$order->customerreference.') and contract ('.$contract->customer_external_id.') are different!';
			$out .= '<h4>'.$msg.'</h4>';
			Log::error($msg);
		}

		if ($contract_changed) {
			$contract->save();
			Log::info('Database table contract updated for contract with id '.$contract->id);
			$out .= "<br><b>Contract table updated</b>";
		};

		// finally check if there is data e.g. in items to update – use the updater from Contract.php
		// perform update only if order/get_status has been triggered manually
		// if run by cron we first get the current state for all orders and then calling the update method from EnviaOrderUpdaterCommand
		// TODO: hier weiter
		/* if (\Str::endswith(\Request::path(), '/request/order_get_status')) { */
		/* 	$updater = new VoipRelatedDataUpdaterByEnvia($order->contract_id); */
		/* }; */

		return $out;
	}


	/**
	 * Apply order changes to modem
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_order_get_status_response_for_modem($order, $out) {

		// modem entry can be missing
		if (!boolval($order->modem_id)) {
			Log::debug('Order '.$order->id.' has no related modem');
			return $out;
		}

		// get the related modem to check if external identifier is set
		$modem = Modem::findOrFail($order->modem_id);

		$modem_changed = False;

		// check external identifier:
		//   if not set (e.g. not known at manual creation time: update
		//   if set to different values: something went wrong!
		if (!boolval($modem->contract_external_id)) {
			$modem->contract_external_id = $order->contractreference;
			$modem_changed = True;
		}
		if ($order->contractreference != $modem->contract_external_id) {
			$msg = 'Error: Contract reference in order ('.$order->contractreference.') and modem ('.$modem->contract_external_id.') are different!';
			$out .= '<h4>'.$msg.'</h4>';
			Log::error($msg);
		}

		if ($modem_changed) {
			$modem->save();
			Log::info('Database table modem updated for modem with id '.$modem_id);
			$out .= "<br><b>Modem table updated</b>";
		};

		return $out;
	}


	/**
	 * Restore canceled order.
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_order_get_status_response_for_cancelation_failed($order, $out) {

		$order_to_restore = EnviaOrder::withTrashed()->find($order->related_order_id);

		if ($order_to_restore && $order_to_restore->trashed()) {

			$order_to_restore->restore();
			Log::info('Cancel of order '.$order_to_restore->id.' failed. Restored soft deleted original order');
			$out .= '<br><b>Cancelation failed. Restored order with id '.$order_to_restore->id.' (Envia ID '.$order_to_restore->orderid.')</b>';
		}

		return $out;

	}


	/**
	 * Cancellation of an order was successfull: Now we have to reset the related database entries
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_order_get_status_response_for_cancellation_successful($order, $out) {

		$cancelled_order = EnviaOrder::withTrashed()->find($order->related_order_id);
		$cancelled_method = $cancelled_order->method;

		$msg = 'Cancelation of order '.$cancelled_order->id.' (Envia ID '.$cancelled_order->orderid.', method '.$cancelled_method.') was successful.';
		Log::info($msg);
		$out .= '<br><b>'.$msg.'</b>';

		// lambda to clear phonenumbermanagement from creation data
		$clean_phonenumbermanagement_creation_data = function($cancelled_order) {
			$ret = '';
			$order_phonenumbers = $cancelled_order->phonenumbers;
			foreach ($order_phonenumbers as $phonenumber) {

				$phonenumber->contract_external_id = null;
				$phonenumber->save();

				$management = $phonenumber->phonenumbermanagement;

				$management->voipaccount_ext_creation_date = null;
				$management->external_activation_date = null;
				$management->save();

				$msg = 'Cleared data in phonenumbermanagement '.$management->id.'.';
				Log::info($msg);
				$ret .= '<br><b>'.$msg.'</b>';
			}
			return $ret;
		};


		// order cancelled creation of a contract
		if ($cancelled_method == 'contract/create') {

			// clear modem data
			$modem = $cancelled_order->modem;
			$modem->contract_external_id = null;
			$modem->contract_ext_creation_date = null;
			$modem->save();

			// clear contract data
			$contract = $cancelled_order->contract;
			$contract->customer_external_id = null;
			$contract->save();

			$msg = 'Cleared data in modem '.$modem->id.' and contract '.$contract->id.'.';
			Log::info($msg);
			$out .= '<br><b>'.$msg.'</b>';

			// creating contracts contains phonenumbers
			// we have to check this, too
			$out .= $clean_phonenumbermanagement_creation_data($cancelled_order);
		}
		// order cancelled creation of a voip account
		elseif ($cancelled_method == 'voip_account/create') {
			$out .= $clean_phonenumbermanagement_creation_data($cancelled_order);
		}
		// order cancelled termination of a voip account
		elseif ($cancelled_method == 'voip_account/terminate') {

			$order_phonenumbers = $cancelled_order->phonenumbers;
			foreach ($order_phonenumbers as $phonenumber) {
				$management = $phonenumber->phonenumbermanagement;
				$management->voipaccount_ext_termination_date = null;
				$management->external_deactivation_date = null;
				$management->save();

				$msg = 'Cleared data in phonenumbermanagement '.$management->id.'.';
				Log::info($msg);
				$out .= '<br><b>'.$msg.'</b>';
			}
		}
		// fallback: At least warn the user!
		else {
			$msg = 'Updating the database to reset related database entries for method '.$cancelled_method.' (on cancelled order '.$cancelled_order->id.' not yet implemented. Has to be done manually!!';
			Log::error($msg);
			$out .= '<h5>Attention: '.$msg.'</h5>';
		}

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
		$order_data['method'] = 'voip_account/create';
		$order_data['contract_id'] = $this->contract->id;
		$order_data['modem_id'] = $this->modem->id;
		$order_data['ordertype'] = 'voip_account/create';
		$order_data['orderstatus'] = 'initializing';

		$enviaOrder = EnviaOrder::create($order_data);

		// add entry to pivot table – there can only be one for this method
		$enviaOrder->phonenumbers()->attach($this->phonenumber->id);

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
	protected function _process_voip_account_terminate_response($xml, $data, $out) {

		// update phonenumbermanagement
		$this->phonenumbermanagement->voipaccount_ext_termination_date = date('Y-m-d H:i:s');
		$this->phonenumbermanagement->save();

		// create enviaorder
		$order_data = array();

		$order_data['orderid'] = $xml->orderid;
		$order_data['method'] = 'voip_account/terminate';
		$order_data['contract_id'] = $this->contract->id;
		$order_data['modem_id'] = $this->modem->id;
		$order_data['ordertype'] = 'voip_account/terminate';
		$order_data['orderstatus'] = 'initializing';

		$enviaOrder = EnviaOrder::create($order_data);

		// add entry to pivot table – there can only be one for this method
		$enviaOrder->phonenumbers()->attach($this->phonenumber->id);

		// view data
		$out .= "<h5>VoIP account terminated (order ID: ".$xml->orderid.")</h5>";

		return $out;
	}


	/**
	 * Process data after successful voipaccount update
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_voip_account_update_response($xml, $data, $out) {

		// create enviaorder
		$order_data = array();

		$order_data['orderid'] = $xml->orderid;
		$order_data['method'] = 'voip_account/update';
		$order_data['contract_id'] = $this->contract->id;
		$order_data['modem_id'] = $this->modem->id;
		$order_data['ordertype'] = 'voip_account/update';
		$order_data['orderstatus'] = 'initializing';

		$enviaOrder = EnviaOrder::create($order_data);

		// add entry to pivot table
		$enviaOrder->phonenumbers()->attach($this->phonenumber->id);

		// view data
		$out .= "<h5>VoIP account updated (order ID: ".$xml->orderid.")</h5>";

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
		$order_data['method'] = 'order/cancel';
		$order_data['contract_id'] = $canceled_enviaorder->contract_id;
		$order_data['ordertype'] = 'Stornierung eines Auftrags';
		$order_data['orderstatus'] = 'in Bearbeitung';
		$order_data['related_order_id'] = $canceled_enviaorder->id;
		$order_data['customerreference'] = $canceled_enviaorder->customerreference;
		$order_data['contractreference'] = $canceled_enviaorder->contractreference;

		$enviaOrder = EnviaOrder::create($order_data);

		// add entries to pivot table
		$affected_phonenumbers = $canceled_enviaorder->phonenumbers;
		foreach ($affected_phonenumbers as $phonenumber) {
			$enviaOrder->phonenumbers()->attach($phonenumber->id);
		}

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
		$order_data['method'] = 'order/create_attachment';
		$order_data['contract_id'] = $related_enviaorder->contract_id;
		$order_data['ordertype'] = 'order/create_attachment';
		$order_data['orderstatus'] = 'successful';
		$order_data['related_order_id'] = $related_order_id;
		$order_data['customerreference'] = $related_enviaorder->customerreference;
		$order_data['contractreference'] = $related_enviaorder->contractreference;

		$enviaOrder = EnviaOrder::create($order_data);

		// add entry to pivot table
		$affected_phonenumbers = $related_enviaorder->phonenumbers;
		foreach ($affected_phonenumbers as $phonenumber) {
			$enviaOrder->phonenumbers()->attach($phonenumber->id);
		}

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
		d($xml);
	}


	/**
	 * Process data after successful deletion of a phonebook entry
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_phonebookentry_delete_response($xml, $data, $out) {

		$out = "";

		echo "<h1>Not yet implemented in ".__METHOD__."</h1>Check ".__FILE__." (line ".__LINE__.")<h2>Returned XML is:</h2>";
		d($xml);
	}


	/**
	 * Process data after successful creation/change of a phonebook entry
	 *
	 * @author Patrick Reichel
	 */
	protected function _process_phonebookentry_get_response($xml, $data, $out) {

		$out = "";

		echo "<h1>Not yet implemented in ".__METHOD__."</h1>Check ".__FILE__." (line ".__LINE__.")<h2>Use returned data to create new or update existing phonebookentry</h2><h2>Returned XML is:</h2>";
		d($xml);
	}


	/**
	 * This method returns HTML containing error message or array of free Envia phonenumbers.
	 *
	 * It uses the request() method in controller, which has been extended to return HTML instead of a view.
	 *
	 * @author Patrick Reichel
	 */
	public static function get_free_numbers_for_view() {

		// manipulate \Input to perform action against Envia without confirmation
		\Input::merge(array('really' => 'true'));
		// manipulate \Input to return flat html instead of a view
		\Input::merge(array('return_type' => 'html'));

		// get a controller instance and execute the request
		$c = new ProvVoipEnviaController();
		$ret = $c->request('misc_get_free_numbers');

		// get rid of (here senseless) debug information in case of success
		if (substr_count($ret, '<h4>Success ') > 0) {
			$ret = explode('<hr><h4>DEBUG', $ret)[0];
		}
		else {
			// error: return the html string as is
			return $ret;
		}

		// extract numbers if any and make $ret an array
		$pattern = '#[0-9]+/[0-9]+#';
		preg_match_all($pattern, $ret, $free_numbers);
		$ret = $free_numbers[0];

		// this now is an array containing all numbers
		return $ret;

	}

}
