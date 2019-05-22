<?php

namespace Modules\ProvVoipEnvia\Http\Controllers;

use Illuminate\Support\Facades\View;
use Modules\ProvVoipEnvia\Entities\ProvVoipEnvia;
use Modules\ProvVoipEnvia\Exceptions\XmlCreationError;

class ProvVoipEnviaController extends \BaseController
{
    // TODO: Patrick Reichel: is this field required ?
    public $name = 'VOIP';

    /**
     * Constructor.
     *
     * @author Patrick Reichel
     */
    public function __construct()
    {

        // we need to create the model manually
        $this->model = new ProvVoipEnvia();

        // store the called entry method => later needed for different output (echo vs. view)
        // don't try to extract via explode from Request::url => e.g. „php artisan route:list“ crashes with index out of bound…
        $url = \Request::url();
        if (\Str::contains($url, '/request/')) {
            $this->entry_method = 'request';
        } elseif (\Str::contains($url, '/cron/')) {
            $this->entry_method = 'cron';
        } elseif (\Str::contains($url, '/index/')) {
            $this->entry_method = 'index';
        } else {
            $this->entry_method = '';
        }

        // get base URL of the envia API and append slash (if not ending with one)
        $this->base_url = isset($_ENV['PROVVOIPENVIA__REST_API_URL']) ? $_ENV['PROVVOIPENVIA__REST_API_URL'] : '';
        if (($this->base_url) && (! \Str::endswith($this->base_url, '/'))) {
            $this->base_url .= '/';
        }

        parent::__construct();
    }

