<?php

namespace Modules\ProvMon\Http\Controllers;

use Log;
use View;
use Acme\php\ArrayHelper;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\NetGw;
use Modules\HfcReq\Entities\NetElement;
use Modules\ProvBase\Entities\ProvBase;
use Modules\ProvBase\Entities\Configfile;
use App\Http\Controllers\BaseViewController;

/**
 * This is the Basic Stuff for Modem Analyses Page
 * Note: this class does not have a corresponding Model
 *       it fetches all required stuff from Modem or Server
 *
 * @author: Torsten Schmidt
 */
class ProvMonController extends \BaseController
{
    const MODEM_IMAGE_PATH = 'images/modems';
    protected $domain_name = '';
    protected $modem = null;
    protected $edit_left_md_size = 12;

    public function __construct()
    {
        $this->domain_name = ProvBase::first()->domain_name;
        parent::__construct();
    }

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        return [
            ['form_type' => 'text', 'name' => 'start_frequency', 'description' => trans('messages.start_frequency_spectrum')],
            ['form_type' => 'text', 'name' => 'stop_frequency', 'description' => trans('messages.stop_frequency_spectrum')],
            ['form_type' => 'text', 'name' => 'span', 'description' => trans('messages.span_spectrum')],
        ];
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
        // if there is no valid hostname specified, then return error view
        // to get the regular Analyses tab the hostname should be: cm-...
        if ($id == 'error') {
            $error = '';
            $message = trans('view.error_specify_id');

            return View::make('errors.generic', compact('error', 'message'));
        }

        $modem = $this->modem ?? Modem::find($id);
        $mac = strtolower($modem->mac);
        $onlineStatus = $this->modemOnlineStatus($modem);

        $modem->help = 'modem_analysis';
        $view_var = $modem;
        $view_header = 'ProvMon-Analyses';

        if ($modem->isTR069()) {
            $prov = json_decode(Modem::callGenieAcsApi("provisions/?query={\"_id\":\"{$id}\"}", 'GET'));

            if ($prov && isset($prov[0]->script)) {
                $configfile['text'] = preg_split('/\r\n|\r|\n/', $prov[0]->script);
            } else {
                $configfile['text'] = [];
            }
        } else {
            $configfile = $this->getConfigfileText("/tftpboot/cm/$modem->hostname");
        }

        // return $ip and $online
        foreach ($onlineStatus as $name => $value) {
            $$name = $value;
        }

        $realtime = $eventlog = null;

        if (\Request::has('offline')) {
            $online = false;
        }

        // this can be done irrespective of device online state
        $measure = $this->realtimePPP($modem);

        if ($online) {
            if ($modemConfigfileStatus = $this->modemConfigfileStatus($modem)) {
                $dash['modemConfigfileStatus'] = $modemConfigfileStatus;
            }

            if ($modem->isTR069()) {
                $measure = array_merge($this->realtimeTR069($modem, false), $measure);
            } else {
                // TODO: only load channel count to initialise the table and fetch data via AJAX call after Page Loaded
                $measure = array_merge($this->realtime($ip, ProvBase::first()->ro_community), $measure);

                // get eventlog table
                if (! array_key_exists('SNMP-Server not reachable', $measure)) {
                    $eventlog = $modem->get_eventlog();
                }
            }
        }
        $realtime['measure'] = $measure;
        $realtime['forecast'] = '';

        $device = \Modules\ProvBase\Entities\Configfile::where('id', $modem->configfile_id)->first()->device;
        // time of this function should be observed - can take a huge time as well
        $dash['modemServicesStatus'] = $this->modemServicesStatus($modem, $configfile, $device);

        // Log dhcp (discover, ...), tftp (configfile or firmware)
        // NOTE: This function takes a long time if syslog file is large - 0.4 to 0.6 sec
        $search = $ip ? "$mac|$modem->hostname[^0-9]|$ip " : "$mac|$modem->hostname[^0-9]";

        $log = $this->_get_syslog_entries($search, '| grep -v MTA | grep -v CPE | tail -n 30  | tac');
        $lease['text'] = $this->searchLease("hardware ethernet $mac");
        $lease = $this->validate_lease($lease);
        $host_id = $this->monitoring_get_host_id($modem);
        $flood_ping = $this->flood_ping($ip);

