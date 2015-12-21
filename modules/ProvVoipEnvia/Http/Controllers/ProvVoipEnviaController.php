<?php namespace Modules\Provvoipenvia\Http\Controllers;

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

		$this->model = new ProvVoipEnvia();

	}


	/**
	 * Overwrite index.
	 */
	public function index() {
		$base = "/lara/provvoipenvia/request";

		$jobs = array(
			'contract_create?contract_id=500000',
			'misc_ping',
			'misc_get_free_numbers',
			'misc_get_free_numbers?localareacode=03725',
			'misc_get_free_numbers?localareacode=03725&amp;baseno=110',
			'voip_account_create?phonenumber_id=300001',
			'',
			'blacklist_create_entry',
			'blacklist_delete_entry',
			'blacklist_get',
			'calllog_delete',
			'calllog_delete_entry',
			'calllog_get',
			'configuration_get',
			'configuration_update',
			'contract_change_method',
			'contract_change_sla',
			'contract_change_tariff',
			'contract_change_variation',
			'contract_get_reference',
			'contract_get_voice_data',
			'contract_lock',
			'contract_terminate',
			'contract_unlock',
			'customer_get_reference',
			'customer_update',
			'misc_get_orders_csv',
			'misc_get_usage_csv',
			'order_add_mgcp_details',
			'order_cancel',
			'order_create_attachment',
			'order_get_status',
			'phonebookentry_create',
			'phonebookentry_delete',
			'phonebookentry_get',
			'voip_account_terminate',
			'voip_account_update',
		);

		foreach ($jobs as $job) {
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
	 * Helper to show the generated XML (in original and pretty shape)
	 * Use this for debugging the XML output and input
	 *
	 * @author Patrick Reichel
	 */
	private function __debug_xml($xml) {

		echo "<pre style=\"border: solid 1px #444; padding: 10px\">";
		echo "<h5>Pretty:</h5>";
		$dom = new \DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml);
		$pretty = htmlentities($dom->saveXML());
		$lines = explode("\n", $pretty);

		$declaration = array_shift($lines);
		$declaration = '<span style="color: #0000ff; font-weight: normal">'.$declaration.'</span>';
		$output = array();
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

		array_unshift($output, $declaration);
		echo implode("\n", $output);
		echo "<br><hr>";
		echo "<h5>Original:</h5>";
		echo htmlentities($xml);
		echo "</pre>";
	}

	/**
	 * Method to perform a request the envia API.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job comes from the route ([…]/provvoipenvia/request/{job})
	 */
	public function request($job) {

		$domain = 'https://www.enviatel.de';
		$sub_url = '/portal/api/rest/v1/';
		$base_url = $domain.$sub_url;

		// the URLs to use for the jobs to do
		$urls = array(
			'blacklist_create_entry' => $base_url.'____TODO____',
			'blacklist_delete_entry' => $base_url.'____TODO____',
			'blacklist_get' => $base_url.'____TODO____',

			'calllog_delete' => $base_url.'____TODO____',
			'calllog_delete_entry' => $base_url.'____TODO____',
			'calllog_get' => $base_url.'____TODO____',

			'configuration_get' => $base_url.'____TODO____',
			'configuration_update' => $base_url.'____TODO____',

			'contract_change_method' => $base_url.'____TODO____',
			'contract_change_sla' => $base_url.'____TODO____',
			'contract_change_tariff' => $base_url.'____TODO____',
			'contract_change_variation' => $base_url.'____TODO____',
			'contract_create' => $base_url.'contract/create',
			'contract_get_reference' => $base_url.'____TODO____',
			'contract_get_voice_data' => $base_url.'____TODO____',
			'contract_lock' => $base_url.'____TODO____',
			'contract_terminate' => $base_url.'____TODO____',
			'contract_unlock' => $base_url.'____TODO____',

			'customer_get_reference' => $base_url.'____TODO____',
			'customer_update' => $base_url.'____TODO____',

			'misc_get_free_numbers' => $base_url.'misc/get_free_numbers',
			'misc_get_orders_csv' => $base_url.'____TODO____',
			'misc_get_usage_csv' => $base_url.'____TODO____',
			'misc_ping' => $base_url.'misc/ping',

			'order_add_mgcp_details' => $base_url.'____TODO____',
			'order_cancel' => $base_url.'____TODO____',
			'order_create_attachment' => $base_url.'____TODO____',
			'order_get_status' => $base_url.'____TODO____',

			'phonebookentry_create' => $base_url.'____TODO____',
			'phonebookentry_delete' => $base_url.'____TODO____',
			'phonebookentry_get' => $base_url.'____TODO____',

			'voip_account_create' => $base_url.'____TODO____',
			'voip_account_terminate' => $base_url.'____TODO____',
			'voip_account_update' => $base_url.'____TODO____',
		);

		// TODO: improve error handling
		if (!array_key_exists($job, $urls)) {
			throw new \Exception("Job ".$job." not implemented yet");
		}

		// the API URL to use for the request
		$url = $urls[$job];

		// the requests payload (=XML)
		$payload = $this->model->get_xml($job);

		$this->__debug_xml($payload);

		echo "We are not sending data to Envia yet! Will now exit";
		exit();
		// perform the request and receive the result (meta and content)
		$data = $this->_ask_envia($url, $payload);

		// major problem!!
		if ($data['error']) {
			$this->_handle_curl_error($job, $data);
		}
		// got an answer
		else {
			$this->_handle_curl_success($job, $data);
		}

	}

	/**
	 * Method to handle exceptions and curl errors
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job which should have been done
	 * @param $data collected data from request try
	 */
	protected function _handle_curl_error($job, $data) {
		echo "ERROR! We got an ".$data['error_type'].": ".$data['error_msg']." executing job ".$job;
	}

	/**
	 * Method to handle successful request (on cURL level).
	 * Mainly used to separate further process using the HTTP status code.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job which should have been done
	 * @param $data collected data from request try
	 */
	protected function _handle_curl_success($job, $data) {

		// success!!
		if (($data['status'] >= 200) && ($data < 300)) {
			$this->_handle_request_success($job, $data);
		}
		// unauthorized => handle separately
		elseif ($data['status'] == 401) {
			$this->_handle_request_failed_401($job, $data);
		}
		// other => something went wrong
		else {
			$this->_handle_request_failed($job, $data);
		}

		echo "<hr>";
		echo "Return data:<br>";
		echo "<pre>";
		echo htmlentities($data['xml']);
		echo "</pre>";
	}

	/**
	 * Process rest answers with http error status 401 (Access denied)
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job which should have been done
	 * @param $data collected data from request try
	 */
	protected function _handle_request_failed_401($job, $data) {

		$errors = $this->model->get_error_messages($data['xml']);

		// TODO: Error output shall be handled via views
		echo "The following errors occured:<br>";
		echo "<table style=\"background-color: #faa\">";
		foreach ($errors as $error) {
			echo "<tr>";
			echo "<td>";
				echo $error['status'];
			echo "</td>";
			echo "<td>";
				echo $error['message'];
			echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}

	/**
	 * Process rest answers with http error status (400, 401, e.g.)
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job which should have been done
	 * @param $data collected data from request try
	 */
	protected function _handle_request_failed($job, $data) {

		echo "Problem: status code is ".$data['status']."<br>";
	}

	/**
	 * Process successfully performed REST request.
	 *
	 * @author Patrick Reichel
	 *
	 * @param $job job which should have been done
	 * @param $data collected data from request try
	 */
	protected function _handle_request_success($job, $data) {
	}

}