    /**
     * Checks if API version is set.
     *
     * @author Patrick Reichel
     */
    public function check_api_version($type, $job)
    {
        if ($this->model->api_version['major'] < 0) {
            throw new \InvalidArgumentException('Error performing '.$type.' ('.$job.'): PROVVOIPENVIA__REST_API_VERSION in .env has to be set to a positive float value (e.g.: 1.4) ⇒ ask your admin for proper values');
        }

        if (
            ($job == 'customer_get_contracts')
            &&
            ($this->model->api_version_less_than('2.2'))
        ) {
            return false;
        }

        return true;
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
    public function cron($job)
    {
        \Log::debug("Starting ProvVoipEnviaController::cron('$job')");
        $base_url = $this->base_url;
        $client_ip = \Request::getClientIp();
        $request_uri = \Request::getUri();
        $origin = \URL::to('/');	// origin is not relevant in cron jobs; set only for compatibility reasons…

        if (! $this->check_api_version('cron', $job)) {
            $msg = 'Job '.$job.' not allowed in API version '.$this->model->api_version_string.'.';
            echo "Error: $msg";
            \Log::error($msg);
            exit(1);
        }

        // as this method is not protected by normal auth mechanism we will allow only a small number of jobs
        $allowed_cron_jobs = [
            'misc_get_keys' => $base_url.'misc/get_keys',
            'misc_get_orders_csv' => $base_url.'misc/get_orders_csv',
            'order_get_status' => $base_url.'order/get_status',
            'contract_get_reference' => $base_url.'contract/get_reference',
            'contract_get_tariff' => $base_url.'contract/get_tariff',
            'contract_get_variation' => $base_url.'contract/get_variation',
            'contract_get_voice_data' => $base_url.'contract/get_voice_data',
            'customer_get_contracts' => $base_url.'customer/get_contracts',
            'customer_get_reference' => $base_url.'customer/get_reference',
            'customer_get_reference_by_legacy_number' => $base_url.'customer/get_reference',
            'misc_get_orders_csv_process_single_order' => '',
        ];

        // allowed client IPs – currently restricted to localhost
        $allowed_client_ips = [];
        $raw = explode(',', $_ENV['PROVVOIPENVIA__IPS_ALLOWED_FOR_CRON']);
        foreach ($raw as $ip) {
            array_push($allowed_client_ips, trim($ip));
        }

        $alternate_uri = str_replace('/cron/', '/request/', $request_uri);

        // if something else is requested: die with error message
        if (! array_key_exists($job, $allowed_cron_jobs)) {
            echo 'ERROR: Job '.$job.' not allowed in method cron.<br>Try <a href="'.$alternate_uri.'" target="_self">'.$alternate_uri.'</a> instead.';
            \Log::error('ERROR: Job '.$job.' not allowed in ProvVoipEnviaController.cron (Request URI was '.$request_uri.')');
            exit(1);
        }

        if (! in_array($client_ip, $allowed_client_ips)) {
            echo 'ERROR: Client IP '.$client_ip.' not allowed in method cron.<br>Try <a href="'.$alternate_uri.'" target="_self">'.$alternate_uri.'</a> instead.';
            \Log::error('ERROR: Client IP '.$client_ip.' not allowed in method cron (Request URI was '.$request_uri.').');
            exit(1);
        }

        // prepare the model
        $this->model->set_model_data();

        // execute only if job is currently allowed
        if (! $this->_job_allowed($job)) {
            $view_var = $this->_show_job_not_allowed_info($job, $origin);
        } else {

            // the API URL to use for the request
            $url = $allowed_cron_jobs[$job];

            // prepare the model
            $this->model->set_model_data();

            // check what job has to be done – there can be some special cases…
            if ($job == 'misc_get_orders_csv_process_single_order') {
                // special handling – we do not get data from envia but use the data given by GET param
                $order_data = unserialize(urldecode(\Input::get('serialized_order', '')));
                if (! is_array($order_data)) {
                    $msg = 'Malformed data given. Cancelling cronjob';
                    \Log::error($msg);
                    print_r($msg);

                    return;
                }
                $view_var = $this->model->_process_misc_get_orders_csv_response_single_order($order_data, 'cron');
            } else {
                // default case: get data from Envia

                // the requests payload (=XML)
                $payload = $this->model->get_xml($job);

                $view_var = $this->_perform_request($url, $payload, $job);
            }
        }
        print_r($view_var);
    }

    /**
     * Overwrite index.
     * temporary starter for xml generation
     */
    public function index()
    {
        $jobs = [
            ['api' => 'selfcare', 'link' => 'blacklist_get?phonenumber_id=300001&amp;envia_blacklist_get_direction=in'],
            ['api' => 'selfcare', 'link' => 'blacklist_get?phonenumber_id=300001&amp;envia_blacklist_get_direction=out'],
            ['api' => 'selfcare', 'link' => 'calllog_get_status?contract_id=500000'],
            ['api' => 'selfcare', 'link' => 'configuration_get?phonenumber_id=300001'],
            ['api' => 'order', 'link' => 'contract_change_method?phonenumber_id=300014'],
            ['api' => 'order', 'link' => 'contract_change_tariff?contract_id=500010'],
            ['api' => 'order', 'link' => 'contract_change_variation?contract_id=500010'],
            ['api' => 'order', 'link' => 'contract_create?contract_id=500000'],
            ['api' => 'order', 'link' => 'contract_get_reference?phonenumber_id=300012'],
            ['api' => 'order', 'link' => 'contract_get_tariff?phonenumber_id=300012'],
            ['api' => 'order', 'link' => 'contract_get_variation?phonenumber_id=300012'],
            ['api' => 'order', 'link' => 'contract_get_voice_data?contract_id=500000'],
            ['api' => 'order', 'link' => 'contract_terminate?contract_id=500000'],
            ['api' => 'order', 'link' => 'customer_update?contract_id=500000'],
            ['api' => 'order', 'link' => 'customer_get_contracts?contract_id=500000'],
            ['api' => 'order', 'link' => 'misc_ping'],
            ['api' => 'order', 'link' => 'misc_get_free_numbers'],
            ['api' => 'order', 'link' => 'misc_get_free_numbers?localareacode=03735'],
            ['api' => 'order', 'link' => 'misc_get_free_numbers?localareacode=03735&amp;baseno=7696'],
            ['api' => 'order', 'link' => 'misc_get_keys?keyname=index'],
            ['api' => 'order', 'link' => 'misc_get_orders_csv'],
            ['api' => 'order', 'link' => 'misc_get_usage_csv'],
            ['api' => 'order', 'link' => 'order_cancel?order_id='],
            ['api' => 'order', 'link' => 'order_get_status?order_id=72950'],
            ['api' => 'order', 'link' => 'voip_account_create?phonenumber_id=300001'],
            ['api' => 'order', 'link' => 'voip_account_terminate?phonenumber_id=300001'],
            ['api' => 'order', 'link' => 'voip_account_update?phonenumber_id=300001'],
            ['api' => 'order', 'link' => 'availability_check?contract_id=500000'],
            ['api' => '', 'link' => ''],
            ['api' => 'selfcare', 'link' => 'blacklist_create_entry'],
            ['api' => 'selfcare', 'link' => 'blacklist_delete_entry'],
            ['api' => 'selfcare', 'link' => 'calllog_delete'],
            ['api' => 'selfcare', 'link' => 'calllog_delete_entry'],
            ['api' => 'selfcare', 'link' => 'calllog_get'],
            ['api' => 'selfcare', 'link' => 'configuration_update'],
            ['api' => 'order', 'link' => 'contract_change_sla'],
            ['api' => 'order', 'link' => 'contract_lock'],
            ['api' => 'order', 'link' => 'contract_unlock'],
            ['api' => 'order', 'link' => 'customer_get_reference'],
            ['api' => 'order', 'link' => 'customer_get_reference_by_legacy_number'],
            ['api' => 'order', 'link' => 'order_add_mgcp_details'],
            ['api' => 'order', 'link' => 'order_create_attachment?order_id=73013&amp;enviaorderdocument_id=7'],
            ['api' => 'order', 'link' => 'order_get_status'],
            ['api' => 'order', 'link' => 'phonebookentry_create'],
            ['api' => 'order', 'link' => 'phonebookentry_delete'],
            ['api' => 'order', 'link' => 'phonebookentry_get'],
        ];

        echo '<h3>Selfcare-API is not active ⇒ links will not be shown</h3>';
        foreach ($jobs as $job) {
            if (! boolval($job['link'])) {
                echo '<hr>';
                continue;
            }
            if ($job['api'] != 'selfcare') {
                $href = \URL::route('ProvVoipEnvia.request', $job['link']);
                echo '<a href="'.$href.'" target="_self">'.$job['api'].': '.$job['link'].'</a><br>';
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
    protected function _ask_envia($url, $payload)
    {
        $curl_options = $this->_get_curl_headers($url, $payload);

        // create a new cURL resource
        $ch = curl_init();

        // setting the cURL options
        curl_setopt_array($ch, $curl_options);

        // default values for data array
        $data = [
            'error' => false,
            'error_type' => null,
            'error_msg' => null,
            'status' => null,
            'xml' => null,
        ];

        try {

            // perform cURL session
            $ret = curl_exec($ch);

            // check for errors
            if (curl_errno($ch)) {
                $data['error'] = true;
                $data['error_type'] = 'cURL error';
                $data['error_msg'] = curl_error($ch);
            }
            // or get data
            else {
                $data['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $data['xml'] = $ret;
            }
        } catch (Exception $ex) {
            $data['error'] = true;
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
    protected function _get_curl_headers($url, $payload)
    {

        // headers for http request
        $http_headers = [
            'Content-type: text/xml;charset="utf-8"',
            'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
        ];

        // defining cURL options (http://php.net/manual/en/function.curl-setopt.php)
        $curl_options = [

            // basic options
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $http_headers,

            // method and data to use
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,

            // verify peer's certificate to prevent MITM attacks
            CURLOPT_SSL_VERIFYPEER => true,
            // check for common name in cert and match to the hostname provided
            CURLOPT_SSL_VERIFYHOST => 2,

            // force DNS resolution to IPv4 address
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,

            // verbose mode?
            CURLOPT_VERBOSE => false,

            // return server answer instead of echoing it instantly
            CURLOPT_RETURNTRANSFER => true,
        ];

        return $curl_options;
    }

    /**
     * Send data to envia TEL and process result.
     *
     * @author Patrick Reichel
     *
     * @param $url URL for webservice
     * @param $payload data to transmit (XML)
     * @param $job job to do
     * @return data for view (currently plain HTML)
     */
    protected function _perform_request($url, $payload, $job)
    {

        /* echo "<h3>We are not sending data to envia TEL yet! Will now exit…</h3>"; */
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
     * Use this before sending data to envia to prevent e.g. double creation of contracts (if user presses F5 in success screen)
     *
     * This defaults to false – so you have to whitelist all the methods you are going to use.
     *
     * @author Patrick Reichel
     *
     * @param $job job to do
     *
     * @return true if job is allowed, false else
     */
    protected function _job_allowed($job)
    {

        // these jobs are allowed in every case
        $unrestricted_jobs = [
            'availability_check',
            'contract_get_reference',
            'contract_get_tariff',
            'contract_get_variation',
            'contract_get_voice_data',
            'customer_get_contracts',
            'customer_get_reference',
            'customer_get_reference_by_legacy_number',
            'misc_ping',
            'misc_get_keys',
            'misc_get_free_numbers',
            'misc_get_orders_csv',
            'misc_get_orders_csv_process_single_order',
            'misc_get_usage_csv',
            'order_cancel',
            'order_create_attachment',
            'order_get_status',
        ];

        if (in_array($job, $unrestricted_jobs)) {
            return true;
        }

        // perform checks for the rest of the jobs
        if ($job == 'contract_create') {
            return true;
        }

        if ($job == 'contract_change_method') {
            if ($this->model->phonenumber->exists) {
                $this->model->extract_environment($this->model->phonenumber, 'phonenumber');
            } else {
                $this->model->extract_environment($this->model->modem, 'modem');
            }

            // only can change method for existing contract
            if (! $this->model->contract_available) {
                return false;
            }

            return true;
        }

        if ($job == 'contract_change_tariff') {
            $this->model->extract_environment($this->model->modem, 'modem');

            // only can get data for a contract that exists
            if (! $this->model->contract_available) {
                return false;
            }

            if (! boolval($this->model->contract->next_voip_id)) {
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
            if (! $this->model->contract_available) {
                return false;
            }

            if (! boolval($this->model->contract->next_purchase_tariff)) {
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
            if (! $this->model->contract_available) {
                return false;
            }

            return true;
        }

        if ($job == 'customer_update') {
            $this->model->extract_environment($this->model->contract, 'contract');

            // Customer can only be updated if active contract exists
            if (! $this->model->at_least_one_contract_available) {
                return false;
            }

            return true;
        }

        if ($job == 'voip_account_create') {
            $this->model->extract_environment($this->model->phonenumbermanagement, 'phonenumbermanagement');

            // can only be created if not yet created
            if ($this->model->voipaccount_created) {
                return false;
            }

            return true;
        }

        if ($job == 'voip_account_terminate') {
            $this->model->extract_environment($this->model->phonenumbermanagement, 'phonenumbermanagement');

            // can only be terminated if available
            if (! $this->model->voipaccount_available) {
                return false;
            }

            return true;
        }

        if ($job == 'voip_account_update') {
            $this->model->extract_environment($this->model->phonenumbermanagement, 'phonenumbermanagement');

            // can only be terminated if available
            if (! $this->model->voipaccount_available) {
                return false;
            }

            return true;
        }

        if ($job == 'phonebookentry_create') {
            $this->model->extract_environment($this->model->phonebookentry, 'phonebookentry');

            // always allowed as this method is also used to change an existing phonebookentry
            return true;
        }

        if ($job == 'phonebookentry_delete') {
            $this->model->extract_environment($this->model->phonebookentry, 'phonebookentry');

            // can only be created if not existing
            if ($this->model->phonebookentry_created) {
                return true;
            } else {
                return false;
            }
        }

        if ($job == 'phonebookentry_get') {
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
    protected function _show_job_not_allowed_info($job, $origin)
    {
        \Log::error("ProvVoipEnviaController: Execution of $job is not allowed");
        $ret = [];
        $ret['plain_html'] = '';
        $ret['plain_html'] .= '<h4>'.trans('provvoipenvia::messages.error').'</h4>';
        $ret['plain_html'] .= trans('provvoipenvia::messages.job_currently_not_allowed', [$job]);
        $ret['plain_html'] .= '<h5><b><a href="'.urldecode($origin).'">'.trans('provvoipenvia::messages.back').'</h5>';

        return $ret;
    }

    protected function _show_xml_creation_error($msg, $origin)
    {
        $ret = [];

        $ret['plain_html'] = '';
        $ret['plain_html'] .= '<h4>'.trans('provvoipenvia::errors.error_creating_xml').':</h4>';
        $ret['plain_html'] .= '<h5>'.$msg.'</h5><br><br>';
        $ret['plain_html'] .= '<h5><b><a href="'.urldecode($origin).'">'.trans('provvoipenvia::messages.back').'</h5>';

        return $ret;
    }

    /**
     * To do the job there is some extra information needed – here we try to get it…
     *
     * @author Patrick Reichel
     */
    protected function _ask_for_phonenumbers_to_be_created_with_contract($url, $origin)
    {
        $html = '';
        $html .= '<h4>'.trans('provvoipenvia::messages.choose_numbers_to_create').'</h4>';

        $phonenumbers_on_modem = $this->model->get_numbers_related_to_modem_for_contract_create();

        $html .= "<form method='GET'>\n";
        foreach ($phonenumbers_on_modem as $porting_group => $phonenumbers_by_date) {
            $html .= "<fieldset>\n";
            foreach ($phonenumbers_by_date as $activation_date => $phonenumbers) {
                $html .= "<legend>$porting_group ($activation_date)</legend>\n";
                foreach ($phonenumbers as $phonenumber) {
                    $html .= "<label><input type='checkbox' name='phonenumbers_to_create[]' value='$phonenumber->id'/>&nbsp;&nbsp;$phonenumber->prefix_number/$phonenumber->number</label><br>\n";
                }
            }
            $html .= "</fieldset>\n\n";
        }

        // add the original GET params as hidden inputs
        foreach (\Input::get() as $name => $value) {
            if ($name != 'phonenumbers_to_create') {
                $html .= "<input type='hidden' name='$name' value='$value' />\n";
            }
        }

        $html .= "<input class='btn btn-primary' style='simple' type='submit' value='".trans('provvoipenvia::messages.create_contract_with_numbers')."'/>\n";

        $html .= '</form>';

        $html .= '<h5><b><a href="'.urldecode($origin).'">'.trans('provvoipenvia::messages.back_to', [urldecode($origin)]).'</h5>';

        $ret = ['plain_html' => $html];

        return $ret;
    }

    /**
     * Get confirmation to continue with chosen action.
     * Used for every job that changes data at envia TEL.
     *
     * @author Patrick Reichel
     * @param $payload generated XML
     * @param $url API-URL to send XML to
     * @param $origin previous URL (to be able to switch back)
     */
    protected function _show_confirmation_request($payload, $url, $origin)
    {
        $ret = [];

        $ret['plain_html'] = '';
        $ret['plain_html'] .= '<h4>'.trans('provvoipenvia::messages.send_to_envia_head1').'</h4>';
        $ret['plain_html'] .= 'URL: '.$url.'<br>';
        $ret['plain_html'] .= 'API version: '.$this->model->api_version_string.'<br><br>';
        $ret['plain_html'] .= '<pre>';
        $ret['plain_html'] .= ProvVoipEnvia::prettify_xml($payload, true);
        $ret['plain_html'] .= '</pre>';

        $ret['plain_html'] .= '<h4>'.trans('provvoipenvia::messages.send_to_envia_head2').'</h4>';

        $ret['plain_html'] .= '<h5><b><a href="'.urldecode($origin).'">'.trans('provvoipenvia::messages.send_to_envia_cancel').'</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        // prefix for GET param: ? if the only one, &amp; else
        if (strpos(\Request::getRequestUri(), '?') === false) {
            $attach_prefix = '?';
        } else {
            $attach_prefix = '&amp;';
        }
        $ret['plain_html'] .= '<a href="'.\Request::getRequestUri().$attach_prefix.'really=True" target="_self">'.trans('provvoipenvia::messages.send_to_envia_now').'</a></b></h5>';

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
    private function __debug_xml($xml)
    {
        $ret = '';
        $ret .= '<pre style="border: solid 1px #444; padding: 10px">';
        $ret .= '<h5>Pretty:</h5>';

        $ret .= ProvVoipEnvia::prettify_xml($xml, false);

        $ret .= '<br><hr>';
        $ret .= '<h5>Original:</h5>';
        $ret .= htmlentities($xml);
        $ret .= '</pre>';

        return $ret;
    }

    /**
     * Create array with jobs as keys and correlating API URL as values.
     *
     * @author Patrick Reichel
     */
    protected function _get_envia_job_to_url_mappings()
    {

        // the URLs to use for the jobs to do
        $urls = [
            /* 'blacklist_create_entry' => $this->base_url.'____TODO____', */
            /* 'blacklist_delete_entry' => $this->base_url.'____TODO____', */
            /* 'blacklist_get' => $this->base_url.'selfcare/blacklist/get', */

            /* 'calllog_delete' => $this->base_url.'____TODO____', */
            /* 'calllog_delete_entry' => $this->base_url.'____TODO____', */
            /* 'calllog_get' => $this->base_url.'____TODO____', */
            /* 'calllog_get_status' => $this->base_url.'selfcare/calllog/get_status', */

            /* 'configuration_get' => $this->base_url.'selfcare/configuration/get', */
            /* 'configuration_update' => $this->base_url.'____TODO____', */

            'availability_check' => $this->base_url.'availability/check',

            'contract_change_method' => $this->base_url.'contract/change_method',
            'contract_change_sla' => $this->base_url.'____TODO____',
            'contract_change_tariff' => $this->base_url.'contract/change_tariff',
            'contract_change_variation' => $this->base_url.'contract/change_variation',
            'contract_create' => $this->base_url.'contract/create',
            'contract_get_reference' => $this->base_url.'contract/get_reference',
            'contract_get_tariff' => $this->base_url.'contract/get_tariff',
            'contract_get_variation' => $this->base_url.'contract/get_variation',
            'contract_get_voice_data' => $this->base_url.'contract/get_voice_data',
            'contract_lock' => $this->base_url.'____TODO____',
            'contract_relocate' => $this->base_url.'contract/relocate',
            'contract_terminate' => $this->base_url.'contract/terminate',
            'contract_unlock' => $this->base_url.'____TODO____',

            'customer_get_contracts' => $this->base_url.'customer/get_contracts',
            'customer_get_reference' => $this->base_url.'customer/get_reference',
            'customer_get_reference_by_legacy_number' => $this->base_url.'customer/get_reference',
            'customer_update' => $this->base_url.'customer/update',

            'misc_get_free_numbers' => $this->base_url.'misc/get_free_numbers',
            'misc_get_keys' => $this->base_url.'misc/get_keys',
            'misc_get_orders_csv' => $this->base_url.'misc/get_orders_csv',
            'misc_get_usage_csv' => $this->base_url.'misc/get_usage_csv',
            'misc_ping' => $this->base_url.'misc/ping',

            'order_add_mgcp_details' => $this->base_url.'____TODO____',
            'order_cancel' => $this->base_url.'order/cancel',
            'order_create_attachment' => $this->base_url.'order/create_attachment',
            'order_get_status' => $this->base_url.'order/get_status',

            'phonebookentry_create' => $this->base_url.'phonebookentry/create',
            'phonebookentry_delete' => $this->base_url.'phonebookentry/delete',
            'phonebookentry_get' => $this->base_url.'phonebookentry/get',

            'voip_account_create' => $this->base_url.'voip_account/create',
            'voip_account_terminate' => $this->base_url.'voip_account/terminate',
            'voip_account_update' => $this->base_url.'voip_account/update',
        ];

        return $urls;
    }

    /**
     * Method to perform a request the envia API.
     *
     * @author Patrick Reichel
     *
     * @param $job comes from the route ([…]/provvoipenvia/request/{job})
     * @return view for showing the data
     */
    public function request($job)
    {

        // check if a non standard return type is wanted
        // usable: view (default), html
        $return_type = \Input::get('return_type', 'view');
        $allowed_return_types = ['view', 'html'];
        if (! in_array($return_type, $allowed_return_types)) {
            throw new \InvalidArgumentException('Allowed return_type has to be in ['.implode(', ', $allowed_return_types).'] but “'.$return_type.'” given.');
        }

        if (! $this->check_api_version('request', $job)) {
            $msg = 'Job '.$job.' not allowed in API version '.$this->model->api_version_string.'.';
            echo "Error: $msg";
            \Log::error($msg);
            exit(1);
        }

        $urls = $this->_get_envia_job_to_url_mappings();

        // TODO: improve error handling: Throwing an exception is a bit hard :-)
        if (! array_key_exists($job, $urls)) {
            /* throw new \Exception("Job ".$job." not implemented yet"); */
            abort(404);
        }

        // the API URL to use for the request
        $url = $urls[$job];

        // for devel phase: die if URL is not set
        if (\Str::contains($url, 'TODO')) {
            throw new \Exception('Missing url: '.$url);
        }

        // set some environmental vars
        $origin = \Input::get('origin', \URL::to('/'));
        $view_header = 'Request envia TEL';
        $view_path = \NamespaceController::get_view_name().'.request';

        // check if there should be an instant redirect – if so do so :-)
        if (\Input::get('instant_redirect', false)) {
            // we have to add the GET param manually as Redirect::to()->with('recentlty_updated', true) is not running
            // this param is used to break out of the endless redirect loop :-)
            return \Redirect::to(urldecode($origin).'?recently_updated=1');
        }

        // prepare the model
        $this->model->set_model_data();

        // build the view
        $view_var = null;
        // first we have to check for special cases
        // check if job to do is allowed
        // e.g. to prevent double contract creation on pressing <F5>
        if (! $this->_job_allowed($job)) {
            $view_var = $this->_show_job_not_allowed_info($job, $origin);
        }
        // creation of contracts without phonenumbers will not longer be possible (from autumn 2017 on)
        // so: if there is a request to create a contract but no phonenumbers to be co-created are given:
        // collect this information from user
        elseif (
            ($job == 'contract_create')
            &&
            (! \Input::get('phonenumbers_to_create', []))
        ) {
            $view_var = $this->_ask_for_phonenumbers_to_be_created_with_contract($url, $origin);
        }

        // in all other cases we will need XML for communication  (and building $view_var)
        if (is_null($view_var)) {
            // the requests payload (=XML)
            $xml_creation_failed = true;
            try {
                // check if data is going to be sent – don't store XML created to be shown for confirmation request
                // if the XML to create is for sending against envia TEL there should be a …&really=True within the GET params
                $store_xml = \Input::get('really', false);
                $payload = $this->model->get_xml($job, $store_xml);
                $xml_creation_failed = false;
            } catch (XmlCreationError $ex) {
                $payload = $ex->getMessage();
            } catch (\Exception $ex) {
                // this should never happen :-)
                throw $ex;
            }

            if ($xml_creation_failed) {
                $view_var = $this->_show_xml_creation_error($payload, $origin);
            } else {
                // on jobs changing data at envia TEL: Ask if job shall be performed
                // therefore show the generated XML
                if (! \Input::get('really', false)) {
                    $view_var = $this->_show_confirmation_request($payload, $url, $origin);
                } else {

                    // this is the default case – we perform a request against envia API…
                    $view_var = $this->_perform_request($url, $payload, $job);

                    // add link to previous page
                    $origin_link = '<hr>';
                    $origin_name = urldecode($origin);
                    $origin_name = explode($_SERVER['CONTEXT_PREFIX'], $origin_name);
                    $origin_name = array_pop($origin_name);
                    $origin_link .= '<h5><b><a href="'.urldecode($origin).'" target="_self">Back to '.$origin_name.'</a></b></h5>';
                    $view_var['plain_html'] .= $origin_link;
                }
            }
        }

        if (is_null($view_var)) {
            throw new \Exception('$view_var empty!!');
        }

        // use this e.g. to get the result directly in your view
        if ($return_type == 'html') {
            return $view_var['plain_html'];
        }

        // default: return a view
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
    protected function _handle_curl_error($job, $data)
    {
        $ret = [];
        $msg = 'We got an '.$data['error_type'].': '.$data['error_msg']." (Executing job $job)";
        \Log::error($msg);
        $ret['plain_html'] = "ERROR! $msg";

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
    protected function _handle_curl_success($job, $data)
    {

        // in the following if statement we decide the method to call by HTTP status codes in API respond
        // first we handle all specific errors, then success and finally process all not specific errors

        // success!!
        if (($data['status'] >= 200) && ($data['status'] < 300)) {
            $view_var = $this->_handle_request_success($job, $data);
        }
        // a 404 on order_get_status is meaningful ⇒ we have to delete this order
        // so let's handle this with the success logic
        elseif (($job == 'order_get_status') && ($data['status'] == 404)) {
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
                $view_var['plain_html'] .= '<hr>';
                $view_var['plain_html'] .= '<h4>DEBUG mode enabled in .env</h4>';
                $view_var['plain_html'] .= 'return data:<br>';
                $view_var['plain_html'] .= '<pre>';
                $view_var['plain_html'] .= ProvVoipEnvia::prettify_xml($data['xml']);
                $view_var['plain_html'] .= '</pre>';
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
    protected function _handle_request_failed($job, $data)
    {

        // check if we want to store the xml
        $xml = new \SimpleXMLElement($data['xml']);
        $this->model->store_xml($job.'_response', $xml);

        $errors = $this->model->get_error_messages($data['xml']);

        if ($this->entry_method == 'cron') {
            foreach ($errors as $error) {
                if (boolval($error['status']) || boolval($error['message'])) {
                    echo 'Error '.$error['status'].' occured on job '.$job.': '.$error['message'];
                    \Log::error('Error '.$error['status'].' occured on job '.$job.': '.$error['message']);
                }
            }
            \Log::error('Exiting cronjob because of the above errors.');
            echo 'Exiting cronjob because of the above errors.';
            exit(1);
        } else {
            $ret = '';

            $ret .= '<h4>The following error(s) occured:</h4>';
            $ret .= '<table style="background-color: #fcc; color: #000; font-size: 1.05em; font-family: monospace; font-weight: bold">';
            foreach ($errors as $error) {
                if (boolval($error['status']) || boolval($error['message'])) {
                    $ret .= '<tr>';
                    $ret .= '<td style="padding: 2px">';
                    $ret .= $error['status'].': ';
                    $ret .= '</td>';
                    $ret .= '<td style="padding: 2px; padding-left: 10px;">';
                    $ret .= $error['message'];
                    $ret .= '</td>';
                    $ret .= '</tr>';
                }
            }
            $ret .= '</table>';

            return ['plain_html' => $ret];
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
    protected function _handle_request_success($job, $data)
    {
        $ret = $this->model->process_envia_data($job, $data);

        if ($this->entry_method == 'cron') {
            return $ret;
        } else {
            return ['plain_html' => $ret];
        }
    }
}
