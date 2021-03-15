<?php

/* do NOT run this script through a web browser */
if (! isset($_SERVER['argv'][0]) || isset($_SERVER['REQUEST_METHOD']) || isset($_SERVER['REMOTE_ADDR'])) {
    exit('<br><strong>This script is only meant to run at the command line.</strong>');
}

/* display no errors */
error_reporting(0);

if (! isset($called_by_script_server)) {
    include_once dirname(__FILE__).'/../include/global.php';

    echo call_user_func('ss_docsis');
}

$snrs = [];
$old = time() - 10 * 60;
foreach (glob('/var/www/nmsprime/storage/app/data/provmon/us_snr/*.json') as $file) {
    // ignore files older than 10 minutes, e.g. from a decommissioned cmts
    if ($old > filemtime($file)) {
        continue;
    }
    $snrs = array_merge($snrs, json_decode(file_get_contents($file), true));
}
$GLOBALS['snrs'] = $snrs;

function ss_docsis_ppp($hostname)
{
    $error = ['acctinputoctets' => 'NaN', 'acctoutputoctets' => 'NaN'];
    $conf = file_get_contents('/etc/nmsprime/env/global.env');

    $login = [
        'HOST' => 'localhost',
        'USERNAME' => 'nmsprime',
        'PASSWORD' => '',
        'DATABASE' => 'nmsprime',
    ];

    foreach ($login as $idx => &$value) {
        if (preg_match("/^DB_$idx\s*=\s*(\S*)/m", $conf, $match) == 1) {
            $value = $match[1];
        }
    }

    $conn = new mysqli($login['HOST'], $login['USERNAME'], $login['PASSWORD'], $login['DATABASE']);
    if ($conn->connect_error) {
        return $error;
    }

    $query = 'SELECT acctinputoctets, acctoutputoctets FROM radacct JOIN modem '.
        'ON radacct.username = modem.ppp_username WHERE '.
        "modem.hostname = '$hostname' ORDER BY radacctid DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result->num_rows != 1) {
        return $error;
    }
    $row = $result->fetch_assoc();

    // ignore counter reset on PPP session reset leading to spikes in diagrams
    if ($row == ['acctinputoctets' => 0, 'acctoutputoctets' => 0]) {
        return $error;
    }

    return $row;
}

