<?php

namespace Modules\ProvMon\Http\Controllers;

use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\Endpoint;
use Modules\ProvBase\Entities\Configfile;
use Modules\ProvBase\Entities\Qos;

use View;


class ProvMonController extends \BaseModuleController {


	/**
	 * Ping
	 *
	 * @return Response
	 */
	public function ping($id)
	{
		$modem = Modem::find($id);
		$hostname = $modem->hostname;

		// Debug
		$hostname = 'google.de';
		
		// Ping
		if (!exec ('ping -c5 -i0.2 '.$hostname, $ping))
			$ping = array ('Modem is Offline');

		// Lease
		$lease  = $this->search_lease('hardware ethernet '.$modem->mac);
		if ($lease == [])
			$lease = array ('No Lease entry found');

		// Log
		if (!exec ('cat /var/log/messages | egrep "('.$modem->mac.'|'.$modem->hostname.')" | tail -n 100  | sort -r', $log))
			$log = array ('no logging');

		// Prepare Output
		$panel_right = [['name' => 'Edit', 'route' => 'Modem.edit', 'link' => [$id]], 
						['name' => 'Analyses', 'route' => 'Provmon.index', 'link' => [$id]]];


		return View::make('provmon::ping', $this->compact_prep_view(compact('modem', 'ping', 'panel_right', 'lease', 'log')));

	}

	
	/**
	 * Monitoring
	 *
	 * @return Response
	 */
	public function monitoring($id)
	{
		$modem = Modem::find($id);

		return View::make('provbase::Modem.monitoring', compact('modem'));
	}


	/**
	 * Search String in dhcpd.lease file and
	 * return the matching host
	 *
	 * TODO: make a seperate class for dhcpd
	 * lease stuff (search, replace, ..)
	 *
	 * @return Response
	 */
	public function search_lease ($search)
	{
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
		    	/*
		    	if ($i == 0)
		    		array_push($ret, "<b>Last Lease:</b>");

		    	if ($i == 1)
		    		array_push($ret, "<br><br><b>Old Leases:</b>");
				*/

		    	// push matching results 
		        array_push($ret, str_replace('{', '{<br>', str_replace(';', ';<br>', $s)));
		        $i++;

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
