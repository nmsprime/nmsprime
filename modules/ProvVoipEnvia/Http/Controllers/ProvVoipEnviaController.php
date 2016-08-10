<?php

namespace Modules\Provvoipenvia\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

use Modules\ProvVoipEnvia\Entities\ProvVoipEnvia;
use Modules\ProvVoipEnvia\Exceptions\XmlCreationError;

class ProvVoipEnviaController extends \BaseController {

	// TODO: @Patrick Reichel: is this field required ?
	public $name = 'VOIP';


	/**
	 * Constructor.
	 *
	 * @author Patrick Reichel
	 */
	public function __construct() {

		// we need to create the model manually
		$this->model = new ProvVoipEnvia();

		// store the called entry method => later needed for different output (echo vs. view)
		// don't try to extract via explode from Request::url => e.g. „php artisan route:list“ crashes with index out of bound…
		$url = \Request::url();
		if (\Str::contains($url, '/request/')) {
			$this->entry_method = 'request';
		}
		elseif (\Str::contains($url, '/cron/')) {
			$this->entry_method = 'cron';
		}
		elseif (\Str::contains($url, '/index/')) {
			$this->entry_method = 'index';
		}
		else {
			$this->entry_method = '';
		}

		// build base URL of the envia API
		$domain = isset($_ENV['PROVVOIPENVIA__REST_API_URL']) ? $_ENV['PROVVOIPENVIA__REST_API_URL'] : '';
		$sub_url = '/api/rest/v1/';
		$this->base_url = $domain.$sub_url;

		parent::__construct();
	}


	/**
	 * Checks if API version is set.
 	 *
	 * @author Patrick Reichel
	 */
	public function check_api_version($job) {

		if ($this->model->api_version < 0) {
			throw new \InvalidArgumentException('Error performing '.$job.': PROVVOIPENVIA__REST_API_VERSION in .env has to be set to a positive float value (e.g.: 1.4) ⇒ ask your admin for proper values');
		}

	}


	/**
	 * Entry method for cron jobs.
	 * Here we can use a different level of authentication – a cron job typically acts not as a logged in user :-)
	 * So with this in mind we have to restrict the possible actions that can be performed from here.
	 * There will be no return value – all output will be directly printed using echo (and can be collected by curl, wget or what else)
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job comes from the route ([…]/provvoipenvia/request/{job})
	 */
	public function cron($job) {

		$base_url = $this->base_url;
		$client_ip = \Request::getClientIp();
		$request_uri = \Request::getUri();
		$origin = \URL::to('/');	// origin is not relevant in cron jobs; set only for compatibility reasons…

		$this->check_api_version('cron job');

		// as this method is not protected by normal auth mechanism we will allow only a small number of jobs
		$allowed_cron_jobs = array(
			'misc_get_orders_csv' => $base_url.'misc/get_orders_csv',
			'order_get_status' => $base_url.'order/get_status',
			'contract_get_voice_data' => $base_url.'contract/get_voice_data',
		);

		// allowed client IPs – currently restricted to localhost
		$allowed_client_ips = array();
		$raw = explode(',', $_ENV['PROVVOIPENVIA__IPS_ALLOWED_FOR_CRON']);
		foreach ($raw as $ip) {
			array_push($allowed_client_ips, trim($ip));
		};

		$alternate_uri = str_replace("/cron/", "/request/", $request_uri);

		// if something else is requested: die with error message
		if (!array_key_exists($job, $allowed_cron_jobs)) {
			echo "ERROR: Job ".$job." not allowed in method cron.<br>Try <a href=\"".$alternate_uri."\" target=\"_self\">".$alternate_uri."</a> instead.";
			\Log::error("ERROR: Job ".$job." not allowed in ProvVoipEnviaController.cron (Request URI was ".$request_uri.")");
			exit(1);
		}

		if (!in_array($client_ip, $allowed_client_ips)) {
			echo "ERROR: Client IP ".$client_ip." not allowed in method cron.<br>Try <a href=\"".$alternate_uri."\" target=\"_self\">".$alternate_uri."</a> instead.";
			\Log::error("ERROR: Client IP ".$client_ip." not allowed in method cron (Request URI was ".$request_uri.").");
			exit(1);
		}

		// the requests payload (=XML), also
		$payload = $this->model->get_xml($job);

		// execute only if job is currently allowed
		if (!$this->_job_allowed($job)) {
			$view_var = $this->_show_job_not_allowed_info($job, $origin);
		}
		else {

			// the API URL to use for the request
			$url = $allowed_cron_jobs[$job];

			// the requests payload (=XML)
			$payload = $this->model->get_xml($job);

			$view_var = $this->_perform_request($url, $payload, $job);
		}
		print_r($view_var);
	}

