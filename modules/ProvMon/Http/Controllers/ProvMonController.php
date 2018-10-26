<?php

namespace Modules\ProvMon\Http\Controllers;

use View;
use Acme\php\ArrayHelper;
use Modules\ProvBase\Entities\Cmts;
use Modules\ProvBase\Entities\Modem;
use Modules\HfcReq\Entities\NetElement;
use Modules\ProvBase\Entities\ProvBase;
use App\Http\Controllers\BaseController;
use Modules\ProvBase\Entities\Configfile;

/**
 * This is the Basic Stuff for Modem Analyses Page
 * Note: this class does not have a corresponding Model
 *       it fetches all required stuff from Modem or Server
 *
 * @author: Torsten Schmidt
 */
class ProvMonController extends \BaseController
{
    protected $domain_name = '';
    protected $modem = null;
    protected $edit_left_md_size = 12;

    public function __construct()
    {
        $this->domain_name = ProvBase::first()->domain_name;
        parent::__construct();
    }

    /**
     * Creates tabs to analysis pages.
     *
     * @author Roy Schneider
     * @param int
     * @return array
     */
    public function analysisPages($id)
    {
        $modem = $this->findModem($id);

        $tabs = [['name' => 'Analyses', 'route' => 'ProvMon.index', 'link' => [$id]],
                ['name' => 'CPE-Analysis', 'route' => 'ProvMon.cpe', 'link' => [$id]],
                ];

        array_unshift($tabs, $this->defineEditRoute($id));

        if (isset($modem->mtas[0])) {
            array_push($tabs, ['name' => 'MTA-Analysis', 'route' => 'ProvMon.mta', 'link' => [$id]]);
        }

        return $tabs;
    }

    /**
     * Find Modem
     *
     * @author Roy Schneider
     * @param int
     * @return Modules\ProvBase\Entities\Modem
     */
    public function findModem($id)
    {
        $this->modem = Modem::findOrFail($id);

        return $this->modem;
    }

    /**
     * Route for Modem or MTA edit page
     *
     * @author Roy Schneider
     * @param int
     * @return array
     */
    public function defineEditRoute($id)
    {
        $session = \Session::get('Edit');
        $modem = $this->findModem($id);

        $edit = ['name' => 'Edit', 'route' => 'Modem.edit', 'link' => [$id]];

        if (isset($modem->mtas[0]) && $session = 'MTA') {
            $edit = ['name' => 'Edit', 'route' => 'Mta.edit', 'link' => [$modem->mtas[0]->id]];
        }

        return $edit;
    }

    /**
     * Main Analyses Function
     *
     * @return Response
     */
    public function analyses($id)
    {
        $ping = $lease = $log = $dash = $realtime = $type = $flood_ping = $configfile = $eventlog = null;
        $modem = $this->modem ? $this->modem : Modem::find($id);
        $view_var = $modem; // for top header
        $hostname = $modem->hostname.'.'.$this->domain_name;
        $mac = strtolower($modem->mac);
        $modem->help = 'modem_analysis';

        $ip = gethostbyname($hostname);
        $ip = ($ip == $hostname) ? null : $ip;

        // Ping: Only check if device is online
        exec('sudo ping -c1 -i0 -w1 '.$hostname, $ping, $ret);
        $online = $ret ? false : true;

        // Flood Ping
        $flood_ping = $this->flood_ping($hostname);

        // Lease
        $lease['text'] = $this->search_lease('hardware ethernet '.$mac);
        $lease = $this->validate_lease($lease, $type);

        // Configfile
        $cf_path = "/tftpboot/cm/$modem->hostname.conf";
        $configfile = is_file($cf_path) ? file($cf_path) : null;

        // Realtime Measure - this takes the most time
        // TODO: only load channel count to initialise the table and fetch data via AJAX call after Page Loaded
        if ($online) {
            // preg_match_all('/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/', $ping[0], $ip);
            $realtime['measure'] = $this->realtime($hostname, ProvBase::first()->ro_community, $ip, false);
            $realtime['forecast'] = 'TODO';
            // get eventlog table
            if (! array_key_exists('SNMP-Server not reachable', $realtime['measure'])) {
                $eventlog = $modem->get_eventlog();
            }
        }

        // Log dhcp (discover, ...), tftp (configfile or firmware)
        $search = $ip ? "$mac|$modem->hostname|$ip " : "$mac|$modem->hostname";
        $log = self::_get_syslog_entries($search, '| grep -v MTA | grep -v CPE | tail -n 30  | tac');

        $host_id = $this->monitoring_get_host_id($modem);

        // TODO: Dash / Forecast

        $panel_right = $this->analysisPages($id);
        $view_header = 'ProvMon-Analyses';

        // View
        return View::make('provmon::analyses', $this->compact_prep_view(compact('modem', 'online', 'panel_right', 'lease', 'log', 'configfile', 'eventlog', 'dash', 'realtime', 'host_id', 'view_var', 'flood_ping', 'ip', 'view_header')));
    }

