<?php

namespace Modules\ProvMon\Http\Controllers;


use View;
use Acme\php\ArrayHelper;

use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\Endpoint;
use Modules\ProvBase\Entities\Configfile;
use Modules\ProvBase\Entities\Qos;
use Modules\ProvBase\Entities\ProvBase;

/*
 * This is the Basic Stuff for Modem Analyses Page
 * Note: this class does not have a corresponding Model
 *       it fetches all required stuff from Modem or Server
 *
 * @author: Torsten Schmidt
 */
class ProvMonController extends \BaseModuleController {

	protected $domain_name = "";

	public function __construct()
	{
		$this->domain_name = ProvBase::first()->domain_name;
	}

	/*
	 * Prepares Sidebar in View
	 */
	public function prep_sidebar($id)
	{
		return [['name' => 'Edit', 'route' => 'Modem.edit', 'link' => [$id]], 
						['name' => 'Analyses', 'route' => 'Provmon.index', 'link' => [$id]],
						['name' => 'CPE-Analysis', 'route' => 'Provmon.cpe', 'link' => [$id]],
						['name' => 'MTA-Analysis', 'route' => 'Provmon.mta', 'link' => [$id]]];
	}

	/**
	 * Main Analyses Function
	 *
	 * @return Response
	 */
	public function analyses($id)
	{
		$modem = Modem::find($id);
		$ping = $lease = $log = $dash = $realtime = $type = null;
		$view_var = $modem; // for top header
		$hostname = $modem->hostname.'.'.$this->domain_name;
		
		// Ping
		exec ('ping -c5 -i0.2 '.$hostname, $ping);
		if (count(array_keys($ping)) <= 9)
			$ping = null;

		// Lease
		$lease = $this->search_lease('hardware ethernet '.$modem->mac);

		// Log
		exec ('egrep "('.$modem->mac.'|'.$hostname.')" /var/log/messages | grep -v CPE | tail -n 20  | sort -r', $log);


		// Realtime Measure
		if (count($ping) == 10) // only fetch realtime values if all pings are successfull
		{
			$realtime['measure']  = $this->realtime($hostname, ProvBase::first()->ro_community);
			$realtime['forecast'] = 'TODO';
		}

		// Monitoring
		$monitoring = $this->monitoring($modem);

		// TODO: Dash / Forecast

		$panel_right = $this->prep_sidebar($id);

		// View
		return View::make('provmon::analyses', $this->compact_prep_view(compact('modem', 'ping', 'panel_right', 'lease', 'log', 'dash', 'realtime', 'monitoring', 'view_var')));
	}