	/**
	 * Overwrite index.
	 * temporary starter for xml generation
	 */
	public function index() {

		// check if user has the right to perform actions against Envia API
		\App\Http\Controllers\BaseAuthController::auth_check('view', 'Modules\ProvVoipEnvia\Entities\ProvVoipEnvia');

		$base = "/lara/provvoipenvia/request";

		$jobs = array(
			['api' => 'selfcare', 'link' => 'blacklist_get?phonenumber_id=300001&amp;envia_blacklist_get_direction=in'],
			['api' => 'selfcare', 'link' => 'blacklist_get?phonenumber_id=300001&amp;envia_blacklist_get_direction=out'],
			['api' => 'selfcare', 'link' => 'calllog_get_status?contract_id=500000'],
			['api' => 'selfcare', 'link' => 'configuration_get?phonenumber_id=300001'],
			['api' => 'order', 'link' => 'contract_change_tariff?contract_id=500010'],
			['api' => 'order', 'link' => 'contract_change_variation?contract_id=500010'],
			['api' => 'order', 'link' => 'contract_create?contract_id=500000'],
			['api' => 'order', 'link' => 'contract_get_voice_data?contract_id=500000'],
			['api' => 'order', 'link' => 'contract_terminate?contract_id=500000'],
			['api' => 'order', 'link' => 'customer_update?contract_id=500000'],
			['api' => 'order', 'link' => 'misc_ping'],
			['api' => 'order', 'link' => 'misc_get_free_numbers'],
			['api' => 'order', 'link' => 'misc_get_free_numbers?localareacode=03735'],
			['api' => 'order', 'link' => 'misc_get_free_numbers?localareacode=03735&amp;baseno=7696'],
			['api' => 'order', 'link' => 'misc_get_orders_csv'],
			['api' => 'order', 'link' => 'misc_get_usage_csv'],
			['api' => 'order', 'link' => 'order_cancel?order_id='],
			['api' => 'order', 'link' => 'order_get_status?order_id=72950'],
			['api' => 'order', 'link' => 'voip_account_create?phonenumber_id=300001'],
			['api' => 'order', 'link' => 'voip_account_terminate?phonenumber_id=300001'],
			['api' => 'order', 'link' => 'voip_account_update?phonenumber_id=300001'],
			['api' => '', 'link' => ''],
			['api' => 'selfcare', 'link' => 'blacklist_create_entry'],
			['api' => 'selfcare', 'link' => 'blacklist_delete_entry'],
			['api' => 'selfcare', 'link' => 'calllog_delete'],
			['api' => 'selfcare', 'link' => 'calllog_delete_entry'],
			['api' => 'selfcare', 'link' => 'calllog_get'],
			['api' => 'selfcare', 'link' => 'configuration_update'],
			['api' => 'order', 'link' => 'contract_change_method'],
			['api' => 'order', 'link' => 'contract_change_sla'],
			['api' => 'order', 'link' => 'contract_get_reference'],
			['api' => 'order', 'link' => 'contract_lock'],
			['api' => 'order', 'link' => 'contract_unlock'],
			['api' => 'selfcare', 'link' => 'customer_get_reference'],
			['api' => 'order', 'link' => 'order_add_mgcp_details'],
			['api' => 'order', 'link' => 'order_create_attachment?order_id=73013&amp;enviaorderdocument_id=7'],
			['api' => 'order', 'link' => 'order_get_status'],
			['api' => 'order', 'link' => 'phonebookentry_create'],
			['api' => 'order', 'link' => 'phonebookentry_delete'],
			['api' => 'order', 'link' => 'phonebookentry_get'],
		);

		echo '<h3>Selfcare-API is not active ⇒ links will not be shown</h3>';
		foreach ($jobs as $job) {
			if (!boolval($job['link'])) {
				echo "<hr>";
				continue;
			}
			if ($job['api'] != "selfcare") {
				echo '<a href="'.$base.'/'.$job['link'].'" target="_self">'.$job['api'].': '.$job['link'].'</a><br>';
			}
		}
	}