        $tabs = $this->analysisPages($id);
        $picture = $this->modemPicture($modem, $realtime);

        $pills = ['<ul class="nav nav-pills" id="loglease">'];
        foreach (['log', 'lease', 'configfile', 'eventlog'] as $pill) {
            if ($$pill) {
                $pills[] = "<li role=\"presentation\"><a href=\"#$pill\" data-toggle=\"pill\">".ucfirst($pill).'</a></li>';
            }
        }
        $pills[] = '</ul>';
        $pills = implode('', $pills);

        return View::make('provmon::analyses', $this->compact_prep_view(compact('modem', 'online', 'tabs', 'lease', 'log', 'configfile',
                'eventlog', 'dash', 'realtime', 'host_id', 'view_var', 'flood_ping', 'ip', 'view_header', 'data', 'id', 'device', 'picture', 'pills')));
    }

    /**
     * Get ID of Modem and ping it for Analyses page.
     *
     * @author  Roy Schneider
     * @param   modem    Modules\ProvBase\Entities\Modem
     * @param   hostname    string
     * @return  array
     */
    private function modemOnlineStatus($modem)
    {
        $hostname = $modem->hostname.'.'.$this->domain_name;
        $ip = gethostbyname($hostname);
        $ip = ($ip == $hostname) ? null : $ip;

        if ($modem->isPPP()) {
            $cur = $modem->radacct()->latest('radacctid')->first();
            if ($cur && ! $cur->acctstoptime) {
                $ip = $hostname = $cur->framedipaddress;
            }

            // workaround for tr069 devices, which block ICMP requests,
            // but listen on the HTTP(s) / SSH ports
            $con = null;
            foreach ([80, 443, 22] as $port) {
                try {
                    $con = fsockopen($ip, $port, $errno, $errstr, 1);
                } catch (\Exception $e) {
                    continue;
                }

                if ($con) {
                    fclose($con);

                    return ['ip' => $ip, 'online' => true];
                }
            }
        }

        // Ping: Only check if device is online
        // takes approx 0.1 sec
        exec('sudo ping -c1 -i0 -w1 '.$hostname, $ping, $ret);

        return ['ip' => $ip, 'online' => $ret ? false : true];
    }

    /**
     * Find matching picture of modem.model.
     *
     * @author  Roy Schneider
     * @param Modules\ProvBase\Entities\Modem
     * @param mixed
     * @return  string
     */
    private function modemPicture($modem, $realtimeValues)
    {
        if (isset($realtimeValues['measure']['System']['SysDescr']['0'])) {
            $model = $realtimeValues['measure']['System']['SysDescr']['0'];
        } else {
            $model = $modem->model;
        }

        foreach (collect(\File::allFiles(public_path(self::MODEM_IMAGE_PATH)))->sortBy(function ($file) {
            return $file->getFilename();
        }) as $file) {
            preg_match('/\d+-(.+)\..+/', $file->getFilename(), $filename);

            if (isset($filename[0]) && str_contains(strtoupper($model), strtoupper($filename[1]))) {
                return self::MODEM_IMAGE_PATH.'/'.$file->getFilename();
            }
        }

        return self::MODEM_IMAGE_PATH.'/default.webp';
    }

    /**
     * Get contents, mtime of configfile and warn if it is outdated
     *
     * @author  Ole Ernst
     * @param   path    String  Path of the configfile excluding its extension
     * @return  array
     */
    private function getConfigfileText($path)
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
    public function modemServicesStatus($modem, $config, $device = 'cm')
    {
        if ($device == 'tr069') {
            return;
        }

        if (! $config || ! isset($config['text']) || isset($config['warn'])) {
            return ['bsclass' => 'danger',
                'text' => $config['warn'] ?? trans('messages.modemAnalysis.cfError'),
                'instructions' => "docsis -e /tftpboot/cm/{$modem->hostname}.conf /tftpboot/keyfile /tftpboot/cm/{$modem->hostname}.cfg",
            ];
        }

        $networkAccess = preg_grep('/NetworkAccess \d/', $config['text']);
        preg_match('/NetworkAccess (\d)/', end($networkAccess), $match);
        $networkAccess = $match[1];

        // Internet and voip blocked
        if (! $networkAccess) {
            return ['bsclass' => 'warning', 'text' => trans('messages.modemAnalysis.noNetworkAccess')];
        }

        $maxCpe = preg_grep('/MaxCPE \d/', $config['text']);
        preg_match('/MaxCPE (\d)/', end($maxCpe), $match);
        $maxCpe = $match[1];

        $cpeMacs = preg_grep('/CpeMacAddress (.*?);/', $config['text']);

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
    public function modemConfigfileStatus($modem)
    {
        if (\Modules\ProvBase\Entities\Configfile::where('id', $modem->configfile_id)->first()->device == 'tr069') {
            return;
        }

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
    private function _get_syslog_entries($search, $grep_pipes)
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
        if (! \Request::filled('flood_ping')) {
            return;
        }

        $hostname = escapeshellarg($hostname);

        switch (\Request::get('flood_ping')) {
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
        $dhcpd_mac = implode(':', array_map(function ($byte) {
            if ($byte == '00') {
                return '0';
            }

            return ltrim($byte, '0');
        }, explode(':', $modem_mac)));
        $lease['text'] = $this->searchLease("billing subclass \"Client\" \"$dhcpd_mac\";");
        $lease = $this->validate_lease($lease, $type);

        $ep = $modem->endpoints()->first();
        if ($ep && $ep->fixed_ip && $ep->ip) {
            $lease = $this->_fake_lease($modem, $ep);
        }

        /// get MAC of CPE first
        $str = $this->_get_syslog_entries($modem_mac, '| grep CPE | tail -n 1 | tac');
        // exec ('grep -i '.$modem_mac." /var/log/messages | grep CPE | tail -n 1  | tac", $str);

        if ($str == []) {
            $mac = $modem_mac;
            $mac[0] = ' ';
            $mac = trim($mac);
            $mac_bug = true;
            // exec ('grep -i '.$mac." /var/log/messages | grep CPE | tail -n 1 | tac", $str);
            $str = $this->_get_syslog_entries($mac, '| grep CPE | tail -n 1 | tac');

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
            $log = $this->_get_syslog_entries($cpe_mac, '| tail -n 20 | tac');
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
        $lease['text'] = $this->searchLease("mta-$mta->id");
        $lease = $this->validate_lease($lease, $type);

        // configfile
        $configfile = $this->getConfigfileText("/tftpboot/mta/$mta->hostname");

        // log
        $ip = gethostbyname($mta->hostname);
        $ip = $mta->hostname == $ip ? null : $ip;
        $mac = strtolower($mta->mac);
        $search = $ip ? "$mac|$mta->hostname|$ip " : "$mac|$mta->hostname";
        $log = $this->_get_syslog_entries($search, '| tail -n 25  | tac');
        // exec ('grep -i "'.$mta->mac.'\|'.$mta->hostname.'" /var/log/messages | grep -v "DISCOVER from" | tail -n 20  | tac', $log);

        end:
        $tabs = $this->analysisPages($id);

        $view_header = 'Provmon-MTA';

        return View::make('provmon::cpe_analysis', $this->compact_prep_view(compact('modem', 'ping', 'type', 'tabs', 'lease', 'log', 'dash', 'realtime', 'configfile', 'view_var', 'view_header')));
    }

    /**
     * Returns view of netgw analysis page
     */
    public function netgw_analysis($id)
    {
        $ping = $lease = $log = $dash = $realtime = $monitoring = $type = $flood_ping = null;
        $netgw = NetGw::find($id);
        $ip = $netgw->ip;
        $view_var = $netgw; // for top header

        // Ping: Send 5 request's at once with max timeout of 1 second
        exec('sudo ping -c5 -i0 -w1 '.$ip, $ping);
        if (count(array_keys($ping)) <= 9) {
            $ping = null;
        }

        // Realtime Measure
        if (count($ping) == 10) { // only fetch realtime values if all pings are successfull
            $realtime['measure'] = $this->realtimeNetgw($netgw, $netgw->get_ro_community());
            $realtime['forecast'] = '';
        }

        $host_id = $this->monitoring_get_host_id($netgw);

        $tabs = [
            ['name' => 'Edit', 'route' => 'NetGw.edit', 'link' => $id],
            ['name' => 'Analysis', 'route' => 'ProvMon.netgw', 'link' => $id],
        ];

        $view_header = 'Provmon-NetGw';

        return View::make('provmon::netgw_analysis', $this->compact_prep_view(compact('ping', 'tabs', 'lease', 'log', 'dash', 'realtime', 'host_id', 'view_var', 'view_header')));
    }

    /**
     * Proves if the last found lease is actually valid or has already expired
     */
    private function validate_lease($lease, $type = null)
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
     * @param ip:    IP address of modem
     * @param com:   SNMP RO community
     * @return: array[section][Fieldname][Values]
     */
    private function realtime($ip, $com)
    {
        // Copy from SnmpController
        $this->snmp_def_mode();

        try {
            // First: get docsis mode, some MIBs depend on special DOCSIS version so we better check it first
            $docsis = snmpget($ip, $com, '1.3.6.1.2.1.10.127.1.1.5.0'); // 1: D1.0, 2: D1.1, 3: D2.0, 4: D3.0
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'php_network_getaddresses: getaddrinfo failed: Name or service not known') !== false ||
                strpos($e->getMessage(), 'No response from') !== false) {
                return ['SNMP-Server not reachable' => ['' => [0 => '']]];
            } elseif (strpos($e->getMessage(), 'Error in packet at') !== false) {
                $docsis = 1;
            }
        }

        $netgw = Modem::get_netgw($ip);
        $sys = [];

        $sys['SysDescr'] = [snmpget($ip, $com, '.1.3.6.1.2.1.1.1.0')];
        $sys['Firmware'] = [snmpget($ip, $com, '.1.3.6.1.2.1.69.1.3.5.0')];
        $sys['Uptime'] = [$this->_secondsToTime(snmpget($ip, $com, '.1.3.6.1.2.1.1.3.0') / 100)];
        $sys['Status Code'] = [snmpget($ip, $com, '.1.3.6.1.2.1.10.127.1.2.2.1.2.2')];
        $sys['DOCSIS'] = [$this->_docsis_mode($docsis)]; // TODO: translate to DOCSIS version
        $sys['NetGw'] = [$netgw->hostname];
        $ds['Frequency MHz'] = ArrayHelper::ArrayDiv(snmpwalk($ip, $com, '.1.3.6.1.2.1.10.127.1.1.1.1.2'), 1000000);
        $us['Frequency MHz'] = ArrayHelper::ArrayDiv(snmpwalk($ip, $com, '.1.3.6.1.2.1.10.127.1.1.2.1.2'), 1000000);
        //$us['Modulation'] = $this->_docsis_modulation($netgw->get_us_mods(snmpwalk($ip, $com, '1.3.6.1.2.1.10.127.1.1.2.1.1')), 'us');

        // Downstream
        $ds['Modulation'] = $this->_docsis_modulation(snmpwalk($ip, $com, '.1.3.6.1.2.1.10.127.1.1.1.1.4'), 'ds');
        $ds['Power dBmV'] = ArrayHelper::ArrayDiv(snmpwalk($ip, $com, '.1.3.6.1.2.1.10.127.1.1.1.1.6'));
        try {
            $ds['MER dB'] = ArrayHelper::ArrayDiv(snmpwalk($ip, $com, '.1.3.6.1.4.1.4491.2.1.20.1.24.1.1'));
        } catch (\Exception $e) {
            $ds['MER dB'] = ArrayHelper::ArrayDiv(snmpwalk($ip, $com, '.1.3.6.1.2.1.10.127.1.1.4.1.5'));
        }
        $ds['Microreflection -dBc'] = snmpwalk($ip, $com, '.1.3.6.1.2.1.10.127.1.1.4.1.6');

        // Upstream
        $us['Width MHz'] = ArrayHelper::ArrayDiv(snmpwalk($ip, $com, '.1.3.6.1.2.1.10.127.1.1.2.1.3'), 1000000);
        if ($docsis >= 4) {
            $us['Power dBmV'] = ArrayHelper::ArrayDiv(snmpwalk($ip, $com, '.1.3.6.1.4.1.4491.2.1.20.1.2.1.1'));
        } else {
            $us['Power dBmV'] = ArrayHelper::ArrayDiv(snmpwalk($ip, $com, '.1.3.6.1.2.1.10.127.1.2.2.1.3.2'));
        }

        $snrs = $netgw->get_us_snr($ip);
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
            foreach (snmpwalk($ip, $com, '1.3.6.1.4.1.4491.2.1.20.1.2.1.9') as $key => $val) {
                if ($val != 4) {
                    foreach ($us as $entry => $arr) {
                        unset($us[$entry][$key]);
                    }
                }
            }
        }

        // colorize downstream
        foreach (['Power dBmV', 'MER dB', 'Microreflection -dBc'] as $item) {
            foreach ($ds[$item] as $key => &$value) {
                $value = [$value, BaseViewController::getQualityColor('ds', $ds['Modulation'][$key], $item, $value, true)];
            }
        }

        // colorize upstream
        foreach (['Power dBmV', 'SNR dB'] as $item) {
            foreach ($us[$item] as $key => &$value) {
                $value = [$value, BaseViewController::getQualityColor('us', 'QAM64', $item, $value, true)];
            }
        }

        // Put Sections together
        $ret['System'] = $sys;
        $keys = array_keys(reset($ds));
        $ret['DT_Downstream'] = array_merge(['#' => array_combine($keys, $keys)], $ds);
        $keys = array_keys(reset($us));
        $ret['DT_Upstream'] = array_merge(['#' => array_combine($keys, $keys)], $us);

        return $ret;
    }

    /**
     * Fetch realtime values via GenieACS
     *
     * @param modem: modem object
     * @param refresh: bool refresh values from device instead of using cached ones
     * @return mixed
     *
     * @author Ole Ernst
     */
    public function realtimeTR069($modem, $refresh)
    {
        $mon = $modem->configfile->getMonitoringConfig();
        if (! $mon) {
            return [];
        }

        if ($refresh) {
            $request = ['name' => 'refreshObject'];
            $request['objectName'] = \Illuminate\Support\Arr::flatten($mon);

            $devId = rawurlencode($modem->getGenieAcsModel('_id'));
            Modem::callGenieAcsApi("devices/$devId/tasks?timeout=3000&connection_request", 'POST', json_encode($request));
        }

        foreach ($mon as $category => &$values) {
            $values = array_map(function ($value) use ($modem) {
                $model = $modem->getGenieAcsModel($value);

                return isset($model->_value) ? [$model->_value] : [];
            }, $values);
        }

        return $mon;
    }

    /**
     * Fetch realtime values via FreeRADIUS database
     *
     * @param modem: modem object
     * @return array[section][Fieldname][Values]
     *
     * @author Ole Ernst
     */
    private function realtimePPP($modem)
    {
        $ret = [];

        if (! $modem->isPPP()) {
            return $ret;
        }

        // Current
        $cur = $modem->radacct()->latest('radacctid')->first();
        if ($cur && ! $cur->acctstoptime) {
            $ret['DT_Current Session']['Start'] = [$cur->acctstarttime];
            $ret['DT_Current Session']['Last Update'] = [$cur->acctupdatetime];
            $ret['DT_Current Session']['BRAS IP'] = [$cur->nasipaddress];
        }

        // Sessions
        $sessionItems = [
            ['acctstarttime', 'Start', null],
            ['acctstoptime', 'Stop', null],
            ['acctsessiontime', 'Duration', function ($item) {
                return \Carbon\CarbonInterval::seconds($item)->cascade()->format('%dd %Hh %Im %Ss');
            }],
            ['acctterminatecause', 'Stop Info', null],
            ['acctinputoctets', 'In', function ($item) {
                return humanFilesize($item);
            }],
            ['acctoutputoctets', 'Out', function ($item) {
                return humanFilesize($item);
            }],
            ['nasportid', 'Port', null],
            ['callingstationid', 'MAC', null],
            ['framedipaddress', 'IP', null],
        ];
        $sessions = $modem->radacct()
            ->latest('radacctid')
            ->limit(10)
            ->get(array_map(function ($a) {
                return $a[0];
            }, $sessionItems));

        foreach ($sessionItems as $item) {
            $values = $sessions->pluck($item[0])->toArray();
            $ret['DT_Last Sessions'][$item[1]] = $item[2] ? array_map($item[2], $values) : $values;
        }

        // Replies
        $replyItems = [
            ['attribute', 'Attribute'],
            ['op', 'Operand'],
            ['value', 'Value'],
        ];
        $replies = $modem->radusergroups()
            ->join('radgroupreply', 'radusergroup.groupname', 'radgroupreply.groupname')
            ->get(array_map(function ($a) {
                return $a[0];
            }, $replyItems));

        foreach ($replyItems as $item) {
            $ret['DT_Replies'][$item[1]] = $replies->pluck($item[0])->toArray();
        }
        // add sequence number for proper sorting
        $ret['DT_Replies'] = array_merge(['#' => array_keys(reset($ret['DT_Replies']))], $ret['DT_Replies']);

        // Authentications
        $authItems = [
            ['authdate', 'Date'],
            ['reply', 'Reply'],
        ];
        $auths = $modem->radpostauth()
            ->latest('id')
            ->limit(10)
            ->get(array_map(function ($a) {
                return $a[0];
            }, $authItems));

        foreach ($authItems as $item) {
            $ret['DT_Authentications'][$item[1]] = $auths->pluck($item[0])->toArray();
        }

        return $ret;
    }

    /**
     * The NETGW Realtime Measurement Function
     * Fetches all realtime values from NETGW with SNMP
     *
     * @param netgw: NETGW object
     * @param com: SNMP RO community
     * @param ctrl: shall the RX power be controlled?
     * @return: array[section][Fieldname][Values]
     */
    public function realtimeNetgw($netgw, $com)
    {
        // Copy from SnmpController
        $this->snmp_def_mode();
        try {
            // First: get docsis mode, some MIBs depend on special DOCSIS version so we better check it first
            $docsis = snmpget($netgw->ip, $com, '1.3.6.1.2.1.10.127.1.1.5.0'); // 1: D1.0, 2: D1.1, 3: D2.0, 4: D3.0
        } catch (\Exception $e) {
            if (((strpos($e->getMessage(), 'php_network_getaddresses: getaddrinfo failed: Name or service not known') !== false) || (strpos($e->getMessage(), 'No response from') !== false))) {
                return ['SNMP-Server not reachable' => ['' => [0 => '']]];
            }
            if (strpos($e->getMessage(), 'noSuchName') !== false) {
                $docsis = 0;
            }
        }

        // System
        $sys['SysDescr'] = [snmpget($netgw->ip, $com, '.1.3.6.1.2.1.1.1.0')];
        $sys['Uptime'] = [$this->_secondsToTime(snmpget($netgw->ip, $com, '.1.3.6.1.2.1.1.3.0') / 100)];
        if ($netgw->type != 'cmts') {
            return ['System' => $sys];
        }

        $sys['DOCSIS'] = [$this->_docsis_mode($docsis)];

        $freq = snmprealwalk($netgw->ip, $com, '.1.3.6.1.2.1.10.127.1.1.2.1.2');
        try {
            $desc = snmprealwalk($netgw->ip, $com, '.1.3.6.1.2.1.31.1.1.1.18');
        } catch (\Exception $e) {
            $desc = ['n/a'];
        }
        $snr = snmprealwalk($netgw->ip, $com, '.1.3.6.1.2.1.10.127.1.1.4.1.5');
        try {
            $util = snmprealwalk($netgw->ip, $com, '.1.3.6.1.2.1.10.127.1.3.9.1.3');
        } catch (\Exception $e) {
        }
        try {
            $rx = snmprealwalk($netgw->ip, $com, '.1.3.6.1.4.1.4491.2.1.20.1.25.1.2');
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
            if ($netgw->company == 'Casa' && $us['SNR dB'][$idx] == 0) {
                foreach (array_keys($us) as $entry) {
                    unset($us[$entry][$idx]);
                }
            }
        }

        // colorize upstream
        foreach (['SNR dB', 'Avg Utilization %', 'Rx Power dBmV'] as $item) {
            foreach ($us[$item] as $key => &$value) {
                $value = [$value, BaseViewController::getQualityColor('us', 'QAM64', $item, $value, true)];
            }
        }

        $ret['System'] = $sys;
        $keys = array_keys(reset($us));
        $ret['Upstream'] = array_merge(['#' => array_combine($keys, $keys)], $us);

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
     * Returns the lease entry that contains the search parameter
     *
     * TODO: make a seperate class for dhcpd
     * lease stuff (search, replace, ..)
     *
     * @return array	of lease entry strings
     */
    private function searchLease(string $search): array
    {
        $ret = [];

        // parse dhcpd.lease file
        $file = file_get_contents('/var/lib/dhcpd/dhcpd.leases');
        // start each lease with a line that begins with "lease" and end with a line that begins with "{"
        preg_match_all('/^lease(.*?)(^})/ms', $file, $section);

        // fetch all lines matching hw mac
        foreach (array_unique($section[0]) as $s) {
            if (strpos($s, $search)) {
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
        // Search parent NETGW for type cluster
        if ($netelem->netelementtype_id == 2 && ! $netelem->ip && $netgw = $netelem->get_parent_netgw()) {
            $ip = $netgw->ip;
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
        $graph_template_id = self::monitoring_get_graph_template_id("$vendor NETGW Overview");
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
        $from = \Request::get('from');
        $to = \Request::get('to');

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
     * Note: 1 = Net, 2 = Cluster, 3 = NetGw, 4 = Amplifier, 5 = Node, 6 = Data, 7 = UPS
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

        if (in_array($type, [1, 2, 8])) {
            $sqlCol = $type == 8 ? 'parent_id' : $model->netelementtype->name;

            array_push($tabs,
                ['name' => 'Entity Diagram', 'route' => 'TreeErd.show', 'link' => [$sqlCol, $model->id]],
                ['name' => 'Topography', 'route' => 'TreeTopo.show', 'link' => [$sqlCol, $model->id]]
            );
        }

        if (! in_array($type, [1, 8, 9])) {
            array_push($tabs, ['name' => 'Controlling', 'route' => 'NetElement.controlling_edit', 'link' => [$model->id, 0, 0]]);
        }

        if ($type == 9) {
            array_push($tabs, ['name' => 'Controlling', 'route' => 'NetElement.tapControlling', 'link' => [$model->id]]);
        }

        if ($type == 4 || $type == 5 && \Bouncer::can('view_analysis_pages_of', Modem::class)) {
            //create Analyses tab (for ORA/VGP) if IP address is no valid IP
            array_push($tabs, ['name' => 'Analyses', 'route' => 'ProvMon.index', 'link' => $provmon->createAnalysisTab($model->ip)]);
        }

        if (! in_array($type, [4, 5, 8, 9])) {
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
     * @author Roy Schneider, Nino Ryschawy
     * @param Modules\ProvBase\Entities\Modem
     * @return JSON response
     */
    public function getSpectrumData($id)
    {
        $provmon = \Modules\ProvMon\Entities\ProvMon::first();
        $provbase = ProvBase::first();
        $modem = Modem::find($id);
        $hostname = $modem->hostname;
        $roCommunity = $provbase->ro_community;
        $rwCommunity = $provbase->rw_community;
        $expressions = [];

        // Configure and start spectrum measurement only if not yet done
        $ret = snmp2_get($hostname, $roCommunity, '.1.3.6.1.4.1.4491.2.1.20.1.34.1.0');

        if ($ret != '1') {
            Log::debug("Set Pwr Spectrum measurement values for modem $id");
            // NOTE: It's actually possible that these OIDs can be set even if the modem doesn't support spectrum measurement
            // NOTE: NO RESPONSE leads to exception and message that spectrum can not be created for this modem, but it's currently [Jan 2020]
            // quite common that the SNMP server stops to respond for some time when spectrum measurement is done
            $r1 = snmp2_set($hostname, $rwCommunity, '.1.3.6.1.4.1.4491.2.1.20.1.34.3.0', 'u', $provmon->start_frequency * 1e6);
            $r2 = snmp2_set($hostname, $rwCommunity, '.1.3.6.1.4.1.4491.2.1.20.1.34.4.0', 'u', $provmon->stop_frequency * 1e6);
            $r3 = snmp2_set($hostname, $rwCommunity, '.1.3.6.1.4.1.4491.2.1.20.1.34.5.0', 'u', $provmon->span * 1e6);

            if (! $r1 || ! $r2 || ! $r3) {
                Log::error("Set Pwr Spectrum measurement values for modem $id failed");
            }

            // Enable docsIf3CmSpectrumAnalysisCtrlCmd - Start measurement - By default the measurement is stopped after 300 seconds
            // Time to stop measurement can be set in seconds with .1.3.6.1.4.1.4491.2.1.20.1.34.2.0
            $r4 = snmp2_set($hostname, $rwCommunity, '.1.3.6.1.4.1.4491.2.1.20.1.34.1.0', 'i', 1);

            if (! $r4) {
                Log::error("Start Pwr Spectrum measurement for modem $id failed");
            }
        } else {
            Log::debug("Pwr Spectrum measurement of modem $id is already running");
        }

        Log::info("Get Pwr Spectrum measurement values for modem $id");

        // after enabling docsIf3CmSpectrumAnalysisCtrlCmd it may take a few seconds to start the snmpwalḱ (error: End of MIB)
        $time = 0;
        while ($time <= 20) {
            $time += 2;

            // Unset makes php really get new value from exec command - seems otherwise to be cached and not really executing the command
            unset($expressions);
            exec("snmpbulkwalk -v2c -c$roCommunity $hostname .1.3.6.1.4.1.4491.2.1.20.1.35.1.3", $expressions);

            // END OF MIB means the spectrum is not yet fully created
            if ($expressions && strtolower($expressions[0]) == 'end of mib') {
                Log::debug("Pwr Spectrum measurement of modem $id after $time seconds not ready: end of mib");

                sleep(2);
                continue;
            }

            break;

            // NOTE: These functions should be used but are currently (Jan 2020) way less reliable
            // try {
            //     $expressions = snmp2_real_walk($hostname, $roCommunity, '.1.3.6.1.4.1.4491.2.1.20.1.35.1.3', 100, 0);
            //     $expressions = snmprealwalk($hostname, $roCommunity, '.1.3.6.1.4.1.4491.2.1.20.1.35.1.3', 100, 0);

            //     break;
            // } catch (\Exception $e) {
            //     $time++;
            //     Log::debug("Pwr Spectrum measurement of modem $id after $time seconds not ready (exception): ".$e->getMessage());
            //     sleep(2);
            // }
        }

        if (! $this->snmpReturnValueValid($expressions, $modem)) {
            return;
        }

        if (strtolower($expressions[0]) == 'end of mib') {
            return response()->json('processing');
        }

        // Log::debug("Pwr Spectrum measurement of modem $id first return value: ".$expressions[0]);

        // Returned value example: SNMPv2-SMI::enterprises.4491.2.1.20.1.35.1.3.985500000 = INTEGER: -361
        // foreach ($expressions as $oid => $level) {
        foreach ($expressions as $entry) {
            // preg_match('/[0-9]{7,} =/', $oid, $frequency);
            preg_match('/[0-9]{7,} =/', $entry, $frequency);
            if (! $frequency) {
                continue;
            }

            $frequency = str_replace(' =', '', $frequency[0]);
            $data['span'][] = $frequency / 1000000;
            $data['amplitudes'][] = intval(substr($entry, strpos($entry, 'INTEGER:') + 8)) / 10;
            // $data['amplitudes'][] = intval($level) / 10;
        }

        return response()->json($data);
    }

    /**
     * Derermine if return value of the SNMP request is valid
     *
     * @return bool
     */
    private function snmpReturnValueValid($array, $modem = null)
    {
        if (! $array) {
            return false;
        }

        // OID is not supported
        if (strpos(strtolower($array[0]), 'no such instance') !== false) {
            Log::error("Pwr spectrum measurement of modem $modem->id failed - returned 'No such instance ...'.");

            return false;
        }

        return true;
    }
}
