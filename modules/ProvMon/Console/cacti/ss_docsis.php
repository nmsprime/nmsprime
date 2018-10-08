<?php

/* do NOT run this script through a web browser */
if (! isset($_SERVER['argv'][0]) || isset($_SERVER['REQUEST_METHOD']) || isset($_SERVER['REMOTE_ADDR'])) {
    die('<br><strong>This script is only meant to run at the command line.</strong>');
}

/* display no errors */
error_reporting(0);

if (! isset($called_by_script_server)) {
    include_once dirname(__FILE__).'/../include/global.php';

    echo call_user_func('ss_docsis');
}

$snrs = [];
foreach (glob('/var/www/nmsprime/storage/app/data/provmon/us_snr/*.json') as $file) {
    $snrs = array_merge($snrs, json_decode(file_get_contents($file), true));
}
$GLOBALS['snrs'] = $snrs;

function ss_docsis_stats($a, $name)
{
    if (empty($a)) {
        return [
            'min'.$name => null,
            'avg'.$name => null,
            'max'.$name => null,
        ];
    }

    return [
        'min'.$name => min($a),
        'avg'.$name => array_sum($a) / count($a),
        'max'.$name => max($a),
    ];
}

function ss_docsis_snmp($host, $com, $oid, $denom = null)
{
    try {
        $ret = snmp2_walk($host, $com, $oid);
        if ($ret === false) {
            throw new Exception('No value using SNMP v2.');
        }
    } catch (\Exception $e) {
        try {
            $ret = snmpwalk($host, $com, $oid);
        } catch (\Exception $e) {
            return;
        }
    }

    if ($ret === false) {
        $ret = snmpwalk($host, $com, $oid);
    }

    if ($denom) {
        return array_map(function ($val) use ($denom) {
            return $val / $denom;
        }, $ret);
    }

    return $ret;
}

function ss_docsis($hostname, $snmp_community)
{
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    try {
        // 1: D1.0, 2: D1.1, 3: D2.0, 4: D3.0
        $ver = snmpget($hostname, $snmp_community, '1.3.6.1.2.1.10.127.1.1.5.0');
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'Error in packet at') !== false) {
            $ver = 1;
        } else {
            return;
        }
    }

    $ds['Pow'] = ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.1.1.1.6', 10);
    $ds['MuRef'] = ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.1.4.1.6');
    if ($ver >= 4) {
        $ds['SNR'] = ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.4.1.4491.2.1.20.1.24.1.1', 10);
        $us['Pow'] = ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.4.1.4491.2.1.20.1.2.1.1', 10);
    } else {
        $ds['SNR'] = ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.1.4.1.5', 10);
        $us['Pow'] = ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.2.2.1.3.2', 10);
    }
    $us['SNR'] = $GLOBALS['snrs'][gethostbyname($hostname)];

    foreach ($ds['Pow'] as $key => $val) {
        if ($ds['SNR'][$key] == 0) {
            foreach ($ds as $entry => $arr) {
                unset($ds[$entry][$key]);
            }
        }
    }
    if ($ver >= 4) {
        foreach (ss_docsis_snmp($hostname, $snmp_community, '1.3.6.1.4.1.4491.2.1.20.1.2.1.9') as $key => $val) {
            if ($val != 4) {
                foreach ($us as $entry => $arr) {
                    unset($us[$entry][$key]);
                }
            }
        }
    }

    $arr = array_merge(
        ss_docsis_stats($ds['Pow'], 'DsPow'),
        ss_docsis_stats($ds['SNR'], 'DsSNR'),
        ss_docsis_stats($ds['MuRef'], 'MuRef'),
        ss_docsis_stats($us['Pow'], 'UsPow'),
        ss_docsis_stats($us['SNR'], 'UsSNR'),
        ['T3Timeout' => array_sum(ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.2.2.1.12'))],
        ['T4Timeout' => array_sum(ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.2.2.1.13'))],
        ['Corrected' => array_sum(ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.1.4.1.3'))],
        ['Uncorrectable' => array_sum(ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.1.4.1.4'))]
    );

    // pre-equalization-related data
    $file = "/usr/share/cacti/rra/$hostname.json";
    $rates = ['+8 hours', '+4 hours', '+10 minutes'];
    $preq = json_decode(file_exists($file) ? file_get_contents($file) : '{"rate":0}', true);
    if (! isset($preq['next']) || time() > $preq['next']) {
        snmp_set_quick_print(true);
        snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
        $tmp = ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.2.2.1.17.2');
        $preq['data'] = $tmp ? preg_replace('/[^A-Fa-f0-9]/', '', reset($tmp)) : '';
        $tmp = ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.2.1.10.127.1.1.2.1.3');
        // assume as width of 3.2MHz if we can't get the info
        $preq['width'] = $tmp ? reset($tmp) : 3200000;
        $preq['next'] = strtotime($rates[$preq['rate']]);
        $tmp = ss_docsis_snmp($hostname, $snmp_community, '.1.3.6.1.2.1.1.1')[0];
        $preq['descr'] = isset($tmp) ? $tmp : 'n/a';
        file_put_contents($file, json_encode($preq));
    }

    $result = '';
    foreach ($arr as $key => $value) {
        $result = is_numeric($value) ? ($result.$key.':'.$value.' ') : ($result.$key.':NaN ');
    }

    return trim($result);
}