	/**
	 * Performs https request against envial using given URL and payload (XML)
	 *
	 * @author Patrick Reichel
	 *
	 * @param $url URL to use
	 * @param $payload string containing XML to send
	 * @return array containing informations about errors, the http status and the received data
	 */
	protected function _ask_envia($url, $payload) {

		$curl_options = $this->_get_curl_headers($url, $payload);

		// create a new cURL resource
		$ch = curl_init();

		// setting the cURL options
		curl_setopt_array($ch, $curl_options);

		// default values for data array
		$data = array(
			'error' => FALSE,
			'error_type' => null,
			'error_msg' => null,
			'status' => null,
			'xml' => null,
		);

		try {

			// perform cURL session
			$ret = curl_exec($ch);

			// check for errors
			if (curl_errno($ch)) {
				$data['error'] = TRUE;
				$data['error_type'] = "cURL error";
				$data['error_msg'] = curl_error($ch);
			}
			// or get data
			else {
				$data['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$data['xml'] = $ret;
			}
		}
		catch (Exception $ex) {
			$data['error'] = TRUE;
			$data['error_type'] = 'Exception';
			$data['error_msg'] = $ex->getMessage();
		}

		// free the resource
		curl_close($ch);

		return $data;

	}

	/**
	 * Helper to generate the cURL options to use
	 *
	 * @author Patrick Reichel
	 *
	 * @param $url URL to visit
	 * @param $payload data to send
	 *
	 * @return array with cURL options to be set before the request
	 */
	protected function _get_curl_headers($url, $payload) {

		// headers for http request
		$http_headers = array(
			"Content-type: text/xml;charset=\"utf-8\"",
			"Accept: text/xml",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
		);

		// defining cURL options (http://php.net/manual/en/function.curl-setopt.php)
		$curl_options = array(

			// basic options
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => $http_headers,

			// method and data to use
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => $payload,

			// verify peer's certificate to prevent MITM attacks
			CURLOPT_SSL_VERIFYPEER => TRUE,
			// check for common name in cert and match to the hostname provided
			CURLOPT_SSL_VERIFYHOST => 2,

			// verbose mode?
			CURLOPT_VERBOSE => FALSE,

			// return server answer instead of echoing it instantly
			CURLOPT_RETURNTRANSFER => TRUE,
		);

		return $curl_options;

	}


	/**
	 * Send data to Envia and process result.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $url URL for webservice
	 * @param $payload data to transmit (XML)
	 * @param $job job to do
	 * @return data for view (currently plain HTML)
	 */
	protected function _perform_request($url, $payload, $job) {

		/* echo "<h3>We are not sending data to Envia yet! Will now exit…</h3>"; */
		/* exit(); */

		// perform the request and receive the result (meta and content)
		$data = $this->_ask_envia($url, $payload);
		$data['entry_method'] = $this->entry_method;

		// major problem!!
		if ($data['error']) {
			$view_var = $this->_handle_curl_error($job, $data);
		}
		// got an answer
		else {
			$view_var = $this->_handle_curl_success($job, $data);
		}

		return $view_var;
	}


	/**
	 * Checks if a job is allowed to be done.
	 * Use this before sending data to envia to prevent e.g. double creation of contracts (if user presses <F5> in success screen)
	 *
	 * This defaults to false – so you have to whitelist all the methods you are going to use.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job to do
	 *
	 * @return true if job is allowed, false else
	 */
	protected function _job_allowed($job) {

		// these jobs are allowed in every case
		$unrestricted_jobs = array(
			'misc_ping',
			'misc_get_free_numbers',
			'misc_get_orders_csv',
			'misc_get_usage_csv',
			'order_cancel',
			'order_create_attachment',
			'order_get_status',
		);

		if (in_array($job, $unrestricted_jobs)) {
			return true;
		}

		// perform checks for the rest of the jobs
		if ($job == "contract_create") {
			$this->model->extract_environment($this->model->modem, 'modem');

			// contract creation is only allowed once (you cannot re-create a contract)
			if ($this->model->contract_created) {
				return false;
			}

			return true;
		}

		if ($job == "contract_get_voice_data") {
			$this->model->extract_environment($this->model->modem, 'modem');

			// only can get data for a contract that exists (or existed)
			if (!$this->model->contract_created) {
				return false;
			}

			return true;
		}

		if ($job == 'contract_change_tariff') {
			$this->model->extract_environment($this->model->modem, 'modem');

			// only can get data for a contract that exists
			if (!$this->model->contract_available) {
				return false;
			}

			if (!boolval($this->model->contract->next_voip_id)) {
				return false;
			}

			if ($this->model->contract->voip_id == $this->model->contract->next_voip_id) {
				return false;
			}

			return true;

		}

		if ($job == 'contract_change_variation') {
			$this->model->extract_environment($this->model->modem, 'modem');

			// only can get data for a contract that exists
			if (!$this->model->contract_available) {
				return false;
			}

			if (!boolval($this->model->contract->next_purchase_tariff)) {
				return false;
			}

			if ($this->model->contract->voip_id == $this->model->contract->next_purchase_tariff) {
				return false;
			}

			return true;

		}

		if ($job == 'contract_relocate') {
			$this->model->extract_environment($this->model->modem, 'modem');

			// only can change data for a contract that exists
			if (!$this->model->contract_available) {
				return false;
			}

			return true;

		}

		if ($job == "customer_update") {
			$this->model->extract_environment($this->model->contract, 'contract');

			// Customer can only be updated if active contract exists
			if (!$this->model->at_least_one_contract_available) {
				return false;
			}

			return true;
		}

		if ($job == "voip_account_create") {
			$this->model->extract_environment($this->model->phonenumbermanagement, 'phonenumbermanagement');

			// can only be created if not yet created
			if ($this->model->voipaccount_created) {
				return false;
			}

			return true;
		}

		if ($job == "voip_account_terminate") {
			$this->model->extract_environment($this->model->phonenumbermanagement, 'phonenumbermanagement');

			// can only be terminated if available
			if (!$this->model->voipaccount_available) {
				return false;
			}

			return true;
		}

		if ($job == "voip_account_update") {
			$this->model->extract_environment($this->model->phonenumbermanagement, 'phonenumbermanagement');

			// can only be terminated if available
			if (!$this->model->voipaccount_available) {
				return false;
			}

			return true;
		}

		if ($job == "phonebookentry_create") {

			$this->model->extract_environment($this->model->phonebookentry, 'phonebookentry');

			// always allowed as this method is also used to change an existing phonebookentry
			return true;
		}

		if ($job == "phonebookentry_delete") {

			$this->model->extract_environment($this->model->phonebookentry, 'phonebookentry');

			// can only be created if not existing
			if ($this->model->phonebookentry_created) {
				return true;
			}
			else {
				return false;
			}
		}

		if ($job == "phonebookentry_get") {

			$this->model->extract_environment($this->model->phonebookentry, 'phonebookentry');
			// always allowed
			return true;
		}



		/* elseif (\Str::startswith($job, 'voipaccount_')) { */
		/* 	$this->model->extract_environment($this->model->voipaccount, 'voipaccount_'); */
		/* } */


		// forbid every other action by default
		// using a whitelist prevents you from forgetting something
		return false;
	}


	/**
	 * Generates the view content if a job is not allowed to do.
	 *
	 * @author Patrick Reichel
	 */
	protected function _show_job_not_allowed_info($job, $origin) {

		$ret = array();
		$ret['plain_html'] = '';
		$ret['plain_html'] .= '<h4>Error</h4>';
		$ret['plain_html'] .= 'Job '.$job.' is currently not allowed';
		$ret['plain_html'] .= '<h5><b><a href="'.urldecode($origin).'">Bring me back </h5>';
		return $ret;
	}


	protected function _show_xml_creation_error($msg, $origin) {

		$ret = array();

		$ret['plain_html'] = '';
		$ret['plain_html'] .= "<h4>There was error creating XML to be sent to Envia:</h4>";
		$ret['plain_html'] .= "<h5>".$msg."</h5><br><br>";

		$ret['plain_html'] .= '<h5><b><a href="'.urldecode($origin).'">Bring me back…</a>';

		return $ret;
	}

	/**
	 * Get confirmation to continue with chosen action.
	 * Used for every job that changes data at Envia.
	 *
	 * @author Patrick Reichel
	 * @param $payload generated XML
	 */
	protected function _show_confirmation_request($payload, $url, $origin) {

		$ret = array();

		$ret['plain_html'] = '';
		$ret['plain_html'] .= "<h4>Data to be sent to Envia</h4>";
		$ret['plain_html'] .= "URL: ".$url."<br><br>";
		$ret['plain_html'] .= "<pre>";
		$ret['plain_html'] .= ProvVoipEnvia::prettify_xml($payload, True);
		$ret['plain_html'] .= "</pre>";

		$ret['plain_html'] .= "<h4>You are going to change data at Envia! Proceed?</h4>";

		$ret['plain_html'] .= '<h5><b><a href="'.urldecode($origin).'">NOOO! Please bring me back…</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

		// prefix for GET param: ? if the only one, &amp; else
		if (strpos(\Request::getRequestUri(), '?') === False) {
			$attach_prefix = '?';
		}
		else {
			$attach_prefix = '&amp;';
		}
		$ret['plain_html'] .= '<a href="'.\Request::getRequestUri().$attach_prefix.'really=True" target="_self">Yes, send data now!</a></b></h5>';

		return $ret;
	}


	/**
	 * Helper to show the generated XML (in original and pretty shape)
	 * Use this for debugging the XML output and input
	 *
	 * @author Patrick Reichel
	 *
	 * @param $xml xml for debug output
	 * @return data for view (currently plain HTML)
	 */
	private function __debug_xml($xml) {

		$ret = '';
		$ret .= "<pre style=\"border: solid 1px #444; padding: 10px\">";
		$ret .= "<h5>Pretty:</h5>";

		$ret .= ProvVoipEnvia::prettify_xml($xml, False);

		$ret .= "<br><hr>";
		$ret .= "<h5>Original:</h5>";
		$ret .= htmlentities($xml);
		$ret .= "</pre>";

		return $ret;
	}

	/**
	 * Method to perform a request the envia API.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job comes from the route ([…]/provvoipenvia/request/{job})
	 * @return view for showing the data
	 */
	public function request($job) {

		// check if user has the right to perform actions against Envia API
		\App\Http\Controllers\BaseAuthController::auth_check('view', 'Modules\ProvVoipEnvia\Entities\ProvVoipEnvia');

		$this->check_api_version('request');

		$base_url = $this->base_url;

		// the URLs to use for the jobs to do
		$urls = array(
			/* 'blacklist_create_entry' => $base_url.'____TODO____', */
			/* 'blacklist_delete_entry' => $base_url.'____TODO____', */
			/* 'blacklist_get' => $base_url.'selfcare/blacklist/get', */

			/* 'calllog_delete' => $base_url.'____TODO____', */
			/* 'calllog_delete_entry' => $base_url.'____TODO____', */
			/* 'calllog_get' => $base_url.'____TODO____', */
			/* 'calllog_get_status' => $base_url.'selfcare/calllog/get_status', */

			/* 'configuration_get' => $base_url.'selfcare/configuration/get', */
			/* 'configuration_update' => $base_url.'____TODO____', */

			'contract_change_method' => $base_url.'____TODO____',
			'contract_change_sla' => $base_url.'____TODO____',
			'contract_change_tariff' => $base_url.'contract/change_tariff',
			'contract_change_variation' => $base_url.'contract/change_variation',
			'contract_create' => $base_url.'contract/create',
			'contract_get_reference' => $base_url.'____TODO____',
			'contract_get_voice_data' => $base_url.'contract/get_voice_data',
			'contract_lock' => $base_url.'____TODO____',
			'contract_relocate' => $base_url.'contract/relocate',
			'contract_terminate' => $base_url.'contract/terminate',
			'contract_unlock' => $base_url.'____TODO____',

			'customer_get_reference' => $base_url.'____TODO____',
			'customer_update' => $base_url.'customer/update',

			'misc_get_free_numbers' => $base_url.'misc/get_free_numbers',
			'misc_get_orders_csv' => $base_url.'misc/get_orders_csv',
			'misc_get_usage_csv' => $base_url.'misc/get_usage_csv',
			'misc_ping' => $base_url.'misc/ping',

			'order_add_mgcp_details' => $base_url.'____TODO____',
			'order_cancel' => $base_url.'order/cancel',
			'order_create_attachment' => $base_url.'order/create_attachment',
			'order_get_status' => $base_url.'order/get_status',

			'phonebookentry_create' => $base_url.'phonebookentry/create',
			'phonebookentry_delete' => $base_url.'phonebookentry/delete',
			'phonebookentry_get' => $base_url.'phonebookentry/get',

			'voip_account_create' => $base_url.'voip_account/create',
			'voip_account_terminate' => $base_url.'voip_account/terminate',
			'voip_account_update' => $base_url.'voip_account/update',
		);

		// TODO: improve error handling: Throwing an exception is a bit hard :-)
		if (!array_key_exists($job, $urls)) {
			/* throw new \Exception("Job ".$job." not implemented yet"); */
			abort(404);
		}

		// the API URL to use for the request
		$url = $urls[$job];

		// for devel phase: die if URL is not set
		if (\Str::contains($url, "TODO")) {
			throw new \Exception("Missing url: ".$url);
		}

		// the requests payload (=XML)
		$xml_creation_failed = True;
		try {
			$payload = $this->model->get_xml($job);
			$xml_creation_failed = False;
		}
		catch (XmlCreationError $ex) {
			$payload = $ex->getMessage();
		}
		catch (\Exception $ex) {
			throw $ex;
		}

		// extract origin
		$origin = \Input::get('origin', \URL::to('/'));

		$view_header = 'Request Envia';

		$view_path = \NamespaceController::get_view_name().'.request';

		if ($xml_creation_failed) {
			$view_var = $this->_show_xml_creation_error($payload, $origin);
		}
		else {
			// check if job to do is allowed
			// e.g. to prevent double contract creation on pressing <F5>
			if (!$this->_job_allowed($job)) {
				$view_var = $this->_show_job_not_allowed_info($job, $origin);
			}
			// on jobs changing data at Envia: Ask if job shall be performed
			elseif (!\Input::get('really', False)) {
				$view_var = $this->_show_confirmation_request($payload, $url, $origin);
			}
			else {

				$view_var = $this->_perform_request($url, $payload, $job);

				// add link to original page
				$origin_link = '<hr>';
				$origin_name = urldecode($origin);
				$origin_name = explode($_SERVER['CONTEXT_PREFIX'], $origin_name);
				$origin_name = array_pop($origin_name);
				$origin_link .= '<h5><b><a href="'.urldecode($origin).'" target="_self">Back to '.$origin_name.'</a></b></h5>';
				$view_var['plain_html'] .= $origin_link;
			}

			// check if there should be an instant redirect – if so do so :-)
			if (\Input::get('instant_redirect', false)) {
				// we have to add the GET param manually as Redirect::to()->with('recentlty_updated', true) is not running
				// this param is used to break out of the endless redirect loop :-)
				return \Redirect::to(urldecode($origin).'?recently_updated=1');
			}
		}

		return View::make($view_path, $this->compact_prep_view(compact('model_name', 'view_header', 'view_var')));
	}

	/**
	 * Method to handle exceptions and curl errors
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job which should have been done
	 * @param $data collected data from request try
	 * @return data for view (currently plain HTML)
	 */
	protected function _handle_curl_error($job, $data) {

		$ret = array();
		$ret['plain_html'] = "ERROR! We got an ".$data['error_type'].": ".$data['error_msg']." executing job ".$job;

		return $ret;
	}

	/**
	 * Method to handle successful request (on cURL level).
	 * Mainly used to separate further process using the HTTP status code.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job which should have been done
	 * @param $data collected data from request try
	 * @return data for view (currently plain HTML)
	 */
	protected function _handle_curl_success($job, $data) {

		// in the following if statement we decide the method to call by HTTP status codes in API respond
		// first we handle all specific errors, then success and finally process all not specific errors

		// success!!
		if (($data['status'] >= 200) && ($data['status'] < 300)) {
			$view_var = $this->_handle_request_success($job, $data);
		}
		// a 404 on order_get_status is meaningful ⇒ we have to delete this order
		// so let's handle this with the success logic
		elseif (($job == "order_get_status") && ($data['status'] == 404)) {
			$view_var = $this->_handle_request_success($job, $data);
		}
		// TODO: should we handle some of the errors in a special way?
		/* // bad request */
		/* elseif ($data['status'] == 400) { */
		/* 	$view_var = $this->_handle_request_failed_400($job, $data); */
		/* } */
		/* // unauthorized */
		/* elseif ($data['status'] == 401) { */
		/* 	$view_var = $this->_handle_request_failed_401($job, $data); */
		/* } */
		/* // forbidden */
		/* elseif ($data['status'] == 403) { */
		/* 	$view_var = $this->_handle_request_failed_403($job, $data); */
		/* } */
		/* // not found */
		/* elseif ($data['status'] == 404) { */
		/* 	$view_var = $this->_handle_request_failed_404($job, $data); */
		/* } */
		// none of the above (fallback) => use generic error handling
		else {
			$view_var = $this->_handle_request_failed($job, $data);
		}

		if ($this->entry_method != 'cron') {
			if (\Config::get('app.debug')) {
				$view_var['plain_html'] .= "<hr>";
				$view_var['plain_html'] .= "<h4>DEBUG mode enabled in .env</h4>";
				$view_var['plain_html'] .= "return data:<br>";
				$view_var['plain_html'] .= "<pre>";
				$view_var['plain_html'] .= ProvVoipEnvia::prettify_xml($data['xml']);
				$view_var['plain_html'] .= "</pre>";
			}
		}
		return $view_var;
	}

	/* /** */
	/*  * Process rest answers with http error status 400 (Bad request) */
	/*  * */
	/*  * @author Patrick Reichel */
	/*  * */
	/*  * @param $job job which should have been done */
	/*  * @param $data collected data from request try */
	/*  * @return data for view (currently plain HTML) */
	/*  *1/ */
	/* protected function _handle_request_failed_400($job, $data) { */

	/* 	$errors = $this->model->get_error_messages($data['xml']); */

	/* 	$ret = ''; */

	/* 	$ret .= "<h4>The following errors occured:</h4>"; */
	/* 	$ret .= "<table style=\"background-color: #faa\">"; */
	/* 	foreach ($errors as $error) { */
	/* 		if (boolval($error['status']) || boolval($error['message'])) { */
	/* 			$ret .= "<tr>"; */
	/* 			$ret .= "<td>"; */
	/* 				$ret .= $error['status'].': '; */
	/* 			$ret .= "</td>"; */
	/* 			$ret .= "<td>"; */
	/* 				$ret .= $error['message']; */
	/* 			$ret .= "</td>"; */
	/* 			$ret .= "</tr>"; */
	/* 		} */
	/* 	} */
	/* 	$ret .= "</table>"; */

	/* 	return array('plain_html' => $ret); */
	/* } */

	/* /** */
	/*  * Process rest answers with http error status 401 (Access denied) */
	/*  * */
	/*  * @author Patrick Reichel */
	/*  * */
	/*  * @param $job job which should have been done */
	/*  * @param $data collected data from request try */
	/*  * @return data for view (currently plain HTML) */
	/*  *1/ */
	/* protected function _handle_request_failed_401($job, $data) { */

	/* 	$errors = $this->model->get_error_messages($data['xml']); */

	/* 	$ret = ''; */

	/* 	$ret .= "<h4>Error (HTTP status code ".$data['status'].")</h4>"; */
	/* 	$ret .= "<table style=\"background-color: #faa\">"; */
	/* 	foreach ($errors as $error) { */
	/* 		if (boolval($error['status']) || boolval($error['message'])) { */
	/* 			$ret .= "<tr>"; */
	/* 			$ret .= "<td>"; */
	/* 				$ret .= $error['status'].': '; */
	/* 			$ret .= "</td>"; */
	/* 			$ret .= '<td style="padding-left: 10px;">'; */
	/* 				$ret .= $error['message']; */
	/* 			$ret .= "</td>"; */
	/* 			$ret .= "</tr>"; */
	/* 		} */
	/* 	} */
	/* 	$ret .= "</table>"; */

	/* 	return array('plain_html' => $ret); */
	/* } */

	/**
	 * Process rest answers with http other error status
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job which should have been done
	 * @param $data collected data from request try
	 * @return data for view (currently plain HTML)
	 */
	protected function _handle_request_failed($job, $data) {

		$errors = $this->model->get_error_messages($data['xml']);

		if ($this->entry_method == 'cron') {
			foreach ($errors as $error) {
				if (boolval($error['status']) || boolval($error['message'])) {
					echo 'Error '.$error['status'].' occured on job '.$job.': '.$error['message'];
					Log::error('Error '.$error['status'].' occured on job '.$job.': '.$error['message']);
				}
			}
			Log::error('Exiting cronjob because of the above errors.');
			echo 'Exiting cronjob because of the above errors.';
			exit(1);
		}
		else {
			$ret = '';

			$ret .= "<h4>The following error(s) occured:</h4>";
			$ret .= "<table style=\"background-color: #fcc; color: #000; font-size: 1.05em; font-family: monospace\">";
			foreach ($errors as $error) {
				if (boolval($error['status']) || boolval($error['message'])) {
					$ret .= "<tr>";
					$ret .= "<td style=\"padding: 2px\">";
						$ret .= $error['status'].': ';
					$ret .= "</td>";
					$ret .= '<td style="padding: 2px; padding-left: 10px;">';
						$ret .= $error['message'];
					$ret .= "</td>";
					$ret .= "</tr>";
				}
			}
			$ret .= "</table>";

			return array('plain_html' => $ret);
		}
	}

	/**
	 * Process successfully performed REST request.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job which should have been done
	 * @param $data collected data from request try
	 * @return data for view (currently plain HTML)
	 */
	protected function _handle_request_success($job, $data) {

		$ret = $this->model->process_envia_data($job, $data);

		if ($this->entry_method == 'cron') {
			return $ret;
		}
		else {
			return array('plain_html' => $ret);
		}
	}

}