function ss_docsis($hostname, $snmp_community)
{
    if (substr($hostname, 0, 4) === 'ppp-') {
        $bytes = ss_docsis_ppp(explode('.', $hostname)[0]);

        return 'T3Timeout:NaN T4Timeout:NaN Corrected:NaN Uncorrectable:NaN '.
        'minDsPow:NaN avgDsPow:NaN maxDsPow:NaN minDsSNR:NaN avgDsSNR:NaN '.
        'maxDsSNR:NaN minMuRef:NaN avgMuRef:NaN maxMuRef:NaN minUsPow:NaN '.
        'avgUsPow:NaN maxUsPow:NaN minUsSNR:NaN avgUsSNR:NaN maxUsSNR:NaN '.
        "ifHCInOctets:{$bytes['acctinputoctets']} ".
        "ifHCOutOctets:{$bytes['acctoutputoctets']}";
    }

    $reps = [
        'DsPow' => '.1.3.6.1.2.1.10.127.1.1.1.1.6',
        'DsSNR' => '.1.3.6.1.4.1.4491.2.1.20.1.24.1.1',
        'MuRef' => '.1.3.6.1.2.1.10.127.1.1.4.1.6',
        'UsPow' => '.1.3.6.1.4.1.4491.2.1.20.1.2.1.1',
        'UsSNR' => '',
    ];

    $helper = [
        'DsSNR2' => '.1.3.6.1.2.1.10.127.1.1.4.1.5',
        'UsRng' =>  '.1.3.6.1.4.1.4491.2.1.20.1.2.1.9',
        'UsPow2' => '.1.3.6.1.2.1.10.127.1.2.2.1.3',
        'UsFreq' => '.1.3.6.1.2.1.10.127.1.1.2.1.2',
    ];

    $non_reps = [
        'T3Timeout' =>      '.1.3.6.1.2.1.10.127.1.2.2.1.12',
        'T4Timeout' =>      '.1.3.6.1.2.1.10.127.1.2.2.1.13',
        'Corrected' =>      '.1.3.6.1.2.1.10.127.1.1.4.1.3',
        'Uncorrectable' =>  '.1.3.6.1.2.1.10.127.1.1.4.1.4',
        'ifHCInOctets' =>   '.1.3.6.1.2.1.31.1.1.1.6',
        'ifHCOutOctets' =>  '.1.3.6.1.2.1.31.1.1.1.10',
    ];

    $json = [
        'PreEqu' =>     '.1.3.6.1.2.1.10.127.1.2.2.1.17',
        'UsWidth' =>    '.1.3.6.1.2.1.10.127.1.1.2.1.3',
        'SysDescr' =>   '.1.3.6.1.2.1.1.1',
    ];

    $path = '/run/nmsprime/cacti';
    $filename = "$path/$hostname";
    if (! file_exists($filename)) {
        $result = '';
        foreach ($non_reps as $key => $val) {
            $result .= "$key:NaN ";
        }
        foreach ($reps as $key => $val) {
            foreach (['min', 'avg', 'max'] as $cf) {
                $result .= "{$cf}{$key}:NaN ";
            }
        }

        return trim($result);
    }
    $file = file_get_contents($filename);

    $res = [];
    foreach (array_merge($reps, $helper, $non_reps) as $name => $oid) {
        preg_match_all("/^$oid\.(\d+) (.+)/m", $file, $match);
        $res[$name] = array_combine($match[1], $match[2]);
    }

    // DS SNR: fallback to D2.0 values, if D3.0 ones are not available
    if (! count($res['DsSNR'])) {
        $res['DsSNR'] = $res['DsSNR2'];
    }
    unset($res['DsSNR2']);

    // US Power: fallback to D2.0 values, if D3.0 ones are not available
    if (! count($res['UsPow'])) {
        $res['UsPow'] = $res['UsPow2'];
    } else {
        // in case of D3.0 values check ranging status
        // array_filter should be used, see below
        // however php5.4 does not support ARRAY_FILTER_USE_KEY
        foreach ($res['UsPow'] as $idx => $pow) {
            if ($res['UsRng'][$idx] != 4) {
                unset($res['UsPow'][$idx]);
            }
        }
        /*
        $rng = $res['UsRng'];
        $res['UsPow'] = array_filter($res['UsPow'], function ($key) use ($rng) {
            return $rng[$key] == 4;
        }, ARRAY_FILTER_USE_KEY);
        */
    }
    unset($res['UsPow2'], $res['UsRng']);

    // convert TenthdB to dB
    foreach (['DsPow', 'UsPow', 'DsSNR'] as $name) {
        $res[$name] = array_map(function ($val) {
            return $val / 10;
        }, $res[$name]);
    }

    // evaluate US SNR values from the CMTS
    $ip = gethostbyname($hostname);
    if (array_key_exists($ip, $GLOBALS['snrs'])) {
        $snrs = $GLOBALS['snrs'][$ip];
        foreach ($res['UsFreq'] as $freq) {
            if (! isset($snrs[strval($freq / 1000000)]) || ! $snrs[strval($freq / 1000000)]) {
                continue;
            }
            $res['UsSNR'][] = $snrs[strval($freq / 1000000)];
        }
    }
    unset($res['UsFreq']);

    // calculate sum for non-rep elements
    foreach ($non_reps as $name => $non_rep) {
        $res[$name] = array_sum($res[$name]);
    }

    // calculate min, avg, max for rep elements
    foreach ($reps as $name => $rep) {
        if (count($res[$name])) {
            $res['min'.$name] = min($res[$name]);
            $res['avg'.$name] = array_sum($res[$name]) / count($res[$name]);
            $res['max'.$name] = max($res[$name]);
        }
        unset($res[$name]);
    }

    // pre-equalization-related data
    $json_file = "/usr/share/cacti/rra/$hostname.json";
    $rates = ['+8 hours', '+4 hours', '+10 minutes'];
    $preq = json_decode(file_exists($json_file) ? file_get_contents($json_file) : '{"rate":0}', true);
    if (! isset($preq['next']) || time() > $preq['next']) {
        $res_json = [];
        foreach ($json as $name => $oid) {
            preg_match_all("/^$oid\.(\d+) (.+)/m", $file, $match);
            $res_json[$name] = array_combine($match[1], $match[2]);
        }

        $preq['preEqu'] = $res_json['PreEqu'] ? preg_replace('/[^A-Fa-f0-9]/', '', reset($res_json['PreEqu'])) : '';
        // assume US bandwidth of 3.2MHz, if we can't get the actual value
        $preq['width'] = $res_json['UsWidth'] ? reset($res_json['UsWidth']) : 3200000;
        $preq['next'] = strtotime($rates[$preq['rate']]);
        $preq['descr'] = isset($res_json['SysDescr']) ? reset($res_json['SysDescr']) : 'n/a';

        file_put_contents($json_file, json_encode($preq));
    }

    if (isset($res['avgUsPow']) && is_numeric($res['avgUsPow']) &&
        isset($res['avgUsSNR']) && is_numeric($res['avgUsSNR']) &&
        isset($res['avgDsPow']) && is_numeric($res['avgDsPow']) &&
        isset($res['avgDsSNR']) && is_numeric($res['avgDsSNR']) &&
        preg_match('/^cm-(\d+)\./m', $hostname, $match)) {
        $content = sprintf(
            "UPDATE modem SET us_pwr = %d, us_snr = %d, ds_pwr = %d, ds_snr = %d WHERE id = %d;\n",
            round($res['avgUsPow']),
            round($res['avgUsSNR']),
            round($res['avgDsPow']),
            round($res['avgDsSNR']),
            $match[1]
        );

        file_put_contents("$path/update.sql", $content, FILE_APPEND | LOCK_EX);
    }

    $result = '';
    foreach ($res as $key => $val) {
        $result .= is_numeric($val) ? "$key:$val " : "$key:NaN ";
    }

    return trim($result);
}
