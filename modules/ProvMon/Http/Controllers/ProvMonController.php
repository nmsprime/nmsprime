<?php

namespace Modules\ProvMon\Http\Controllers;


use View;
use Acme\php\ArrayHelper;

use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\Endpoint;
use Modules\ProvBase\Entities\Configfile;
use Modules\ProvBase\Entities\Qos;
use Modules\ProvBase\Entities\ProvBase;
use Modules\ProvVoip\Entities\ProvVoip;
use Modules\ProvBase\Entities\IpPool;
use Modules\ProvBase\Entities\Cmts;

/*
 * This is the Basic Stuff for Modem Analyses Page
 * Note: this class does not have a corresponding Model
 *       it fetches all required stuff from Modem or Server
 *
 * @author: Torsten Schmidt
 */
class ProvMonController extends \BaseController {

	protected $domain_name = "";
	protected $modem = null;

	public function __construct()
	{
		$this->domain_name = ProvBase::first()->domain_name;
		parent::__construct();
	}

	/*
	 * Prepares Sidebar in View
	 */
	public function prep_sidebar($id)
	{
		$modem = Modem::find($id);
		$this->modem = $modem;

		$a = array(['name' => 'Edit', 'route' => 'Modem.edit', 'link' => [$id]],
						['name' => 'Analyses', 'route' => 'Provmon.index', 'link' => [$id]],
						['name' => 'CPE-Analysis', 'route' => 'Provmon.cpe', 'link' => [$id]],
				);

		if (isset($modem->mtas[0]))
			array_push($a, ['name' => 'MTA-Analysis', 'route' => 'Provmon.mta', 'link' => [$id]]);

		array_push($a, ['name' => 'Logging', 'route' => 'GuiLog.filter', 'link' => ['model_id' => $modem->id, 'model' => 'Modem']]);

		return $a;
	}

	/**
	 * Main Analyses Function
	 *
	 * @return Response
	 */
	public function analyses($id)
	{
		$ping = $lease = $log = $dash = $realtime = $type = $flood_ping = $configfile = null;
		$modem 	  = $this->modem ? $this->modem : Modem::find($id);
		$view_var = $modem; // for top header
		$hostname = $modem->hostname.'.'.$this->domain_name;
		$mac 	  = strtolower($modem->mac);

		// Ping: Send 5 request's at once with max timeout of 1 second
		$ip = gethostbyname($hostname);
		if ($ip != $hostname)
			exec ('sudo ping -c5 -i0 -w1 '.$hostname, $ping);
		else
			$ip = null;

		// Flood Ping
		$flood_ping = $this->flood_ping ($hostname);

		// Lease
		$lease['text'] = $this->search_lease('hardware ethernet '.$mac);
		$lease = $this->validate_lease($lease, $type);

		// Configfile
		$cf_path = "/tftpboot/cm/$modem->hostname.conf";
		$configfile = is_file($cf_path) ? file($cf_path) : ['Error: Missing Configfile!'];

		// Realtime Measure - only fetch realtime values if all pings are successfull
		if (count($ping) == 10)
		{
			// preg_match_all('/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/', $ping[0], $ip);
			$realtime['measure']  = $this->realtime($hostname, ProvBase::first()->ro_community, $ip);
			$realtime['forecast'] = 'TODO';
		}
		else if (count($ping) <= 7)
			$ping = null;

		// Log dhcp (discover, ...), tftp (configfile or firmware)
		$search = $ip ? "$mac|$modem->hostname|$ip" : "$mac|$modem->hostname";
		exec ('egrep -i "('.$search.')" /var/log/messages | grep -v MTA | grep -v CPE | tail -n 20  | tac', $log);

		// Monitoring
		$monitoring = $this->monitoring($modem);

		// TODO: Dash / Forecast

		$panel_right = $this->prep_sidebar($id);

		// View
		return View::make('provmon::analyses', $this->compact_prep_view(compact('modem', 'ping', 'panel_right', 'lease', 'log', 'configfile', 'dash', 'realtime', 'monitoring', 'view_var', 'flood_ping')));
	}


