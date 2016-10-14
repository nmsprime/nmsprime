<?php

/* do NOT run this script through a web browser */
if (!isset($_SERVER['argv'][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die('<br><strong>This script is only meant to run at the command line.</strong>');
}

$no_http_headers = true;

/* display ALL errors */
error_reporting(0);

if (!isset($called_by_script_server)) {
	include_once(dirname(__FILE__) . '/../include/global.php');

	print call_user_func('ss_docsis');
}

require '/var/www/lara/bootstrap/autoload.php';
$app = require_once '/var/www/lara/bootstrap/app.php';

// https://gist.github.com/frzsombor/ddd0e11f93885060ef35#gistcomment-1455777
$app->make('Illuminate\Contracts\Http\Kernel')->handle(Illuminate\Http\Request::capture());
$GLOBALS['controller'] = $app->make('Modules\ProvMon\Http\Controllers\ProvMonController');

function ss_docsis_avg($a) {
	return array_sum($a) / count($a);
}

function ss_docsis($hostname, $snmp_community) {
	$val = app()->call([$GLOBALS['controller'], 'realtime'], [$hostname, $snmp_community, gethostbyname($hostname)]);

	$arr = [
		 'minDsPow' => min($val['Downstream']['Power dBmV']),
		 'avgDsPow' => ss_docsis_avg($val['Downstream']['Power dBmV']),
		 'maxDsPow' => max($val['Downstream']['Power dBmV']),
		 'minMuRef' => min($val['Downstream']['Microreflection -dBc']),
		 'avgMuRef' => ss_docsis_avg($val['Downstream']['Microreflection -dBc']),
		 'maxMuRef' => max($val['Downstream']['Microreflection -dBc']),
		 'avgDsSNR' => ss_docsis_avg($val['Downstream']['MER dB']),
		 'minUsPow' => min($val['Upstream']['Power dBmV']),
		 'avgUsPow' => ss_docsis_avg($val['Upstream']['Power dBmV']),
		 'maxUsPow' => max($val['Upstream']['Power dBmV']),
		 'avgUsSNR' => ss_docsis_avg($val['Upstream']['SNR dB']),
		   'upDSch' => $val['Downstream']['Operational CHs %'][0],
		   'upUSch' => $val['Upstream']['Operational CHs %'][0],
		'T3Timeout' => array_sum(snmpwalk($hostname, $snmp_community, '1.3.6.1.2.1.10.127.1.2.2.1.12')),
		'T4Timeout' => array_sum(snmpwalk($hostname, $snmp_community, '1.3.6.1.2.1.10.127.1.2.2.1.13')),
		'Corrected' => array_sum(snmpwalk($hostname, $snmp_community, '1.3.6.1.2.1.10.127.1.1.4.1.3')),
		 'InOctets' => array_sum(snmpwalk($hostname, $snmp_community, '1.3.6.1.2.1.2.2.1.10')),
		'OutOctets' => array_sum(snmpwalk($hostname, $snmp_community, '1.3.6.1.2.1.2.2.1.16'))
	];

	$result = '';
	foreach ($arr as $key => $value) {
		$result = is_numeric($value) ? ($result . $key . ':' . $value . ' ') : ($result . $key . ':NaN ');
	}
	return trim($result);
}