	/**
	 * Returns view of cpe analysis page
	 */
	public function cpe_analysis($id)
	{
		$ping = $lease = $log = $dash = $realtime = null;
		$modem = Modem::find($id);
		$view_var = $modem; // for top header
		$type = 'CPE';

		// get MAC of CPE first
		exec ('grep '.$modem->mac." /var/log/messages | grep CPE | tail -n 1  | sort -r", $str);
		if (isset($str[0]))
			preg_match_all('/(?:[0-9a-fA-F]{2}[:]?){6}/', $str[0], $cpe_mac);

		// Log
		if (isset($cpe_mac[0][0]))
			exec ('grep '.$cpe_mac[0][0].' /var/log/messages | grep -v "DISCOVER from" | tail -n 20  | sort -r', $log);

		// Lease
		$lease['text'] = $this->search_lease('billing subclass', $modem->mac);
		$lease = $this->validate_lease($lease, $type);

		// Ping
		if (isset($lease['text'][0]))
		{
			// get ip first
			preg_match_all('/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/', $lease['text'][0], $ip);
			if (isset($ip[0][0]))
			{
				$ip = $ip[0][0];
				exec ('ping -c3 -i0.2 '.$ip, $ping);
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
	 */
	public function mta_analysis($id)
	{
		$ping = $lease = $log = $dash = $realtime = null;
		$modem = Modem::find($id);
		$view_var = $modem; // for top header
		$type = 'MTA';

		$mtas = $modem->mtas;		// Note: we should use one-to-one relationship here
		if (isset($mtas[0]))
			$mta = $mtas[0];
		else
			goto end;

		// Ping
		exec ('ping -c3 -i0.2 '.$mta->hostname, $ping);
		if (count(array_keys($ping)) <= 7)
			$ping = null;

		// lease
		$lease['text'] = $this->search_lease("mta-".$mta->id);
		$lease = $this->validate_lease($lease, $type);

		// log
		exec ('grep "'.$mta->mac.'" /var/log/messages | grep -v "DISCOVER from" | tail -n 20  | sort -r', $log);


end:
		$panel_right = $this->prep_sidebar($id);
		
		return View::make('provmon::cpe_analysis', $this->compact_prep_view(compact('modem', 'ping', 'type', 'panel_right', 'lease', 'log', 'dash', 'realtime', 'view_var')));
	}


	/**
	 * Proves if the last found lease is actually valid or has already expired
	 */
	private function validate_lease($lease, $type)
	{
		if ($lease['text'])
		{
			// calculate endtime
			preg_match ('/ends [1-7] (.*?);/', $lease['text'][0], $endtime);
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
			$lease['forecast'] = 'No valid lease found';
		}

		return $lease;
	}


	/* 
	 * Local Helper to Convert the sysUpTime from Seconds to human readable format
	 * See: http://stackoverflow.com/questions/8273804/convert-seconds-into-days-hours-minutes-and-seconds
	 *
	 * TODO: move somewhere else
	 */
	private function _d_h_m_s__array($seconds, $format = 'string')
	{
	    $ret = array();

	    $divs = array(86400, 3600, 60, 1);

	    for ($d = 0; $d < 4; $d++)
	    {
	        $q = $seconds / $divs[$d];
	        $r = $seconds % $divs[$d];
	        $ret[substr('dhms', $d, 1)] = round($q);

	        $seconds = $r;
	    }

	    if ($format == 'string')
	    	return $ret['d'].' Days '.$ret['h'].' Hours '.$ret['m'].' Min '.$ret['s'].' Sec';

	    return $ret; // Array Format
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
	 * convert docsis ds modulation from int to human readable string
	 */
	private function _docsis_ds_modulation ($a)
	{
		$r = [];
		foreach ($a as $m) 
		{
			switch ($m) 
			{
				case 3: $b = 'QAM64'; break;
				case 4: $b = 'QAM256'; break;
				default: $b = null; break;
			}
			array_push ($r, $b);
		}
		return $r;
	}


	/*
	 * The Modem Realtime Measurement Function
	 * Fetches all realtime values from Modem with SNMP
	 *
	 * TODO: add units like (dBmV, MHz, ..)
	 *
	 * @param host: The Modem hostname like cm-xyz.abc.de
	 * @param com:  SNMP RO community
	 * @return: array[section][Fieldname][Values]
	 */
	public function realtime($host, $com)
	{
		// Copy from SnmpController
		$this->snmp_def_mode();

        try
        {
			snmpget($host, $com, '1.3.6.1.2.1.10.127.1.1.5.0');
        }
        catch (\Exception $e)
        {
            if (((strpos($e->getMessage(), "php_network_getaddresses: getaddrinfo failed: Name or service not known") !== false) || (strpos($e->getMessage(), "No response from") !== false)))
            {
            	return ["SNMP-Server not reachable" => ['' => [ 0 => '']]];
            }
        }

        // First: get docsis mode, some MIBs depend on special DOCSIS version so we better check it first
		$docsis = snmpget($host, $com, '1.3.6.1.2.1.10.127.1.1.5.0'); // 1: D1.0, 2: D1.1, 3: D2.0, 4: D3.0

		// System 
		$sys['SysDescr'] = [snmpget($host, $com, '.1.3.6.1.2.1.1.1.0')]; 
		$sys['Firmware'] = [snmpget($host, $com, '.1.3.6.1.2.1.69.1.3.5.0')]; 	  
		$sys['Uptime']   = [$this->_d_h_m_s__array(snmpget($host, $com, '.1.3.6.1.2.1.1.3.0'))]; 
		$sys['DOCSIS']   = [$this->_docsis_mode($docsis)]; // TODO: translate to DOCSIS version

		// Downstream
		$ds['Frequency']  = snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.1.1.2');
		$ds['Modulation'] = $this->_docsis_ds_modulation(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.1.1.4'));
		$ds['Power']      = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.1.1.6'));	
		$ds['MER']        = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.4.1.5'));
		$ds['Microreflection'] = snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.4.1.6.3');
	
		// Upstream
		$us['Frequency']  = snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.2.1.2');
		if ($docsis >= 4) $us['Power'] = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.4.1.4491.2.1.20.1.2.1.1'));
		else              $us['Power'] = ArrayHelper::ArrayDiv(snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.2.2.1.3.2'));
		$us['Width']      = snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.2.1.3'); 
		$us['Modulation Profile'] = snmpwalk($host, $com, '.1.3.6.1.2.1.10.127.1.1.2.1.4'); 

		// Put Sections together
		$ret['System']      = $sys;
		$ret['Downstream']  = $ds;
		$ret['Upstream']    = $us;

		// Return
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
		$string = preg_replace( "/\r|\n/", "", $file );
		preg_match_all('/lease(.*?)}/', $string, $section);

		$ret = array();
		$i   = 0;

		// fetch all lines matching hw mac
		foreach (array_reverse(array_unique($section[0])) as $s)
		{
		    if(strpos($s, $search))
		    {
		    	if (isset($search2))
		    	{
		    		if (!strpos($s, $search2))
		    			continue;
		    	}
		    	
		    	// if ($i == 0)
		    	// 	array_push($ret, "<b>Last Lease:</b>");

		    	// if ($i == 1)
		    	// 	array_push($ret, "<br><br><b>Old Leases:</b>");
				

		    	// push matching results 
		        array_push($ret, str_replace('{', '{<br>', str_replace(';', ';<br>', $s)));

		        // return only the last entry
		        // delete this if we want to see all stuff
		        return $ret;

if (0)
{
		        // TODO: convert string to array and convert return
				$a = explode(';', str_replace ('{', ';', $s));

				if (!isset($ret[$a[0]]))
				$ret[$a[0]] = array();   

				array_push($ret[$a[0]], $a);
}

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
	 * @return: array of related cacti graph id's, false if no entries are found
	 *
	 * @author: Torsten Schmidt
	 */
	public static function monitoring_get_graph_ids($modem)
	{
		// Connect to Cacti DB
		$cacti = \DB::connection('mysql-cacti');

		// Get Cacti Host ID to $modem
		$host  = $cacti->table('host')->where('description', '=', 'cm-'.$modem->id)->get();
		if (!isset($host[0]))
			return false;

		$host_id = $host[0]->id;

		// Get all Graph IDs to Modem
		$graph_ids = [];
		foreach ($cacti->table('graph_local')->where('host_id', '=', $host_id)->get() as $host_graph)
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
	 * @return: the prepared monitoring array for view. Returns false if no diagram exists.
	 *          No other adaptions required. See example in comment above
	 *
	 * @author: Torsten Schmidt
	 */
	public function monitoring ($modem)
	{
		if (!ProvMonController::monitoring_get_graph_ids($modem))
			return false;

		/*
		 * Time Calculation
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
		// Base URL: Should be always available (?)
		$url_base = "https://localhost/cacti/graph_image.php";

		// SSL Array for disabling SSL verification
		$ssl=array(
		    "ssl"=>array(
		        "verify_peer"=>false,
		        "verify_peer_name"=>false,
		    ),
		);

		// TODO: should be auto adapted to screen resolution. Note that we still use width=100% setting
		// in the image view. This could lead to diffuse (unscharf) fonts.
		$graph_width = '700';

		// Fetch Cacti DB for images of $modem and request the Image from Cacti
		foreach (ProvMonController::monitoring_get_graph_ids($modem) as $id)
		{
			$url = "$url_base?local_graph_id=$id&rra_id=0&graph_width=$graph_width&graph_start=$from_t&graph_end=$to_t";

			// Load the image
			//
			// TODO: error handling (for example: no valid login)
			//
			// Consider that we use guest login in Cacti.
			// See: https://numpanglewat.wordpress.com/2009/07/27/how-to-view-cacti-graphics-without-login/
			$img = base64_encode(file_get_contents($url, false, stream_context_create($ssl)));

			if ($img)	// if valid image
				$ret['graphs'][$id] = 'data:application/octet-stream;base64,'.$img;
		}

		if (!isset($ret['graphs']))
			return false;

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
		
		if (!exec ('cat /var/log/messages | egrep "('.$mac.'|'.$hostname.')" | tail -n 100  | sort -r', $ret))
			$out = array ('no logging');

		return View::make('provbase::Modem.log', compact('modem', 'out'));
	}

}