	/*
	 * Flood ping
	 *
	 * NOTE:
	 * --- add /etc/sudoers.d/nms-lara ---
	 * Defaults:apache        !requiretty
	 * apache  ALL=(root) NOPASSWD: /usr/bin/ping
	 * --- /etc/sudoers.d/nms-lara ---
	 *
	 * @param hostname  the host to send a flood ping
	 * @return flood ping exec result
	 */
	public function flood_ping ($hostname)
	{
		if (!\Input::has('flood_ping'))
			return null;

		switch (\Input::get('flood_ping'))
		{
			case "1":
				exec("sudo ping -c500 -f $hostname 2>&1", $fp, $ret);
				break;
			case "2":
				exec("sudo ping -c1000 -s736 -f $hostname 2>&1", $fp, $ret);
				break;
			case "3":
				exec("sudo ping -c2500 -f $hostname 2>&1", $fp, $ret);
				break;
			case "4":
				exec("sudo ping -c2500 -s1472 -f $hostname 2>&1", $fp, $ret);
				break;
		}

		// remove the flood ping line "....." from result
		if ($ret == 0)
			unset ($fp[1]);

		return $fp;
	}


	/**
	 * Returns view of cpe analysis page
	 */
	public function cpe_analysis($id)
	{
		$ping = $lease = $log = $dash = $realtime = null;
		$modem 	  = $this->modem ? $this->modem : Modem::find($id);
		$view_var = $modem; // for top header
		$type 	  = 'CPE';
		$modem_mac = strtolower($modem->mac);

		// Lease
		$lease['text'] = $this->search_lease('billing subclass', $modem_mac);
		$lease = $this->validate_lease($lease, $type);

		// get MAC of CPE first
		exec ('grep -i '.$modem_mac." /var/log/messages | grep CPE | tail -n 1  | tac", $str);
		if ($str == [])
		{
			$mac = $modem_mac;
			$mac[0] = ' ';
			$mac = trim($mac);
			$mac_bug = true;
			exec ('grep -i '.$mac." /var/log/messages | grep CPE | tail -n 1  | tac", $str);

			if (!$str && $lease['text'])
				// get cpe mac addr from lease - first option tolerates small structural changes in dhcpd.leases and assures that it's a mac address
				preg_match_all('/(?:[0-9a-fA-F]{2}[:]?){6}/', substr($lease['text'][0], strpos($lease['text'][0], 'hardware ethernet'), 40), $cpe_mac);
				// $cpe_mac[0][0] = substr($lease['text'][0], strpos($lease['text'][0], 'hardware ethernet') + 18, 17);
		}

		if (isset($str[0]))
		{
			if (isset($mac_bug))
				preg_match_all('/([0-9a-fA-F][:]){1}(?:[0-9a-fA-F]{2}[:]?){5}/', $str[0], $cpe_mac);
			else
				preg_match_all('/(?:[0-9a-fA-F]{2}[:]?){6}/', $str[0], $cpe_mac);
		}

		// Log
		if (isset($cpe_mac[0][0]))
			exec ('grep -i '.$cpe_mac[0][0].' /var/log/messages | grep -v "DISCOVER from" | tail -n 20 | tac', $log);

		// Ping
		if (isset($lease['text'][0]))
		{
			// get ip first
			preg_match_all('/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/', $lease['text'][0], $ip);
			if (isset($ip[0][0]))
			{
				$ip = $ip[0][0];
				exec ('sudo ping -c3 -i0 -w1 '.$ip, $ping);
			}
		}
		if (is_array($ping) && count(array_keys($ping)) <= 7)
		{
			$ping = null;
			if ($lease['state'] == 'green')
				$ping[0] = 'but not reachable from WAN-side due to manufacturing reasons (it can be possible to enable ICMP response via modem configfile)';
		}

		$panel_right = $this->prep_sidebar($id);

		return View::make('provmon::cpe_analysis', $this->compact_prep_view(compact('modem', 'ping', 'type', 'panel_right', 'lease', 'log', 'dash', 'realtime', 'view_var')));
	}

