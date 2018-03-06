<?php

/* do NOT run this script through a web browser */
if (!isset($_SERVER['argv'][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
	die('<br><strong>This script is only meant to run at the command line.</strong>');
}

$no_http_headers = true;
$_SERVER['SERVER_PORT'] = 8080;

/* display ALL errors */
error_reporting(0);

if (!isset($called_by_script_server)) {
	include_once(dirname(__FILE__) . '/../include/global.php');

	print call_user_func('ss_docsis');
}

require '/var/www/nmsprime/bootstrap/autoload.php';
$app = require_once '/var/www/nmsprime/bootstrap/app.php';

// https://gist.github.com/frzsombor/ddd0e11f93885060ef35#gistcomment-1455777
$app->make('Illuminate\Contracts\Http\Kernel')->handle(Illuminate\Http\Request::capture());
$GLOBALS['controller'] = $app->make('Modules\ProvMon\Http\Controllers\ProvMonController');

function ss_docsis_isset_return(&$mixed)
{
	return (isset($mixed)) ? $mixed : null;
}

function ss_docsis_avg($a)
{
	if(empty($a))
		return null;

	return array_sum($a) / count($a);
}

function ss_docsis_stats($a, $name)
{
	if(empty($a))
		return [
			'min'.$name => null,
			'avg'.$name => null,
			'max'.$name => null,
		];

	return [
		'min'.$name => min($a),
		'avg'.$name => array_sum($a) / count($a),
		'max'.$name => max($a),
	];
}

function ss_docsis_snmp_sum($host, $com, $oid, $name)
{
	try {
		$ret = snmp2_walk($host, $com, $oid);
	} catch (\Exception $e) {
		try {
			$ret = snmpwalk($host, $com, $oid);
		} catch (\Exception $e) {
			return [$name => null];
		}
	}

	return [$name => array_sum($ret)];
}

function ss_docsis($hostname, $snmp_community)
{
	$file = "/usr/share/cacti/rra/$hostname.json";
	$rates = ['+8 hours', '+4 hours', '+10 minutes'];
	$val = app()->call([$GLOBALS['controller'], 'realtime'], [$hostname, $snmp_community, gethostbyname($hostname), true]);

	$preq = json_decode(file_exists($file) ? file_get_contents($file) : '{"rate":0}', true);
	if(!isset($preq['next']) || time() > $preq['next']) {
		snmp_set_quick_print(TRUE);
		snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
		try {
			$tmp = snmp2_walk($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.2.2.1.17.2');
		} catch (\Exception $e) {
			#do nothing
		}
		$preq['data'] = isset($tmp) ? preg_replace("/[^A-Fa-f0-9]/", '', reset($tmp)) : '';
		$preq['width'] = ss_docsis_avg(ss_docsis_isset_return($val['Upstream']['Width MHz'])) * 1000000;
		$preq['next'] = strtotime($rates[$preq['rate']]);
		file_put_contents($file, json_encode($preq));
	}

	$arr = array_merge(
		ss_docsis_stats(ss_docsis_isset_return($val['Downstream']['Power dBmV']), 'DsPow'),
		ss_docsis_stats(ss_docsis_isset_return($val['Downstream']['Microreflection -dBc']), 'MuRef'),
		ss_docsis_stats(ss_docsis_isset_return($val['Downstream']['MER dB']), 'DsSNR'),
		ss_docsis_stats(ss_docsis_isset_return($val['Upstream']['Power dBmV']), 'UsPow'),
		ss_docsis_stats(ss_docsis_isset_return($val['Upstream']['SNR dB']), 'UsSNR'),
		ss_docsis_snmp_sum($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.2.2.1.12', 'T3Timeout'),
		ss_docsis_snmp_sum($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.2.2.1.13', 'T4Timeout'),
		ss_docsis_snmp_sum($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.1.4.1.3', 'Corrected'),
		ss_docsis_snmp_sum($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.1.4.1.4', 'Uncorrectable')
	);

	$result = '';
	foreach ($arr as $key => $value)
		$result = is_numeric($value) ? ($result . $key . ':' . $value . ' ') : ($result . $key . ':NaN ');

	return trim($result);
}
