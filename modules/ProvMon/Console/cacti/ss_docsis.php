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

function ss_docsis($hostname, $snmp_community = null)
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

    $cactiStats = [];
    foreach (array_merge($reps, $helper, $non_reps) as $name => $oid) {
        preg_match_all("/^$oid\.(\d+) (.+)/m", $file, $match);
        $cactiStats[$name] = array_combine($match[1], $match[2]);
    }

    // DS SNR: fallback to D2.0 values, if D3.0 ones are not available
    if (! count($cactiStats['DsSNR'])) {
        $cactiStats['DsSNR'] = $cactiStats['DsSNR2'];
    }
    unset($cactiStats['DsSNR2']);

    // US Power: fallback to D2.0 values, if D3.0 ones are not available
    if (! count($cactiStats['UsPow'])) {
        $cactiStats['UsPow'] = $cactiStats['UsPow2'];
    } else {
        // in case of D3.0 values check ranging status
        // array_filter should be used, see below
        // however php5.4 does not support ARRAY_FILTER_USE_KEY
        foreach ($cactiStats['UsPow'] as $idx => $pow) {
            if ($cactiStats['UsRng'][$idx] != 4) {
                unset($cactiStats['UsPow'][$idx]);
            }
        }
        /*
        $rng = $cactiStats['UsRng'];
        $cactiStats['UsPow'] = array_filter($cactiStats['UsPow'], function ($key) use ($rng) {
            return $rng[$key] == 4;
        }, ARRAY_FILTER_USE_KEY);
        */
    }
    unset($cactiStats['UsPow2'], $cactiStats['UsRng']);

    // convert TenthdB to dB
    foreach (['DsPow', 'UsPow', 'DsSNR'] as $name) {
        $cactiStats[$name] = array_map(function ($val) {
            return $val / 10;
        }, $cactiStats[$name]);
    }

    // evaluate US SNR values from the CMTS
    $ip = gethostbyname($hostname);
    if (array_key_exists($ip, $GLOBALS['snrs'])) {
        $snrs = $GLOBALS['snrs'][$ip];
        foreach ($cactiStats['UsFreq'] as $freq) {
            if (! isset($snrs[strval($freq / 1000000)]) || ! $snrs[strval($freq / 1000000)]) {
                continue;
            }
            $cactiStats['UsSNR'][] = $snrs[strval($freq / 1000000)];
        }
    }
    unset($cactiStats['UsFreq']);

    // calculate sum for non-rep elements
    foreach ($non_reps as $name => $non_rep) {
        $cactiStats[$name] = array_sum($cactiStats[$name]);
    }

    // calculate min, avg, max for rep elements
    foreach (array_keys($reps) as $name) {
        if (count($cactiStats[$name])) {
            $cactiStats['min'.$name] = min($cactiStats[$name]);
            $cactiStats['avg'.$name] = array_sum($cactiStats[$name]) / count($cactiStats[$name]);
            $cactiStats['max'.$name] = max($cactiStats[$name]);
        }
        unset($cactiStats[$name]);
    }

    // pre-equalization-related data
    $basepath = '/usr/share/cacti/rra';
    $deviceFile = "{$basepath}/{$hostname}.json";

    if ($preEquData = ss_docsis_get_pnm($file)) {
        file_put_contents($deviceFile, json_encode($preEquData));
    }

    if (isset($cactiStats['avgUsPow']) && is_numeric($cactiStats['avgUsPow']) &&
        isset($cactiStats['avgUsSNR']) && is_numeric($cactiStats['avgUsSNR']) &&
        isset($cactiStats['avgDsPow']) && is_numeric($cactiStats['avgDsPow']) &&
        isset($cactiStats['avgDsSNR']) && is_numeric($cactiStats['avgDsSNR']) &&
        preg_match('/^cm-(\d+)\./m', $hostname, $match)) {
        $content = sprintf(
            "UPDATE modem SET us_pwr = %d, us_snr = %d, ds_pwr = %d, ds_snr = %d WHERE id = %d;\n",
            round($cactiStats['avgUsPow']),
            round($cactiStats['avgUsSNR']),
            round($cactiStats['avgDsPow']),
            round($cactiStats['avgDsSNR']),
            $match[1]
        );

        if (isset($preEquData['tdr']) && isset($preEquData['max'])) {
            $content .= sprintf("UPDATE modem SET tdr = %f, fft_max = %f WHERE id = %d;\n", $preEquData['tdr'], $preEquData['max'], $match[1]);
        }

        file_put_contents("$path/update.sql", $content, FILE_APPEND | LOCK_EX);
    }

    $cactiString = '';
    foreach ($cactiStats as $key => $val) {
        $cactiString .= is_numeric($val) ? "$key:$val " : "$key:NaN ";
    }

    return trim($cactiString);
}

function ss_docsis_get_pnm($file)
{
    $keep = ['descr', 'max', 'tdr', 'width'];
    $explode = ['energy', 'fft', 'power'];

    if (preg_match_all("/^([^\.].+?):(.+)/m", $file, $match) != count(array_merge($keep, $explode)) ||
        array_diff(array_merge($keep, $explode), $match[1])) {
        return [];
    }

    $preEquData = array_combine($match[1], $match[2]);
    foreach ($explode as $explodeKeys) {
        $preEquData[$explodeKeys] = explode(',', $preEquData[$explodeKeys]);
    }

    return $preEquData;
}