	/**
	 * Returns view of mta analysis page
	 *
	 * Note: This is never called if ProvVoip Module is not active
	 */
	public function mta_analysis($id)
	{
		$ping = $lease = $log = $dash = $realtime = null;
		$modem 	  = $this->modem ? $this->modem : Modem::find($id);
		$view_var = $modem; // for top header
		$type = 'MTA';

		$mtas = $modem->mtas;		// Note: we should use one-to-one relationship here
		if (isset($mtas[0]))
			$mta = $mtas[0];
		else
			goto end;

		// Ping
		$domain   = ProvVoip::first()->mta_domain;
		$hostname = $domain ? $mta->hostname.'.'.$domain : $mta->hostname.'.'.$this->domain_name;

		exec ('sudo ping -c3 -i0 -w1 '.$hostname, $ping);
		if (count(array_keys($ping)) <= 7)
			$ping = null;

		// lease
		$lease['text'] = $this->search_lease("mta-".$mta->id);
		$lease = $this->validate_lease($lease, $type);

		// log
		exec ('grep -i "'.$mta->mac.'\|'.$mta->hostname.'" /var/log/messages | grep -v "DISCOVER from" | tail -n 20  | tac', $log);


end:
		$panel_right = $this->prep_sidebar($id);

		return View::make('provmon::cpe_analysis', $this->compact_prep_view(compact('modem', 'ping', 'type', 'panel_right', 'lease', 'log', 'dash', 'realtime', 'view_var')));
	}


	/**
	 * Returns view of cmts analysis page
	 */
	public function cmts_analysis($id)
	{
		$ping = $lease = $log = $dash = $realtime = $monitoring = $type = $flood_ping = null;
		$modem = $this->modem ? $this->modem : Cmts::find($id);
		$ip = $modem->ip;
		$view_var = $modem; // for top header

		// Ping: Send 5 request's at once with max timeout of 1 second
		exec ('sudo ping -c5 -i0 -w1 '.$ip, $ping);
		if (count(array_keys($ping)) <= 9)
			$ping = null;

		// Realtime Measure
		if (count($ping) == 10) // only fetch realtime values if all pings are successfull
		{
			$realtime['measure']  = $this->realtime_cmts($modem, $modem->get_ro_community());
			$realtime['forecast'] = 'TODO';
		}

		// Monitoring
		$monitoring = $this->monitoring($modem);

		$panel_right =  [
			['name' => 'Edit', 'route' => 'Cmts.edit', 'link' => [$id]],
			['name' => 'Analysis', 'route' => 'Provmon.cmts', 'link' => [$id]]
		];

		return View::make('provmon::cmts_analysis', $this->compact_prep_view(compact('ping', 'panel_right', 'lease', 'log', 'dash', 'realtime', 'monitoring', 'view_var')));
	}

	/**
	 * Proves if the last found lease is actually valid or has already expired
	 */
	private function validate_lease($lease, $type)
	{
		if ($lease['text'] && $lease['text'][0])
		{
			// calculate endtime
			preg_match ('/ends [0-6] (.*?);/', $lease['text'][0], $endtime);
			$et = explode (',', str_replace ([':', '/', ' '], ',', $endtime[1]));
			$endtime = \Carbon\Carbon::create($et[0], $et[1], $et[2], $et[3], $et[4], $et[5], 'UTC');

			// lease calculation
			// take care changing the state - it's used under cpe analysis
			$lease['state']    = 'green';
			$lease['forecast'] = "$type has a valid lease.";
			if ($endtime < \Carbon\Carbon::now())
			{
				$lease['state'] = 'red';
				$lease['forecast'] = 'Lease is out of date';
			}
		}
		else
		{
			$lease['state']    = 'red';
			$lease['forecast'] = trans('messages.modem_lease_error');
		}

		return $lease;
	}


	/*
	 * Local Helper to Convert the sysUpTime from Seconds to human readable format
	 * See: http://stackoverflow.com/questions/8273804/convert-seconds-into-days-hours-minutes-and-seconds
	 *
	 * TODO: move somewhere else
	 */
	private function _secondsToTime($seconds) {
		$seconds = round($seconds);
		$dtF = new \DateTime('@0');
		$dtT = new \DateTime("@$seconds");
		return $dtF->diff($dtT)->format('%a Days %h Hours %i Min %s Sec');
	}


	/*
	 * convert docsis mode from int to human readable string
	 */
	private function _docsis_mode ($i)
	{
		switch ($i)
		{
			case 1: return 'DOCSIS 1.0';
			case 2: return 'DOCSIS 1.1';
			case 3: return 'DOCSIS 2.0';
			case 4: return 'DOCSIS 3.0';

			default: return null;
		}
	}


