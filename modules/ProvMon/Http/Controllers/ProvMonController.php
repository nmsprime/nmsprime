<?php

namespace Modules\ProvMon\Http\Controllers;

use View;
use Acme\php\ArrayHelper;
use Modules\ProvBase\Entities\Cmts;
use Modules\ProvBase\Entities\Modem;
use Modules\HfcReq\Entities\NetElement;
use Modules\ProvBase\Entities\ProvBase;
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
     * @param int   modem id
     * @return array
     */
    public function analysisPages($id)
    {
        $modem = $this->modem ?: Modem::findOrFail($id);

        $tabs = [['name' => 'Analyses', 'route' => 'ProvMon.index', 'link' => $id],
                ['name' => 'CPE-Analysis', 'route' => 'ProvMon.cpe', 'link' => $id],
                ];

        array_unshift($tabs, $this->defineEditRoute($id));

        if (isset($modem->mtas[0])) {
            array_push($tabs, ['name' => 'MTA-Analysis', 'route' => 'ProvMon.mta', 'link' => $id]);
        }

        return $tabs;
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
        $modem = $this->modem ?: Modem::findOrFail($id);

        $edit = ['name' => 'Edit', 'route' => 'Modem.edit', 'link' => $id];

        if (isset($modem->mtas[0]) && $session == 'MTA') {
            $edit = ['name' => 'Edit', 'route' => 'Mta.edit', 'link' => $modem->mtas[0]->id];
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
        $ping = $lease = $log = $dash = $realtime = $type = $flood_ping = $configfile = $eventlog = $data = null;
        $modem = $this->modem ? $this->modem : Modem::find($id);
        $view_var = $modem; // for top header
        $error = '';
        $message = trans('view.error_specify_id');

        // if there is no valid hostname specified, then return error view
        // to get the regular Analyses tab the hostname should be: cm-...
        if ($id == 'error') {
            return View::make('errors.generic', compact('error', 'message'));
        }

        $hostname = $modem->hostname.'.'.$this->domain_name;
        $mac = strtolower($modem->mac);
        $modem->help = 'modem_analysis';

        // takes approx 0.1 sec
        $ip = gethostbyname($hostname);
        $ip = ($ip == $hostname) ? null : $ip;

        // Ping: Only check if device is online
        // takes approx 0.1 sec
        exec('sudo ping -c1 -i0 -w1 '.$hostname, $ping, $ret);
        $online = $ret ? false : true;

        // Flood Ping
        $flood_ping = $this->flood_ping($hostname);

        // Lease
        $lease['text'] = $this->search_lease('hardware ethernet '.$mac);
        $lease = $this->validate_lease($lease, $type);

        // Configfile
        $configfile = self::_get_configfile("/tftpboot/cm/$modem->hostname");

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
        // NOTE: This function takes a long time if syslog file is large - 0.4 to 0.6 sec
        $search = $ip ? "$mac|$modem->hostname[^0-9]|$ip " : "$mac|$modem->hostname[^0-9]";
        $log = self::_get_syslog_entries($search, '| grep -v MTA | grep -v CPE | tail -n 30  | tac');

        // Dashboard
        $dash['modemServicesStatus'] = self::modemServicesStatus($modem, $configfile['text']);
        // time of this function should be observed - can take a huge time as well
        if ($online) {
            $modemConfigfileStatus = self::modemConfigfileStatus($modem);
            if ($modemConfigfileStatus) {
                $dash['modemConfigfileStatus'] = $modemConfigfileStatus;
            }
        }

        $host_id = $this->monitoring_get_host_id($modem);

        $tabs = $this->analysisPages($id);
        $view_header = 'ProvMon-Analyses';

        // View
        return View::make('provmon::analyses', $this->compact_prep_view(compact('modem', 'online', 'tabs', 'lease', 'log', 'configfile',
                'eventlog', 'dash', 'realtime', 'host_id', 'view_var', 'flood_ping', 'ip', 'view_header', 'data', 'id')));
    }

    /**
     * Get contents, mtime of configfile and warn if it is outdated
     *
     * @author  Ole Ernst
     * @param   path    String  Path of the configfile excluding its extension
     * @return  array
     */
    private static function _get_configfile($path)
    {
        if (! is_file("$path.conf") || ! is_file("$path.cfg")) {
            return;
        }

        if (filemtime("$path.conf") > filemtime("$path.cfg")) {
            $conf['warn'] = trans('messages.configfile_outdated');
        }

        $conf['mtime'] = strftime('%c', filemtime("$path.cfg"));

        exec("docsis -d $path.cfg", $conf['text']);
        $conf['text'] = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $conf['text']);

        return $conf;
    }

    /**
     * Determine modem status of internet access and telephony for analyses dashboard
     *
     * @param object    Modem
     * @param array     Lines of Configfile
     * @return array    Color & status text
     */
    public static function modemServicesStatus($modem, $config)
    {
        $networkAccess = preg_grep('/NetworkAccess \d/', $config);
        preg_match('/NetworkAccess (\d)/', end($networkAccess), $match);
        $networkAccess = $match[1];

        // Internet and voip blocked
        if (! $networkAccess) {
            return ['bsclass' => 'warning', 'text' => trans('messages.modemAnalysis.noNetworkAccess')];
        }

        $maxCpe = preg_grep('/MaxCPE \d/', $config);
        preg_match('/MaxCPE (\d)/', end($maxCpe), $match);
        $maxCpe = $match[1];

        $cpeMacs = preg_grep('/CpeMacAddress (.*?);/', $config);

        // Internet and voip allowed
        if ($maxCpe > count($cpeMacs)) {
            return ['bsclass' => 'success', 'text' => trans('messages.modemAnalysis.fullAccess')];
        }

        // Only voip allowed
        // Check if configfile contains a different CPE MTA than the MTAs have - this case is actually [2019-03-06] not valid
        $mtaMacs = $modem->mtas->each(function ($mac) {
            $mac->mac = strtolower($mac->mac);
        })->pluck('mac')->all();

        foreach ($cpeMacs as $line) {
            preg_match('/CpeMacAddress (.*?);/', $line, $match);

            $cpeMac = strtolower($match[1]);

            if (! in_array($cpeMac, $mtaMacs)) {
                return ['bsclass' => 'info', 'text' => trans('messages.modemAnalysis.cpeMacMissmatch')];
            }
        }

        return ['bsclass' => 'info', 'text' => trans('messages.modemAnalysis.onlyVoip')];
    }

    /**
     * Determine if modem runs with/has already downloaded actual configfile
     *
     * @param object Modem
     */
    public static function modemConfigfileStatus($modem)
    {
        $path = '/var/log/nmsprime/tftpd-cm.log';
        $ts_cf = filemtime("/tftpboot/cm/$modem->hostname.cfg");
        $ts_dl = exec("zgrep $modem->hostname $path | tail -1 | cut -d' ' -f1");

        if (! $ts_dl) {
            // get all but the current logfile, order them descending by file modification time
            // we assume that logrotate adds "-TIMESTAMP" to the logfiles name
            $logfiles = glob("$path-*");
            usort($logfiles, function ($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            foreach ($logfiles as $path) {
                // get the latest line indicating a configfile download
                $ts_dl = exec("zgrep $modem->hostname $path | tail -1 | cut -d' ' -f1");

                if ($ts_dl) {
                    break;
                }
            }
        }

        if (! $ts_dl) {
            // inform the user that last download was to long ago to check if the configfile is up-to-date
            return ['bsclass' => 'info', 'text' => trans('messages.modemAnalysis.missingLD')];
        }

        if ($ts_dl <= $ts_cf) {
            return ['bsclass' => 'warning', 'text' => trans('messages.modemAnalysis.cfOutdated')];
        }
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

        $tabs = $this->analysisPages($id);

        $view_header = 'Provmon-CPE';

        return View::make('provmon::cpe_analysis', $this->compact_prep_view(compact('modem', 'ping', 'type', 'tabs', 'lease', 'log', 'dash', 'realtime', 'view_var', 'view_header')));
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
        $configfile = self::_get_configfile("/tftpboot/mta/$mta->hostname");

        // log
        $ip = gethostbyname($mta->hostname);
        $ip = $mta->hostname == $ip ? null : $ip;
        $mac = strtolower($mta->mac);
        $search = $ip ? "$mac|$mta->hostname|$ip " : "$mac|$mta->hostname";
        $log = self::_get_syslog_entries($search, '| tail -n 25  | tac');
        // exec ('grep -i "'.$mta->mac.'\|'.$mta->hostname.'" /var/log/messages | grep -v "DISCOVER from" | tail -n 20  | tac', $log);

        end:
        $tabs = $this->analysisPages($id);

        $view_header = 'Provmon-MTA';

        return View::make('provmon::cpe_analysis', $this->compact_prep_view(compact('modem', 'ping', 'type', 'tabs', 'lease', 'log', 'dash', 'realtime', 'configfile', 'view_var', 'view_header')));
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

        $tabs = [
            ['name' => 'Edit', 'route' => 'Cmts.edit', 'link' => $id],
            ['name' => 'Analysis', 'route' => 'ProvMon.cmts', 'link' => $id],
        ];

        $view_header = 'Provmon-CMTS';

        return View::make('provmon::cmts_analysis', $this->compact_prep_view(compact('ping', 'tabs', 'lease', 'log', 'dash', 'realtime', 'host_id', 'view_var', 'view_header')));
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
            $sys['Status Code'] = [snmpget($host, $com, '.1.3.6.1.2.1.10.127.1.2.2.1.2.2')];
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

        $snrs = $cmts->get_us_snr($ip);
        foreach ($us['Frequency MHz'] as $freq) {
            $us['SNR dB'][] = isset($snrs[strval($freq)]) ? $snrs[strval($freq)] : 'n/a';
        }

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
     * The CMTS Realtime Measurement Function
     * Fetches all realtime values from CMTS with SNMP
     *
     * @param cmts:	CMTS object
     * @param com:	SNMP RO community
     * @param ctrl:	shall the RX power be controlled?
     * @return: array[section][Fieldname][Values]
     */
    public function realtime_cmts($cmts, $com)
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
            foreach ($ret as $text) {
                if (preg_match('/starts \d ([^;]+);.*;binding state active;/', $text, $match)) {
                    $start[] = $match[1];
                    $lease[] = $text;
                }
            }

            if (isset($start)) {
                // return the most recent active lease
                natsort($start);
                end($start);

                return [$lease[key($start)]];
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

        $tabs = self::checkNetelementtype($netelem);

        return \View::make('HfcCustomer::Tree.dias', $this->compact_prep_view(compact('monitoring', 'tabs')));
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
        if (! isset($model->netelementtype)) {
            return [];
        }

        $type = $model->netelementtype->get_base_type();
        $provmon = new self;

        $tabs = [['name' => 'Edit', 'route' => 'NetElement.edit', 'link' => $model->id]];

        if ($type <= 2) {
            array_push($tabs,
                ['name' => 'Entity Diagram', 'route' => 'TreeErd.show', 'link' => [$model->netelementtype->name, $model->id]],
                ['name' => 'Topography', 'route' => 'TreeTopo.show', 'link' => [$model->netelementtype->name, $model->id]]
            );
        }

        if ($type != 1) {
            array_push($tabs, ['name' => 'Controlling', 'route' => 'NetElement.controlling_edit', 'link' => [$model->id, 0, 0]]);
        }

        if ($type == 4 || $type == 5 && \Bouncer::can('view_analysis_pages_of', Modem::class)) {
            //create Analyses tab (for ORA/VGP) if IP address is no valid IP
            array_push($tabs, ['name' => 'Analyses', 'route' => 'ProvMon.index', 'link' => $provmon->createAnalysisTab($model->ip)]);
        }

        if ($type != 4 && $type != 5) {
            array_push($tabs, ['name' => 'Diagrams', 'route' => 'ProvMon.diagram_edit', 'link' => [$model->id]]);
        }

        return $tabs;
    }

    /**
     * Return number from IP address field if the record is written like: 'cm-...'.
     *
     * @author Roy Schneider
     * @param string
     * @return string
     */
    public function createAnalysisTab($ip)
    {
        preg_match('/[c][m]\-\d+/', $ip, $return);

        if (empty($return)) {
            return 'error';
        }

        return substr($return[0], 3);
    }

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

    /**
     * Retrieve Data via SNMP and create Array for spectrum in Modem Analyses page.
     *
     * @author Roy Schneider
     * @param Modules\ProvBase\Entities\Modem
     * @return JSON response
     */
    public function getSpectrumData($id)
    {
        $provbase = ProvBase::first();
        $modem = Modem::find($id);
        $hostname = $modem->hostname;
        $roCommunity = $provbase->ro_community;
        $rwCommunity = $provbase->rw_community;

        // set frequency span from 150 to 862 MHz
        snmp2_set($hostname, $rwCommunity, '.1.3.6.1.4.1.4491.2.1.20.1.34.3.0', 'u', 154000000);
        snmp2_set($hostname, $rwCommunity, '.1.3.6.1.4.1.4491.2.1.20.1.34.4.0', 'u', 866000000);

        // every 8 MHz
        snmp2_set($hostname, $rwCommunity, '.1.3.6.1.4.1.4491.2.1.20.1.34.5.0', 'u', 8000000);

        // enable docsIf3CmSpectrumAnalysisCtrlCmd after setting values
        snmp2_set($hostname, $rwCommunity, '.1.3.6.1.4.1.4491.2.1.20.1.34.1.0', 'i', 1);

        // after enabling docsIf3CmSpectrumAnalysisCtrlCmd it may take a few seconds to start the snmpwalḱ (error: End of MIB)
        $time = 1;
        while ($time <= 30) {
            try {
                $expressions = snmp2_real_walk($hostname, $roCommunity, '.1.3.6.1.4.1.4491.2.1.20.1.35.1.3');
            } catch (\Exception $e) {
                $time++;
                sleep(1);
                continue;
            }

            break;
        }

        // in case we don't get return values
        if (! isset($expressions)) {
            return;
        }

        // filter expression for ampitude
        // returned values: level in 10th dB and frequency in Hz
        // Example: SNMPv2-SMI::enterprises.4491.2.1.20.1.35.1.3.985500000 = INTEGER: -361
        foreach ($expressions as $oid => $level) {
            preg_match('/[0-9]{7,}/', $oid, $frequency);
            $data['span'][] = $frequency[0] / 1000000;
            $data['amplitudes'][] = intval($level) / 10;
        }

        return response()->json($data);
    }
}