    /**
     * Helper to get Syslog entries dependent on what should be searched and discarded
     *
     * @param 	search 		String 		to search
     * @param 	grep_pipes 	String 		restrict matches
     * @return 	array
     */
    private static function _get_syslog_entries($search, $grep_pipes)
    {
        $search = escapeshellarg($search);
        // $grep_pipes = escapeshellarg($grep_pipes);

        exec("egrep -i $search /var/log/messages $grep_pipes", $log);

        // check if logrotate was done during last hours and consider older logfile (e.g. /var/log/messages-20170904)
        if (! $log) {
            $files = glob('/var/log/messages-*');
            if (! empty($files)) {
                exec('egrep -i '.$search.' '.max($files).' '.$grep_pipes, $log);
            }
        }

        return $log;
    }

    /**
     * Send output of Ping in real-time to client browser as Stream with Server Sent Events
     * called in analyses.blade.php in javascript content
     *
     * @param 	ip 			String
     * @return 	response 	Stream
     *
     * @author Nino Ryschawy
     */
    public function realtime_ping($ip)
    {
        // \Log::debug(__FUNCTION__. "called with $ip");

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($ip) {
            $cmd = 'ping -c 5 '.escapeshellarg($ip);

            $handle = popen($cmd, 'r');

            if (! is_resource($handle)) {
                echo "data: finished\n\n";
                ob_flush();
                flush();

                return;
            }

            while (! feof($handle)) {
                $line = fgets($handle);
                $line = str_replace("\n", '', $line);
                // \Log::debug("$line");
                // echo 'data: {"message": "'. $line . '"}'."\n";
                echo "data: <br>$line";
                echo "\n\n";
                ob_flush();
                flush();
            }

            pclose($handle);

            echo "data: finished\n\n";
            ob_flush();
            flush();
        });

        $response->headers->set('Content-Type', 'text/event-stream');

