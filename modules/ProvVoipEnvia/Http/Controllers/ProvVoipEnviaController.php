<?php

namespace Modules\Provvoipenvia\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

use Modules\ProvVoipEnvia\Entities\ProvVoipEnvia;

class ProvVoipEnviaController extends \BaseModuleController {


	/**
	 * Constructor.
	 *
	 * @author Patrick Reichel
	 */
	public function __construct() {

		// we need to create the model manually
		$this->model = new ProvVoipEnvia();

		// store the called entry method => later needed for different output (echo vs. view)
		$url = \Request::url();
		$tmp = explode('provvoipenvia/', $url)[1];
		$this->entry_method = explode('/', $tmp)[0];

		// build base URL of the envia API
		$domain = $_ENV['PROVVOIPENVIA__REST_API_URL'];
		$sub_url = '/api/rest/v1/';
		$this->base_url = $domain.$sub_url;

		parent::__construct();
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

		// as this method is not protected by normal auth mechanism we will allow only a small number of jobs
		$allowed_cron_jobs = array(
			'misc_get_orders_csv' => $base_url.'misc/get_orders_csv',
		);

		// if something else is requested: die with error message
		if (!array_key_exists($job, $allowed_cron_jobs)) {
			echo "ERROR: Job ".$job." not allowed in method cron. Try request instead.";
			exit(1);
		}

		// the API URL to use for the request
		$url = $allowed_cron_jobs[$job];

		// the requests payload (=XML)
		$payload = $this->model->get_xml($job);

		$view_var = $this->_perform_request($url, $payload, $job);
		echo $view_var;
	}