	/*
	 * convert docsis modulation from int to human readable string
	 */
	private function _docsis_modulation ($a, $direction)
	{
		$r = [];
		foreach ($a as $m)
		{
			if ($direction == 'ds' || $direction == 'DS')
			{
				switch ($m)
				{
					case 3: $b = 'QAM64'; break;
					case 4: $b = 'QAM256'; break;
					default: $b = null; break;
				}
			}
			else
			{
				switch ($m)
				{
					case 0: $b = '0'; break; 		//no docsIfCmtsModulationTable entry
					case 1: $b = 'QPSK'; break;
					case 2: $b = '16QAM'; break;
					case 3: $b = '8QAM'; break;
					case 4: $b = '32QAM'; break;
					case 5: $b = '64QAM'; break;
					case 6: $b = '128QAM'; break;
					default: $b = null; break;
				}
			}
			array_push ($r, $b);
		}
		return $r;
	}




	/*
	 * The Modem Realtime Measurement Function
	 * Fetches all realtime values from Modem with SNMP
	 *
	 * TODO:
	 * - add units like (dBmV, MHz, ..)
	 * - speed-up: use SNMP::get with multiple gets in one request. Test if this speeds up stuff (?)
	 *
	 * @param host: The Modem hostname like cm-xyz.abc.de
	 * @param com:  SNMP RO community
	 * @param ip: 	ip address of modem
	 * @return: array[section][Fieldname][Values]
	 */
	public function realtime($host, $com, $ip)
	{
		// Copy from SnmpController
		$this->snmp_def_mode();

		try
		{
			// First: get docsis mode, some MIBs depend on special DOCSIS version so we better check it first
			$docsis = snmpget($host, $com, '1.3.6.1.2.1.10.127.1.1.5.0'); // 1: D1.0, 2: D1.1, 3: D2.0, 4: D3.0
		}
		catch (\Exception $e)
		{
			if (((strpos($e->getMessage(), "php_network_getaddresses: getaddrinfo failed: Name or service not known") !== false) || (strpos($e->getMessage(), "No response from") !== false)))
			return ["SNMP-Server not reachable" => ['' => [ 0 => '']]];
		}

		$cmts = $this->get_cmts($ip);

		// System
		$sys['SysDescr'] = [snmpget($host, $com, '.1.3.6.1.2.1.1.1.0')];
		$sys['Firmware'] = [snmpget($host, $com, '.1.3.6.1.2.1.69.1.3.5.0')];
		$sys['Uptime']   = [$this->_secondsToTime(snmpget($host, $com, '.1.3.6.1.2.1.1.3.0') / 100)];
		$sys['DOCSIS']   = [$this->_docsis_mode($docsis)]; // TODO: translate to DOCSIS version
		$sys['CMTS'] = [$cmts->hostname];

		// Downstream
		$ds['Frequency MHz']  = snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.1.1.2');		// DOCS-IF-MIB
		foreach($ds['Frequency MHz'] as $i => $freq)
			$ds['Frequency MHz'][$i] /= 1000000;
		$ds['Modulation'] = $this->_docsis_modulation(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.1.1.4'), 'ds');
		$ds['Power dBmV']      = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.1.1.6'));
		$ds['MER dB']        = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.4.1.5'));
		$ds['Microreflection -dBc'] = snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.4.1.6');

		// Upstream
		$us['Frequency MHz']  = snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.2.1.2');
		foreach($us['Frequency MHz'] as $i => $freq)
			$us['Frequency MHz'][$i] /= 1000000;
		if ($docsis >= 4) $us['Power dBmV'] = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.4.1.4491.2.1.20.1.2.1.1'));
		else              $us['Power dBmV'] = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.2.2.1.3.2'));
		$us['Width MHz']      = snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.2.1.3');
		foreach($us['Width MHz'] as $i => $freq)
			$us['Width MHz'][$i] /= 1000000;
		if ($docsis >= 4)
			$us['Modulation Profile'] = $this->_docsis_modulation(snmpwalk($host, $com, '.1.3.6.1.4.1.4491.2.1.20.1.2.1.5'), 'us');
		else
			$us['Modulation Profile'] = $this->_docsis_modulation(snmpwalk($host, $com, '1.3.6.1.2.1.10.127.1.1.2.1.4'), 'us');
		$us['SNR dB'] = $cmts->get_us_snr($ip);

		// remove all inactive channels (no range success)
		$tmp = count($ds['Frequency MHz']);
		foreach ($ds['Frequency MHz'] as $key => $freq)
		{
			if ($ds['Modulation'][$key] == '' && $ds['MER dB'][$key] == 0)
			{
				foreach ($ds as $entry => $arr)
					unset($ds[$entry][$key]);
			}
		}
		$ds['Operational CHs %'] = [count($ds['Frequency MHz']) / $tmp * 100];

		if ($docsis >= 4)
		{
			$us_ranging_status = snmpwalk($host, $com, '1.3.6.1.4.1.4491.2.1.20.1.2.1.9');
			$tmp = count($us['Frequency MHz']);
			foreach ($us_ranging_status as $key => $value)
			{
				if ($value != 4)
				{
					foreach($us as $entry => $arr)
						unset($us[$entry][$key]);
				}
			}
			$us['Operational CHs %'] = [count($us['Frequency MHz']) / $tmp * 100];
		}
		else
			$us['Operational CHs %'] = [100];

		// Put Sections together
		$ret['System']      = $sys;
		$ret['Downstream']  = $ds;
		$ret['Upstream']    = $us;

		// Return
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
		$rx_pwr = array();
		foreach ($us['If Id'] as $i => $idx) {
			// don't control non-functional channels
			if($us['SNR dB'][$i] == 0)
				continue;
			// the reference SNR is 24 dB
			$r = round($us['Rx Power dBmV'][$i] + 24 - $us['SNR dB'][$i]);
			if ($r < 0)
				// minimum actual power is 0 dB
				$r = 0;
			if ($r > 10)
				// maximum actual power is 10 dB
				$r = 10;
			if ($cmts->company == 'Casa')
				snmpset($cmts->ip, $com, ".1.3.6.1.4.1.4491.2.1.20.1.25.1.2.$idx", 'i', 10 * $r);
			if ($cmts->company == 'Cisco')
				snmpset($cmts->ip, $com, ".1.3.6.1.4.1.9.9.116.1.4.1.1.6.$idx", 'i', 10 * $r);

			array_push($rx_pwr, $r);
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
	public function realtime_cmts($cmts, $com, $ctrl=false)
	{
		// Copy from SnmpController
		$this->snmp_def_mode();
		try
		{
			// First: get docsis mode, some MIBs depend on special DOCSIS version so we better check it first
			$docsis = snmpget($cmts->ip, $com, '1.3.6.1.2.1.10.127.1.1.5.0'); // 1: D1.0, 2: D1.1, 3: D2.0, 4: D3.0
		}
		catch (\Exception $e)
		{
			if (((strpos($e->getMessage(), "php_network_getaddresses: getaddrinfo failed: Name or service not known") !== false) || (strpos($e->getMessage(), "No response from") !== false)))
			return ["SNMP-Server not reachable" => ['' => [ 0 => '']]];
		}

		// System
		$sys['SysDescr'] = [snmpget($cmts->ip, $com, '.1.3.6.1.2.1.1.1.0')];
		$sys['Uptime']   = [$this->_secondsToTime(snmpget($cmts->ip, $com, '.1.3.6.1.2.1.1.3.0') / 100)];
		$sys['DOCSIS']   = [$this->_docsis_mode($docsis)];

		$i = 0;
		foreach(snmprealwalk($cmts->ip, $com, '.1.3.6.1.2.1.10.127.1.1.2.1.2') as $id => $freq)
		{
			$id = end((explode('.', $id)));
			$us['Cluster'][$i] = snmpget($cmts->ip, $com, ".1.3.6.1.2.1.31.1.1.1.18.$id");
			$us['If Id'][$i] = $id;
			$us['Frequency MHz'][$i] = $freq / 1000000;
			$i++;
		}

		$us['SNR dB'] = ArrayHelper::ArrayDiv(snmpwalk($cmts->ip, $com, '.1.3.6.1.2.1.10.127.1.1.4.1.5'));

		if ($cmts->company == 'Casa')
			$us['Rx Power dBmV'] = ArrayHelper::ArrayDiv(snmpwalk($cmts->ip, $com, '.1.3.6.1.4.1.4491.2.1.20.1.25.1.2'));
		if ($cmts->company == 'Cisco') {
			$us['Rx Power dBmV'] = ArrayHelper::ArrayDiv(snmpwalk($cmts->ip, $com, '.1.3.6.1.4.1.9.9.116.1.4.1.1.6'));
			$us['Avg Utilization %'] = snmpwalk($cmts->ip, $com, ".1.3.6.1.4.1.9.9.116.1.4.1.1.7");
		}

		// unset unused interfaces, as we don't want to show them on the web gui
		foreach ($us['Frequency MHz'] as $key => $freq)
		{
			if ($us['SNR dB'][$key] == 0)
			{
				foreach ($us as $entry => $arr)
					unset($us[$entry][$key]);
			}
		}

		if($ctrl && isset($us['Rx Power dBmV']))
			$us['Rx Power dBmV'] = $this->_set_new_rx_power($cmts, $cmts->get_rw_community(), $us);

		// unset interface ID, as we don't want to show it on the web gui, we just needed them for setting the RX power
		unset($us['If Id']);

		$ret['System'] = $sys;
		$ret['Upstream'] = $us;
		return $ret;
	}

	/**
	 * Get CMTS for a registered CM
	 *
	 * @param ip:	ip address of cm
	 *
	 * @author Nino Ryschawy
	 */
	static public function get_cmts($ip)
	{
		$validator = new \Acme\Validators\ExtendedValidator;
		foreach(IpPool::all() as $pool)
		{
			$net[0] = $pool->net;
			$net[1] = $pool->netmask;
			if ($validator->validateIpInRange(0, $ip, $net))
			{
				$cmts_id = $pool->cmts_id;
				break;
			}
		}
		if (isset($cmts_id))
			return Cmts::find($cmts_id);
		return null;
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
		snmp_set_quick_print(TRUE);
		snmp_set_oid_numeric_print(TRUE);
		snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
		snmp_set_oid_output_format (SNMP_OID_OUTPUT_NUMERIC);
	}


	/**
	 * Returns the lease entry that contains 1 or 2 strings specified in the function arguments
	 *
	 * TODO: make a seperate class for dhcpd
	 * lease stuff (search, replace, ..)
	 *
	 * @return Response
	 */
	public function search_lease ()
	{
		$search = func_get_arg(0);
		if (func_num_args() == 2)
			$search2 = func_get_arg(1);

		// parse dhcpd.lease file
		$file   = file_get_contents('/var/lib/dhcpd/dhcpd.leases');
		// start each lease with a line that begins with "lease" and end with a line that begins with "{"
		preg_match_all('/^lease(.*?)(^})/ms', $file, $section);

		$ret = array();
		$i   = 0;

		// fetch all lines matching hw mac
		foreach (array_unique($section[0]) as $s)
		{
			if(strpos($s, $search))
			{
				if (isset($search2))
				{
					if (!strpos($s, $search2))
						continue;
				}

				// push matching results
				array_push($ret, preg_replace('/\r|\n/', '<br />', $s));
			}
		}

		// handle multiple lease entries
		// actual strategy: if possible grep active lease, otherwise return all entries
		//                  in reverse ordered format from dhcpd.leases
		if (sizeof($ret) > 1) {
			foreach(preg_grep ('/(.*?)binding state active(.*?)/', $ret) as $str)
				if(preg_match('/starts \d ([^;]+);/', $str, $s))
					$start[] = $s[1];

			if (isset($start)) {
				// return the most recent active lease
				natsort($start);
				end($start);
				return [ $ret[each($start)[0]] ];
			}
		}

		return $ret;
	}



	/*
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

		if(substr($d, -1) == 'y')
			return \Carbon\Carbon::now()->subYear($v)->timestamp;
		if(substr($d, -1) == 'M')
			return \Carbon\Carbon::now()->subMonth($v)->timestamp;
		if(substr($d, -1) == 'd')
			return \Carbon\Carbon::now()->subDay($v)->timestamp;
		if(substr($d, -1) == 'h')
			return \Carbon\Carbon::now()->subHour($v)->timestamp;
		if(substr($d, -1) == 'm')
			return \Carbon\Carbon::now()->subMinute($v)->timestamp;

		return \Carbon\Carbon::now()->timestamp;
	}


	/*
	 * Get the corresponing graph id's for $modem. These id's could
	 * be used in graph_image.php as HTML GET Request with local_graph_id variable
	 * like https://../cacti/graph_image.php?local_graph_id=<ID>
	 *
	 * NOTE: This function requires a valid 'mysql-cacti' array
	 *       in config/database.php
	 *
	 * @param modem: The modem to look for Cacti Graphs
	 * @param graph_template: only show array[] of cacti graph template ids in result
	 * @return: array of related cacti graph id's, false if no entries are found
	 *
	 * @author: Torsten Schmidt
	 */
	public static function monitoring_get_graph_ids($modem, $graph_template = null)
	{
			// Connect to Cacti DB
			$cacti = \DB::connection('mysql-cacti');

			// Get Cacti Host ID to $modem
			$host  = $cacti->table('host')->where('description', '=', $modem->hostname)->get();
			if (!isset($host[0]))
					return false;

			$host_id = $host[0]->id;

			// Graph Template
			$sql_graph_template = '';
			if ($graph_template == null)
					$sql_graph_template = 'graph_template_id > 0';
			else
			{
					$sql_graph_template = 'graph_template_id = 0 ';
					foreach ($graph_template as $_tmpl)
							$sql_graph_template .= ' OR graph_template_id = '.$_tmpl;
			}

			// Get all Graph IDs to Modem
			$graph_ids = [];
			foreach ($cacti->table('graph_local')->whereRaw("host_id = $host_id AND ($sql_graph_template)")->orderBy('graph_template_id')->get() as $host_graph)
					array_push($graph_ids, $host_graph->id);


			return $graph_ids;
	}


	/*
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
	 * @param modem: The modem to look for Cacti Graphs
	 * @param graph_template: only show array[] of cacti graph template ids in result
	 * @return: the prepared monitoring array for view. Returns false if no diagram exists.
	 *          No other adaptions required. See example in comment above
	 *
	 * @author: Torsten Schmidt
	 */
	public function monitoring ($modem, $graph_template = null)
	{
		// Check if Cacti Host RRD files exist
		// This is a speed-up. A cacti HTTP request takes more time.
		if (!glob('/usr/share/cacti/rra/'.$modem->hostname.'*'))
			return false;

		// parse diagram id's from cacti database
		$ids = ProvMonController::monitoring_get_graph_ids($modem, $graph_template);

		// no id's return
		if (!$ids)
			return false;

		/*
		 * Time Span Calculation
		 */
		$from = \Input::get('from');
		$to   = \Input::get('to');

		if(!$from) $from = '-3d';
		if(!$to)   $to   = '0';

		$ret['from']   = $from;
		$ret['to']     = $to;

		// Convert Time
		$from_t = $this->_date ($from);
		$to_t   = $this->_date ($to);

		$ret['from_t'] = $from_t;
		$ret['to_t']   = $to_t;

		/*
		 * Images
		 */
		$url_base = 'https://'.\Request::getHost()."/cacti/graph_image.php?rra_id=0&graph_start=$from_t&graph_end=$to_t";

		// TODO: should be auto adapted to screen resolution. Note that we still use width=100% setting
		// in the image view. This could lead to diffuse (unscharf) fonts.
		$graph_width = '700';

		// Fetch Cacti DB for images of $modem and request the Image from Cacti
		foreach ($ids as $id)
			$ret['graphs'][$id] = $url_base."&graph_width=$graph_width&local_graph_id=$id";

		// No result checking
		if (!isset($ret['graphs']))
			return false;

		// default return
		return $ret;
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
		$mac  = $modem->mac;


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
		$mac      = $modem->mac;

		if (!exec ('cat /var/log/messages | egrep "('.$mac.'|'.$hostname.')" | tail -n 100  | tac', $ret))
			$out = array ('no logging');

		return View::make('provbase::Modem.log', compact('modem', 'out'));
	}

}