        return $response;
    }

    /**
     * Flood ping
     *
     * NOTE:
     * --- add /etc/sudoers.d/nms-nmsprime ---
     * Defaults:apache        !requiretty
     * apache  ALL=(root) NOPASSWD: /usr/bin/ping
     * --- /etc/sudoers.d/nms-nmsprime ---
     *
     * @param hostname  the host to send a flood ping
     * @return flood ping exec result
     */
    public function flood_ping($hostname)
    {
        if (! \Input::has('flood_ping')) {
            return;
        }

        $hostname = escapeshellarg($hostname);

        switch (\Input::get('flood_ping')) {
            case '1':
                exec("sudo ping -c500 -f $hostname 2>&1", $fp, $ret);
                break;
            case '2':
                exec("sudo ping -c1000 -s736 -f $hostname 2>&1", $fp, $ret);
                break;
            case '3':
                exec("sudo ping -c2500 -f $hostname 2>&1", $fp, $ret);
                break;
            case '4':
                exec("sudo ping -c2500 -s1472 -f $hostname 2>&1", $fp, $ret);
                break;
        }

        // remove the flood ping line "....." from result
        if ($ret == 0) {
            unset($fp[1]);
        }

        return $fp;
    }

    /**
     * Returns view of cpe analysis page
     */
    public function cpe_analysis($id)
    {
        $ping = $lease = $log = $dash = $realtime = null;
        $modem = $this->modem ? $this->modem : Modem::find($id);
        $view_var = $modem; // for top header
        $type = 'CPE';
        $modem_mac = strtolower($modem->mac);
        $modem->help = 'cpe_analysis';

        // Lease
        $lease['text'] = $this->search_lease('billing subclass', $modem_mac);
        $lease = $this->validate_lease($lease, $type);

        $ep = $modem->endpoints()->first();
        if ($ep && $ep->fixed_ip && $ep->ip) {
            $lease = $this->_fake_lease($modem, $ep);
        }

        /// get MAC of CPE first
        $str = self::_get_syslog_entries($modem_mac, '| grep CPE | tail -n 1 | tac');
        // exec ('grep -i '.$modem_mac." /var/log/messages | grep CPE | tail -n 1  | tac", $str);

        if ($str == []) {
            $mac = $modem_mac;
            $mac[0] = ' ';
            $mac = trim($mac);
            $mac_bug = true;
            // exec ('grep -i '.$mac." /var/log/messages | grep CPE | tail -n 1 | tac", $str);
            $str = self::_get_syslog_entries($mac, '| grep CPE | tail -n 1 | tac');

            if (! $str && $lease['text']) {
                // get cpe mac addr from lease - first option tolerates small structural changes in dhcpd.leases and assures that it's a mac address
                preg_match_all('/(?:[0-9a-fA-F]{2}[:]?){6}/', substr($lease['text'][0], strpos($lease['text'][0], 'hardware ethernet'), 40), $cpe_mac);
            }
            // $cpe_mac[0][0] = substr($lease['text'][0], strpos($lease['text'][0], 'hardware ethernet') + 18, 17);
        }

        if (isset($str[0])) {
            if (isset($mac_bug)) {
                preg_match_all('/([0-9a-fA-F][:]){1}(?:[0-9a-fA-F]{2}[:]?){5}/', $str[0], $cpe_mac);
            } else {
                preg_match_all('/(?:[0-9a-fA-F]{2}[:]?){6}/', $str[0], $cpe_mac);
            }
        }

        // Log
        if (isset($cpe_mac[0][0])) {
            // exec ('grep -i '.$cpe_mac[0][0].' /var/log/messages | grep -v "DISCOVER from" | tail -n 20 | tac', $log);
            $cpe_mac = $cpe_mac[0][0];
            $log = self::_get_syslog_entries($cpe_mac, '| tail -n 20 | tac');
        }

        // Ping
        if (isset($lease['text'][0])) {
            // get ip first
            preg_match_all('/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/', $lease['text'][0], $ip);
            if (isset($ip[0][0])) {
                $ip = $ip[0][0];
                exec('sudo ping -c3 -i0 -w1 '.$ip, $ping);

                exec("dig -x $ip +short", $fqdns);
                foreach ($fqdns as $fqdn) {
                    $dash .= "Hostname: $fqdn<br>";
                    exec("dig $fqdn ptr +short", $ptrs);
                    foreach ($ptrs as $ptr) {
                        $dash .= "Hostname: $ptr<br>";
                    }
                }
            }
        }
        if (is_array($ping) && count(array_keys($ping)) <= 7) {
            $ping = null;
            if ($lease['state'] == 'green') {
                $ping[0] = trans('messages.cpe_not_reachable');
            }
        }

        $panel_right = $this->analysisPages($id);

        $view_header = 'Provmon-CPE';

        return View::make('provmon::cpe_analysis', $this->compact_prep_view(compact('modem', 'ping', 'type', 'panel_right', 'lease', 'log', 'dash', 'realtime', 'view_var', 'view_header')));
    }

    /**
     * Returns view of mta analysis page
     *
     * Note: This is never called if ProvVoip Module is not active
     */
    public function mta_analysis($id)
    {
        $ping = $lease = $log = $dash = $realtime = $configfile = null;
        $modem = $this->modem ? $this->modem : Modem::find($id);
        $view_var = $modem; // for top header
        $type = 'MTA';
        $modem->help = 'mta_analysis';

        $mtas = $modem->mtas;		// Note: we should use one-to-one relationship here
        if (isset($mtas[0])) {
            $mta = $mtas[0];
        } else {
            goto end;
        }

        // Ping
        $domain = \Modules\ProvVoip\Entities\ProvVoip::first()->mta_domain;
        $hostname = $domain ? $mta->hostname.'.'.$domain : $mta->hostname.'.'.$this->domain_name;

        exec('sudo ping -c3 -i0 -w1 '.$hostname, $ping);
        if (count(array_keys($ping)) <= 7) {
            $ping = null;
        }

        // lease
        $lease['text'] = $this->search_lease('mta-'.$mta->id);
        $lease = $this->validate_lease($lease, $type);

        // configfile
        $cf_path = "/tftpboot/mta/$mta->hostname.conf";
        $configfile = is_file($cf_path) ? file($cf_path) : null;

        // log
        $ip = gethostbyname($mta->hostname);
        $ip = $mta->hostname == $ip ? null : $ip;
        $mac = strtolower($mta->mac);
        $search = $ip ? "$mac|$mta->hostname|$ip " : "$mac|$mta->hostname";
        $log = self::_get_syslog_entries($search, '| tail -n 25  | tac');
        // exec ('grep -i "'.$mta->mac.'\|'.$mta->hostname.'" /var/log/messages | grep -v "DISCOVER from" | tail -n 20  | tac', $log);

        end:
        $panel_right = $this->analysisPages($id);

        $view_header = 'Provmon-MTA';

        return View::make('provmon::cpe_analysis', $this->compact_prep_view(compact('modem', 'ping', 'type', 'panel_right', 'lease', 'log', 'dash', 'realtime', 'configfile', 'view_var', 'view_header')));
    }

    /**
     * Returns view of cmts analysis page
     */
    public function cmts_analysis($id)
    {
        $ping = $lease = $log = $dash = $realtime = $monitoring = $type = $flood_ping = null;
        $cmts = Cmts::find($id);
        $ip = $cmts->ip;
        $view_var = $cmts; // for top header

        // Ping: Send 5 request's at once with max timeout of 1 second
        exec('sudo ping -c5 -i0 -w1 '.$ip, $ping);
        if (count(array_keys($ping)) <= 9) {
            $ping = null;
        }

        // Realtime Measure
        if (count($ping) == 10) { // only fetch realtime values if all pings are successfull
            $realtime['measure'] = $this->realtime_cmts($cmts, $cmts->get_ro_community());
            $realtime['forecast'] = 'TODO';
        }

        $host_id = $this->monitoring_get_host_id($cmts);

        $panel_right = [
            ['name' => 'Edit', 'route' => 'Cmts.edit', 'link' => [$id]],
            ['name' => 'Analysis', 'route' => 'ProvMon.cmts', 'link' => [$id]],
        ];

        $view_header = 'Provmon-CMTS';

        return View::make('provmon::cmts_analysis', $this->compact_prep_view(compact('ping', 'panel_right', 'lease', 'log', 'dash', 'realtime', 'host_id', 'view_var', 'view_header')));
    }

    /**
     * Proves if the last found lease is actually valid or has already expired
     */
    private function validate_lease($lease, $type)
    {
        if ($lease['text'] && $lease['text'][0]) {
            // calculate endtime
            preg_match('/ends [0-6] (.*?);/', $lease['text'][0], $endtime);
            $et = explode(',', str_replace([':', '/', ' '], ',', $endtime[1]));
            $endtime = \Carbon\Carbon::create($et[0], $et[1], $et[2], $et[3], $et[4], $et[5], 'UTC');

            // lease calculation
            // take care changing the state - it's used under cpe analysis
            $lease['state'] = 'green';
            $lease['forecast'] = "$type has a valid lease.";
            if ($endtime < \Carbon\Carbon::now()) {
                $lease['state'] = 'red';
                $lease['forecast'] = 'Lease is out of date';
            }
        } else {
            $lease['state'] = 'red';
            $lease['forecast'] = trans('messages.modem_lease_error');
        }

        return $lease;
    }

    private function _fake_lease($modem, $ep)
    {
        $lease['state'] = 'green';
        $lease['forecast'] = trans('messages.cpe_fake_lease').'<br />';
        $lease['text'][0] = "lease $ep->ip {<br />".
            "starts 3 $ep->updated_at;<br />".
            'binding state active;<br />'.
            'next binding state active;<br />'.
            'rewind binding state active;<br />'.
            "billing subclass \"Client\" $modem->mac;<br />".
            "hardware ethernet $ep->mac;<br />".
            "set ip = \"$ep->ip\";<br />".
            "set hw_mac = \"$ep->mac\";<br />".
            "set cm_mac = \"$modem->mac\";<br />".
            "option agent.remote-id $modem->mac;<br />".
            'option agent.unknown-9 0:0:11:8b:6:1:4:1:2:3:0;<br />'.
            '}<br />';

        return $lease;
    }

    /**
     * Local Helper to Convert the sysUpTime from Seconds to human readable format
     * See: http://stackoverflow.com/questions/8273804/convert-seconds-into-days-hours-minutes-and-seconds
     *
     * TODO: move somewhere else
     */
    private function _secondsToTime($seconds)
    {
        $seconds = round($seconds);
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");

        return $dtF->diff($dtT)->format('%a Days %h Hours %i Min %s Sec');
    }

    /*
     * convert docsis mode from int to human readable string
     */
    private function _docsis_mode($i)
    {
        switch ($i) {
            case 1: return 'DOCSIS 1.0';
            case 2: return 'DOCSIS 1.1';
            case 3: return 'DOCSIS 2.0';
            case 4: return 'DOCSIS 3.0';

            default: return 'n/a';
        }
    }

    /*
     * convert docsis modulation from int to human readable string
     */
    private function _docsis_modulation($a, $direction)
    {
        $r = [];
        foreach ($a as $m) {
            if ($direction == 'ds' || $direction == 'DS') {
                switch ($m) {
                    case 3: $b = 'QAM64'; break;
                    case 4: $b = 'QAM256'; break;
                    default: $b = null; break;
                }
            } else {
                switch ($m) {
                    case 0: $b = 'unknown'; break;	//no docsIfCmtsModulationTable entry
                    case 1: $b = 'other'; break;
                    case 2: $b = 'QPSK'; break;
                    case 3: $b = 'QAM16'; break;
                    case 4: $b = 'QAM8'; break;
                    case 5: $b = 'QAM32'; break;
                    case 6: $b = 'QAM64'; break;
                    case 7: $b = 'QAM128'; break;
                    default: $b = null; break;
                }
            }
            array_push($r, $b);
        }

        return $r;
    }

    /**
     * The Modem Realtime Measurement Function
     * Fetches all realtime values from Modem with SNMP
     *
     * TODO:
     * - add units like (dBmV, MHz, ..)
     * - speed-up: use SNMP::get with multiple gets in one request. Test if this speeds up stuff (?)
     *
     * @param host:  The Modem hostname like cm-xyz.abc.de
     * @param com:   SNMP RO community
     * @param ip: 	 IP address of modem
     * @param cacti: Is function called by cacti?
     * @return: array[section][Fieldname][Values]
     */
    public function realtime($host, $com, $ip, $cacti)
    {
        // Copy from SnmpController
        $this->snmp_def_mode();

        try {
            // First: get docsis mode, some MIBs depend on special DOCSIS version so we better check it first
            $docsis = snmpget($host, $com, '1.3.6.1.2.1.10.127.1.1.5.0'); // 1: D1.0, 2: D1.1, 3: D2.0, 4: D3.0
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'php_network_getaddresses: getaddrinfo failed: Name or service not known') !== false ||
                strpos($e->getMessage(), 'No response from') !== false) {
                return ['SNMP-Server not reachable' => ['' => [0 => '']]];
            } elseif (strpos($e->getMessage(), 'Error in packet at') !== false) {
                $docsis = 1;
            }
        }

        $cmts = Modem::get_cmts($ip);
        $sys = [];
        // these values are not important for cacti, so only retrieve them on the analysis page
        if (! $cacti) {
            $sys['SysDescr'] = [snmpget($host, $com, '.1.3.6.1.2.1.1.1.0')];
            $sys['Firmware'] = [snmpget($host, $com, '.1.3.6.1.2.1.69.1.3.5.0')];
            $sys['Uptime'] = [$this->_secondsToTime(snmpget($host, $com, '.1.3.6.1.2.1.1.3.0') / 100)];
            $sys['DOCSIS'] = [$this->_docsis_mode($docsis)]; // TODO: translate to DOCSIS version
            $sys['CMTS'] = [$cmts->hostname];
            $ds['Frequency MHz'] = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.1.1.2'), 1000000);
            $us['Frequency MHz'] = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.2.1.2'), 1000000);
            $us['Modulation Profile'] = $this->_docsis_modulation($cmts->get_us_mods(snmpwalk($host, $com, '1.3.6.1.2.1.10.127.1.1.2.1.1')), 'us');
        }

        // Downstream
        $ds['Modulation'] = $this->_docsis_modulation(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.1.1.4'), 'ds');
        $ds['Power dBmV'] = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.1.1.6'));
        try {
            $ds['MER dB'] = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.4.1.4491.2.1.20.1.24.1.1'));
        } catch (\Exception $e) {
            $ds['MER dB'] = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.4.1.5'));
        }
        $ds['Microreflection -dBc'] = snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.4.1.6');

        // Upstream
        $us['Width MHz'] = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.2.1.3'), 1000000);
        if ($docsis >= 4) {
            $us['Power dBmV'] = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.4.1.4491.2.1.20.1.2.1.1'));
        } else {
            $us['Power dBmV'] = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.2.2.1.3.2'));
        }
        $us['SNR dB'] = $cmts->get_us_snr($ip);

        // remove all inactive channels (no range success)
        foreach ($ds['Power dBmV'] as $key => $val) {
            if ($ds['Modulation'][$key] == '' && $ds['MER dB'][$key] == 0) {
                foreach ($ds as $entry => $arr) {
                    unset($ds[$entry][$key]);
                }
            }
        }

        if ($docsis >= 4) {
            foreach (snmpwalk($host, $com, '1.3.6.1.4.1.4491.2.1.20.1.2.1.9') as $key => $val) {
                if ($val != 4) {
                    foreach ($us as $entry => $arr) {
                        unset($us[$entry][$key]);
                    }
                }
            }
        }

        // Put Sections together
        $ret['System'] = $sys;
        $ret['Downstream'] = $ds;
        $ret['Upstream'] = $us;

        return $ret;
    }

    /**
     * Calculate and set "Actual RX Power" of CMTS
     *
     * @param cmts:	CMTS object
     * @param com:	SNMP RW community
     * @param us:	Upstream values
     * @return: array[section][Fieldname][Values]
     */
    protected function _set_new_rx_power($cmts, $com, $us)
    {
        echo "$cmts->hostname\n";

        $rx_pwr = [];
        foreach (array_keys($us['Frequency MHz']) as $idx) {
            // continue if rx power is not available or zero (i.e. no CM on the channel)
            if ($us['Rx Power dBmV'][$idx] === 'n/a' || ! $us['SNR dB'][$idx]) {
                continue;
            }
            // the reference SNR is 24 dB
            $r = round($us['Rx Power dBmV'][$idx] + 24 - $us['SNR dB'][$idx]);
            // minimum actual power is 0 dB
            if ($r < 0) {
                $r = 0;
            }
            // maximum actual power is 10 dB
            if ($r > 10) {
                $r = 10;
            }

            echo "$idx: $r\t(".$us['SNR dB'][$idx].")\n";
            try {
                snmpset($cmts->ip, $com, ".1.3.6.1.4.1.4491.2.1.20.1.25.1.2.$idx", 'i', 10 * $r);
            } catch (\Exception $e) {
                $out = "error while setting new exptected us power on CMTS $cmts->hostname ($idx: $r)\n";
                echo $out;
                \Log::error($out);
            }

            $rx_pwr[$idx] = $r;
        }

        return $rx_pwr;
    }

    /**
     * The CMTS Realtime Measurement Function
     * Fetches all realtime values from CMTS with SNMP
     *
     * @param cmts:	CMTS object
     * @param com:	SNMP RO community
     * @param ctrl:	shall the RX power be controlled?
     * @return: array[section][Fieldname][Values]
     */
    public function realtime_cmts($cmts, $com, $ctrl = false)
    {
        // Copy from SnmpController
        $this->snmp_def_mode();
        try {
            // First: get docsis mode, some MIBs depend on special DOCSIS version so we better check it first
            $docsis = snmpget($cmts->ip, $com, '1.3.6.1.2.1.10.127.1.1.5.0'); // 1: D1.0, 2: D1.1, 3: D2.0, 4: D3.0
        } catch (\Exception $e) {
            if (((strpos($e->getMessage(), 'php_network_getaddresses: getaddrinfo failed: Name or service not known') !== false) || (strpos($e->getMessage(), 'No response from') !== false))) {
                return ['SNMP-Server not reachable' => ['' => [0 => '']]];
            }
            if (strpos($e->getMessage(), 'noSuchName') !== false) {
                $docsis = 0;
            }
        }

        // System
        $sys['SysDescr'] = [snmpget($cmts->ip, $com, '.1.3.6.1.2.1.1.1.0')];
        $sys['Uptime'] = [$this->_secondsToTime(snmpget($cmts->ip, $com, '.1.3.6.1.2.1.1.3.0') / 100)];
        $sys['DOCSIS'] = [$this->_docsis_mode($docsis)];

        $freq = snmprealwalk($cmts->ip, $com, '.1.3.6.1.2.1.10.127.1.1.2.1.2');
        try {
            $desc = snmprealwalk($cmts->ip, $com, '.1.3.6.1.2.1.31.1.1.1.18');
        } catch (\Exception $e) {
            $desc = ['n/a'];
        }
        $snr = snmprealwalk($cmts->ip, $com, '.1.3.6.1.2.1.10.127.1.1.4.1.5');
        try {
            $util = snmprealwalk($cmts->ip, $com, '.1.3.6.1.2.1.10.127.1.3.9.1.3');
        } catch (\Exception $e) {
        }
        try {
            $rx = snmprealwalk($cmts->ip, $com, '.1.3.6.1.4.1.4491.2.1.20.1.25.1.2');
        } catch (\Exception $e) {
        }

        $us = [];
        foreach ($freq as $idx => $val) {
            $idx = last(explode('.', $idx));
            $us['Frequency MHz'][$idx] = $val / 1000000;
            $us['Cluster'][$idx] = array_values($desc)[last(array_flip(preg_grep("/\.$idx$/", array_keys($desc))))];
            $us['SNR dB'][$idx] = array_values($snr)[last(array_flip(preg_grep("/\.$idx$/", array_keys($snr))))] / 10;
            // if utilization is always zero, DOCS-IF-MIB::docsIfCmtsChannelUtilizationInterval must be set to a non-zero value
            $us['Avg Utilization %'][$idx] = isset($util) ? array_values($util)[last(array_flip(preg_grep("/$idx\.129\.\d+$/", array_keys($util))))] : 'n/a';
            $us['Rx Power dBmV'][$idx] = isset($rx) ? array_values($rx)[last(array_flip(preg_grep("/\.$idx$/", array_keys($rx))))] / 10 : 'n/a';
        }

        // unset unused interfaces, as we don't want to show them on the web gui
        foreach (array_keys($us['SNR dB']) as $idx) {
            if ($cmts->company == 'Casa' && $us['SNR dB'][$idx] == 0) {
                foreach (array_keys($us) as $entry) {
                    unset($us[$entry][$idx]);
                }
            }
        }

        if ($ctrl) {
            $us['Rx Power dBmV'] = $this->_set_new_rx_power($cmts, $cmts->get_rw_community(), $us);
        }

        $ret['System'] = $sys;
        $ret['Upstream'] = $us;

        return $ret;
    }

    /**
     * Set PHP SNMP Default Values
     * Note: Must be only called once per Object Init
     *
     * Note: copy from SnmpController
     *
     * @author Torsten Schmidt
     */
    private function snmp_def_mode()
    {
        snmp_set_quick_print(true);
        snmp_set_oid_numeric_print(true);
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
        snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
    }

    /**
     * Returns the lease entry that contains 1 or 2 strings specified in the function arguments
     *
     * TODO: make a seperate class for dhcpd
     * lease stuff (search, replace, ..)
     *
     * @return array 	of lease entry strings
     */
    public function search_lease()
    {
        $ret = [];

        if (func_num_args() <= 0) {
            \Log::error('No argument specified in '.__CLASS__.'::'.__FUNCTION__);

            return $ret;
        }

        $search = func_get_arg(0);

        if (func_num_args() == 2) {
            $search2 = func_get_arg(1);
        }

        // parse dhcpd.lease file
        $file = file_get_contents('/var/lib/dhcpd/dhcpd.leases');
        // start each lease with a line that begins with "lease" and end with a line that begins with "{"
        preg_match_all('/^lease(.*?)(^})/ms', $file, $section);

        // fetch all lines matching hw mac
        foreach (array_unique($section[0]) as $s) {
            if (strpos($s, $search)) {
                if (isset($search2) && ! strpos($s, $search2)) {
                    continue;
                }

                $s = str_replace('  ', '&nbsp;&nbsp;', $s);

                // push matching results
                array_push($ret, preg_replace('/\r|\n/', '<br/>', $s));
            }
        }

        // handle multiple lease entries
        // actual strategy: if possible grep active lease, otherwise return all entries
        //                  in reverse ordered format from dhcpd.leases
        if (count($ret) > 1) {
            foreach (preg_grep('/(.*?)binding state active(.*?)/', $ret) as $str) {
                if (preg_match('/starts \d ([^;]+);/', $str, $s)) {
                    $start[] = $s[1];
                }
            }

            if (isset($start)) {
                // return the most recent active lease
                natsort($start);
                end($start);

                return [$ret[each($start)[0]]];
            }
        }

        return $ret;
    }

    /**
     * Local Helper: Convert String Time Diff to Unix Timestamp
     * Example: '-3d' => to now() - 3 days => unix time 1450350686
     *          '-3h' => to now() - 3 hours => unix time 1450350686
     *
     * Usage: (-) followd by integer AND
     *        d .. day, h .. hour, M .. month, y .. year, m .. minute
     *
     * TODO: - Move to own extensions Carbon Helper API
     *       - use regular expression for validation matching
     *
     * @param d: string encoded time difference to now
     * @return: unix timestamp, returns NOW on input failures, see TODO
     *
     * @author: Torsten Schmidt
     */
    private function _date($d)
    {
        $d = str_replace('-', '', $d);
        $v = substr($d, 0, -1);

        if (substr($d, -1) == 'y') {
            return \Carbon\Carbon::now()->subYear($v)->timestamp;
        }
        if (substr($d, -1) == 'M') {
            return \Carbon\Carbon::now()->subMonth($v)->timestamp;
        }
        if (substr($d, -1) == 'd') {
            return \Carbon\Carbon::now()->subDay($v)->timestamp;
        }
        if (substr($d, -1) == 'h') {
            return \Carbon\Carbon::now()->subHour($v)->timestamp;
        }
        if (substr($d, -1) == 'm') {
            return \Carbon\Carbon::now()->subMinute($v)->timestamp;
        }

        return \Carbon\Carbon::now()->timestamp;
    }

    /**
     * Get the corresponing graph id's for $modem. These id's could
     * be used in graph_image.php as HTML GET Request with local_graph_id variable
     * like https://../cacti/graph_image.php?local_graph_id=<ID>
     *
     * NOTE: This function requires a valid 'mysql-cacti' array
     *       in config/database.php
     *
     * @param modem: The modem to look for Cacti Graphs
     * @param graph_template: only show array[] of cacti graph template ids in result
     * @return: collection of related cacti graph id's, empty collection if no entries are found
     *
     * @author: Torsten Schmidt
     */
    public static function monitoring_get_graph_ids($modem, $graph_template = [])
    {
        // Connect to Cacti DB
        $cacti = \DB::connection('mysql-cacti');

        // Get Cacti Host ID to $modem
        $host = $cacti->table('host')->where('description', '=', $modem->hostname)->get();
        if (! isset($host[0])) {
            return collect();
        }
        $host_id = $host[0]->id;

        if (! $graph_template) {
            return $cacti->table('graph_local')->where('host_id', $host_id)->orderBy('graph_template_id')->pluck('id');
        }

        // Get all Graph IDs to Modem
        $ret = collect();
        // we must use foreach instead of orWhere here, because we want to get the diagrams in a certain order
        foreach ($graph_template as $tmpl) {
            $ret = $ret->merge($cacti->table('graph_local')->where('host_id', $host_id)->where('graph_template_id', $tmpl)->pluck('id'));
        }

        return $ret;
    }

    /**
     * Get the corresponing graph id's for $netelem. These id's could
     * be used in graph_image.php as HTML GET Request with local_graph_id variable
     * like https://../cacti/graph_image.php?local_graph_id=<ID>
     *
     * NOTE: This function requires a valid 'mysql-cacti' array
     *       in config/database.php
     *
     * @param netelem: The netelem to look for Cacti Graphs
     * @return: collection of related cacti graph id's, empty collection if no entries are found
     *
     * @author: Ole Ernst
     */
    public static function monitoring_get_netelement_graph_ids($netelem)
    {
        // Search parent CMTS for type cluster
        if ($netelem->netelementtype_id == 2 && ! $netelem->ip && $cmts = $netelem->get_parent_cmts()) {
            $ip = $cmts->ip;
        } else {
            $ip = $netelem->ip;
        }
        // resolve IP address if necessary
        $ip = gethostbyname($ip);

        $host_id = self::monitoring_get_host_id($ip);
        if (! $host_id) {
            return collect();
        }
        /*
        $vendor = $netelem->netelementtype->vendor;
        $graph_template_id = self::monitoring_get_graph_template_id("$vendor CMTS Overview");
        if (! $graph_template_id)
            return;
        */
        $idxs = $netelem
            ->indices
            ->pluck('indices')
            ->map(function ($i) {
                return explode(',', $i);
            })->collapse();

        $q = \DB::connection('mysql-cacti')
            ->table('graph_local')
            ->where('host_id', $host_id)
            /*->where('graph_template_id', $graph_template_id)*/
            ->where(function ($q) use ($idxs) {
                foreach ($idxs as $idx) {
                    $q->orWhere('snmp_index', $idx);
                }
            });

        return $q->pluck('id');
    }

    /**
     * The Main Monitoring Function
     * Returns the prepared monitoring array required for monitoring view
     * This Array contains: Timing and the pre-loaded Images and looks like:
     *
     * array:5 [▼
     *  "from" => "3h"
     *  "to" => "0"
     *  "from_t" => 1450680378
     *  "to_t" => 1450691178
     *  "graphs" => array:4 [▼
     *  	119 => "data:application/octet-stream;base64,iVBORw0K .."
     *      120 => ..
     *   ]
     * ]
     *
     * @param host: The host to look for Cacti Graphs
     * @param graph_template: only show array[] of cacti graph template ids in result
     * @return: the prepared monitoring array for view. Returns false if no diagram exists.
     *          No other adaptions required. See example in comment above
     *
     * @author: Torsten Schmidt
     */
    public function monitoring($host, $graph_template = [])
    {
        // Check if Cacti Host RRD files exist
        // This is a speed-up. A cacti HTTP request takes more time.
        if (get_class($host) != 'Modules\HfcReq\Entities\NetElement' && ! glob('/usr/share/cacti/rra/'.$host->hostname.'*')) {
            return false;
        }

        // parse diagram id's from cacti database
        if (get_class($host) == 'Modules\HfcReq\Entities\NetElement') {
            $ids = self::monitoring_get_netelement_graph_ids($host);
        } else {
            $ids = self::monitoring_get_graph_ids($host, $graph_template);
        }

        // no id's return
        if ($ids->isEmpty()) {
            return false;
        }

        /*
         * Time Span Calculation
         */
        $from = \Input::get('from');
        $to = \Input::get('to');

        if (! $from) {
            $from = '-3d';
        }
        if (! $to) {
            $to = '0';
        }

        $ret['from'] = $from;
        $ret['to'] = $to;

        // Convert Time
        $from_t = $this->_date($from);
        $to_t = $this->_date($to);

        $ret['from_t'] = $from_t;
        $ret['to_t'] = $to_t;

        /*
         * Images
         */
        $url_base = "/cacti/graph_image.php?rra_id=0&graph_start=$from_t&graph_end=$to_t";

        // TODO: should be auto adapted to screen resolution. Note that we still use width=100% setting
        // in the image view. This could lead to diffuse (unscharf) fonts.
        $graph_width = '700';

        // Fetch Cacti DB for images of $host and request the Image from Cacti
        foreach ($ids as $id) {
            $ret['graphs'][$id] = $url_base."&graph_width=$graph_width&local_graph_id=$id";
        }

        // No result checking
        if (! isset($ret['graphs'])) {
            return false;
        }

        // default return
        return $ret;
    }

    /**
     * Get the cacti host id, which corresponds to a given hostname/ip address of the host object
     *
     * @param host: The host object or ip address
     * @return: The cacti host id
     *
     * @author: Ole Ernst
     */
    public static function monitoring_get_host_id($host)
    {
        try {
            $ret = \Schema::connection('mysql-cacti')->hasTable('host');
        } catch (\PDOException $e) {
            return false;
        }

        // Get Cacti Host ID of $host
        $host = \DB::connection('mysql-cacti')
            ->table('host')
            ->where(is_string($host) ? 'hostname' : 'description', is_string($host) ? $host : $host->hostname)
            ->first();

        if (! isset($host)) {
            return false;
        }

        return $host->id;
    }

    /**
     * Get the cacti graph template ids, which correspond to a given graph template name
     *
     * @param name: The cacti graph template name
     * @return: The matching cacti graph template id
     *
     * @author: Ole Ernst
     */
    public static function monitoring_get_graph_template_id($name)
    {
        // Connect to Cacti DB
        $cacti = \DB::connection('mysql-cacti');

        // Get Cacti Host ID to $modem
        $template = $cacti->table('graph_templates')->where('name', '=', $name)->select('id')->first();
        if (! isset($template)) {
            return;
        }

        return $template->id;
    }

    /**
     * Returns the Diagram View for a NetElement (Device)
     *
     * @param   id          The NetElement id
     * @author  Ole Ernst
     */
    public function diagram_edit($id)
    {
        $monitoring = [];
        $dia = $this->monitoring(NetElement::findOrFail($id));
        $netelem = NetElement::findOrFail($id);

        // reshape array according to HfcCustomer::Tree.dias
        // we might want to split these in the future, to avoid the module dependency
        if ($dia) {
            foreach ($dia['graphs'] as $idx => $graph) {
                $dia['graphs'] = [$idx => $graph];
                $dia['row'] = '';
                $monitoring[] = $dia;
            }
        }

        $panel_right = self::checkNetelementtype($netelem);

        return \View::make('HfcCustomer::Tree.dias', $this->compact_prep_view(compact('monitoring', 'panel_right')));
    }

    /**
     * Defines all tabs for the Netelementtypes.
     * Note: 1 = Net, 2 = Cluster, 3 = Cmts, 4 = Amplifier, 5 = Node, 6 = Data, 7 = UPS
     *
     * @author Roy Schneider
     * @param Modules\HfcReq\Entities\NetElement
     * @return array
     */
    public static function checkNetelementtype($model)
    {
        $type = $model->netelementtype->get_base_type();

        $tabs = [['name' => 'Edit', 'route' => 'NetElement.edit', 'link' => [$model->id]], ];

        if ($type <= 2) {
            array_push($tabs,
                ['name' => 'Entity Diagram', 'route' => 'TreeErd.show', 'link' => [$model->netelementtype->name, $model->id]],
                ['name' => 'Topography', 'route' => 'TreeTopo.show', 'link' => [$model->netelementtype->name, $model->id]]
            );
        }

        if ($type != 1) {
            array_push($tabs, ['name' => 'Controlling', 'route' => 'NetElement.controlling_edit', 'link' => [$model->id, 0, 0]]);
        }

        if ($type == 4 || $type == 5) {

            array_push($tabs, ['name' => 'Analyses', 'route' => 'ProvMon.index', 'link' => [substr($model->ip, 3, 6)]]);
        }

        if ($type != 4  && $type != 5) {
            array_push($tabs, ['name' => 'Diagrams', 'route' => 'ProvMon.diagram_edit', 'link' => [$model->id]]);
        }

        return $tabs;
    }

    /**
     * Add Logging tab in edit page.
     * from BaseController
     *
     * @author Roy Schneider
     * @param array, Modules\HfcReq\Entities\NetElement
     * @return array
     */
    public function loggingTab($array, $model)
    {
        $baseController = new BaseController;
        array_push($array, $baseController->get_form_tabs($model)[0]);

        return $array;
    }

    /*
     * Functions for Feature single Windows Stuff
     * This stuff is at the time not in production
     */

    /**
     * Monitoring
     *
     * @return Response
     */
    public function _monitoring_deprecated($id)
    {
        $modem = Modem::find($id);

        return View::make('provbase::Modem.monitoring', compact('modem'));
    }

    /**
     * Leases
     *
     * @return Response
     */
    public function lease($id)
    {
        $modem = Modem::find($id);
        $mac = $modem->mac;

        // view
        return View::make('provbase::Modem.lease', compact('modem'))->with('out', $ret);
    }

    /**
     * Log
     *
     * @return Response
     */
    public function log($id)
    {
        $modem = Modem::find($id);
        $hostname = $modem->hostname;
        $mac = $modem->mac;

        if (! exec('cat /var/log/messages | egrep "('.$mac.'|'.$hostname.')" | tail -n 100  | tac', $ret)) {
            $out = ['no logging'];
        }

        return View::make('provbase::Modem.log', compact('modem', 'out'));
    }
}