	/**
	 * Overwrite index.
	 * temporary starter for xml generation
	 */
	public function index() {

		$base = "/lara/provvoipenvia/request";

		$jobs = array(
			'blacklist_get?phonenumber_id=300001&amp;envia_blacklist_get_direction=in',
			'blacklist_get?phonenumber_id=300001&amp;envia_blacklist_get_direction=out',
			'calllog_get_status?contract_id=500000',
			'configuration_get?phonenumber_id=300001',
			'contract_create?contract_id=500000',
			'contract_get_voice_data?contract_id=500000',
			'contract_terminate?contract_id=500000',
			'customer_update?contract_id=500000',
			'misc_ping',
			'misc_get_free_numbers',
			'misc_get_free_numbers?localareacode=03735',
			'misc_get_free_numbers?localareacode=03735&amp;baseno=7696',
			'misc_get_orders_csv',
			'misc_get_usage_csv',
			'order_get_status?order_id=72950',
			'voip_account_create?phonenumber_id=300001',
			'voip_account_terminate?phonenumber_id=300001',
			'',
			'blacklist_create_entry',
			'blacklist_delete_entry',
			'calllog_delete',
			'calllog_delete_entry',
			'calllog_get',
			'configuration_update',
			'contract_change_method',
			'contract_change_sla',
			'contract_change_tariff',
			'contract_change_variation',
			'contract_get_reference',
			'contract_lock',
			'contract_unlock',
			'customer_get_reference',
			'order_add_mgcp_details',
			'order_cancel',
			'order_create_attachment',
			'order_get_status',
			'phonebookentry_create',
			'phonebookentry_delete',
			'phonebookentry_get',
			'voip_account_update',
		);

		foreach ($jobs as $job) {
			if (!boolval($job)) {
				echo "<hr>";
				continue;
			}
			echo '<a href="'.$base.'/'.$job.'" target="_self">'.$job.'</a><br>';
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
	 * @author Patrick Reichel
	 *
	 * @param $job job to do
	 *
	 * @return true if job is allowed, false else
	 */
	protected function _job_allowed($job) {

		// get the models environment
		if (\Str::startswith($job, 'contract_')) {
			$this->model->extract_environment($this->model->contract, 'contract');
		}
		elseif (\Str::startswith($job, 'voipaccount_')) {
			$this->model->extract_environment($this->model->voipaccount, 'voipaccount_');
		}

		// run the checks
		if ($job == "contract_create") {
			// contract creation is only allowed once (you cannot re-create a contract)
			if (!isset($this->model->contract_created)) {
				dd($this);
			}
			if ($this->model->contract_created) {
				return false;
			}
		}

		// no restrictions (default)
		return true;
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
		$ret['plain_html'] = "<h4>Data to be sent to Envia</h4>";
		$ret['plain_html'] .= "URL: ".$url."<br><br>";
		$ret['plain_html'] .= "<pre>";
		$ret['plain_html'] .= $this->_prettify_xml($payload, True);
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
	 * Prettify xml for output on screen.
	 * Use e.g. for debugging.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $xml string containing xml data
	 * @param $hide_credentials don't show username/password if set to True
	 * @return string containing prettified xml
	 */
	protected function _prettify_xml($xml, $hide_credentials=True) {

		// replace username and password by some hash signs
		if ($hide_credentials) {
			$xml = preg_replace('/<username>.*<\/username>/', "<username>################</username>", $xml);
			$xml = preg_replace('/<password>.*<\/password>/', "<password>################</password>", $xml);
		}

		$dom = new \DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml);
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

		$ret .= $this->_prettify_xml($xml, False);

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

		// check for permissions
		// TODO: if there are more levels of grants: don't forget to add this here
		try {
			$this->_check_permissions("ext_provider_actions");
		}
		catch (PermissionDeniedError $ex) {
			return View::make('auth.denied', array('error_msg' => $ex->getMessage()));
		}

		$base_url = $this->base_url;

		// the URLs to use for the jobs to do
		$urls = array(
			'blacklist_create_entry' => $base_url.'____TODO____',
			'blacklist_delete_entry' => $base_url.'____TODO____',
			'blacklist_get' => $base_url.'selfcare/blacklist/get',

			'calllog_delete' => $base_url.'____TODO____',
			'calllog_delete_entry' => $base_url.'____TODO____',
			'calllog_get' => $base_url.'____TODO____',
			'calllog_get_status' => $base_url.'selfcare/calllog/get_status',

			'configuration_get' => $base_url.'selfcare/configuration/get',
			'configuration_update' => $base_url.'____TODO____',

			'contract_change_method' => $base_url.'____TODO____',
			'contract_change_sla' => $base_url.'____TODO____',
			'contract_change_tariff' => $base_url.'____TODO____',
			'contract_change_variation' => $base_url.'____TODO____',
			'contract_create' => $base_url.'contract/create',
			'contract_get_reference' => $base_url.'____TODO____',
			'contract_get_voice_data' => $base_url.'contract/get_voice_data',
			'contract_lock' => $base_url.'____TODO____',
			'contract_terminate' => $base_url.'contract/terminate',
			'contract_unlock' => $base_url.'____TODO____',

			'customer_get_reference' => $base_url.'____TODO____',
			'customer_update' => $base_url.'customer/update',

			'misc_get_free_numbers' => $base_url.'misc/get_free_numbers',
			'misc_get_orders_csv' => $base_url.'misc/get_orders_csv',
			'misc_get_usage_csv' => $base_url.'misc/get_usage_csv',
			'misc_ping' => $base_url.'misc/ping',

			'order_add_mgcp_details' => $base_url.'____TODO____',
			'order_cancel' => $base_url.'____TODO____',
			'order_create_attachment' => $base_url.'____TODO____',
			'order_get_status' => $base_url.'order/get_status',

			'phonebookentry_create' => $base_url.'____TODO____',
			'phonebookentry_delete' => $base_url.'____TODO____',
			'phonebookentry_get' => $base_url.'____TODO____',

			'voip_account_create' => $base_url.'voipaccount/create',
			'voip_account_terminate' => $base_url.'____TODO____',
			'voip_account_update' => $base_url.'____TODO____',
		);

		// TODO: improve error handling
		if (!array_key_exists($job, $urls)) {
			throw new \Exception("Job ".$job." not implemented yet");
		}

		// the API URL to use for the request
		$url = $urls[$job];

		// for devel phase: die if URL is not set
		if (\Str::contains($url, "TODO")) {
			throw new \Exception("Missing url: ".$url);
		}

		// the requests payload (=XML)
		$payload = $this->model->get_xml($job);

		// extract origin
		$origin = \Input::get('origin', \URL::to('/'));

		$obj = $this->get_model_obj();
		$view_header = 'Request Envia';

		$view_path = $this->get_view_name().'.request';

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
		// other => something went wrong
		else {
			$view_var = $this->_handle_request_failed($job, $data);
		}

		if ($this->entry_method != 'cron') {
			if (\Config::get('app.debug')) {
				$view_var['plain_html'] .= "<hr>";
				$view_var['plain_html'] .= "<h4>DEBUG mode enabled in .env</h4>";
				$view_var['plain_html'] .= "return data:<br>";
				$view_var['plain_html'] .= "<pre>";
				$view_var['plain_html'] .= $this->_prettify_xml($data['xml']);
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
			echo "ERROR(S) occured:";
			echo "Exiting…";
			exit(1);
		}
		else {
			$ret = '';

			$ret .= "<h4>The following errors occured:</h4>";
			$ret .= "<table style=\"background-color: #faa;\">";
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
