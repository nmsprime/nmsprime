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
    $rates = ['+8 hours', '+4 hours', '+6 minutes'];
    $deviceStats = json_decode(file_exists($deviceFile) ? file_get_contents($deviceFile) : '{"rate":2}', true);

    if (! isset($deviceStats['next']) || time() > $deviceStats['next']) {
        $monPath = "{$basepath}/".date('Ymd');
        $monFile = "{$monPath}/{$hostname}.csv";
        $columns = 'Timestamp;PNM;Bandwidth;'
            .'US SNR min;US SNR avg;US SNR max;US Power min;US Power avg;US Power max;'
            .'DS SNR min;DS SNR avg;DS SNR max;DS Power min;DS Power avg;DS Power max;'
            ."MuRef min;MuRef avg;MuRef max;T3Timeout;T4Timeout;Corrected;Uncorrectable\n";

        if (! file_exists($monFile)) {
            mkdir($monPath);
            file_put_contents($monFile, $columns, LOCK_EX);
        }

        $deviceStats['next'] = strtotime($rates[2]);

        $cactiStatsJson = [];
        foreach ($json as $name => $oid) {
            preg_match_all("/^$oid\.(\d+) (.+)/m", $file, $match);
            $cactiStatsJson[$name] = array_combine($match[1], $match[2]);
        }

        $deviceStats['preEqu'] = $cactiStatsJson['PreEqu'] ? preg_replace('/[^A-Fa-f0-9]/', '', reset($cactiStatsJson['PreEqu'])) : '';
        // assume US bandwidth of 3.2MHz, if we can't get the actual value
        $deviceStats['width'] = $cactiStatsJson['UsWidth'] ? reset($cactiStatsJson['UsWidth']) : 3200000;
        $deviceStats['next'] = strtotime($rates[$deviceStats['rate']]);
        $deviceStats['descr'] = isset($cactiStatsJson['SysDescr']) ? reset($cactiStatsJson['SysDescr']) : 'n/a';

        /* pre-equalization calculations */
        $freq = $deviceStats['width'];
        $hexs = str_split($deviceStats['preEqu'], 8);
        $or_hexs = array_shift($hexs);
        $maintap = 2 * $or_hexs[1] - 2;
        $energymain = $maintap / 2;
        array_splice($hexs, 0, 0);
        $hexs = implode('', $hexs);
        $hexs = str_split($hexs, 4);
        $hexcall = $hexs;
        $counter = 0;
        foreach ($hexs as $hex) {
            $hsplit = str_split($hex, 1);
            $counter++;
            if (is_numeric($hsplit[0]) && $hsplit[0] == 0 && $counter >= 46) {
                $decimal = _threenibble($hexcall);
                break;
            } elseif (ctype_alpha($hsplit[0]) || $hsplit[0] != 0 && $counter >= 46) {
                $decimal = _fournibble($hexcall);
                break;
            }
        }
        $pwr = _nePwr($decimal, $maintap);
        $ene = _energy($pwr, $maintap, $energymain);
        $fft = _fft($pwr);
        $tdr = _tdr($ene, $energymain, $freq);
        $preEquData['power'] = $pwr;
        $preEquData['energy'] = $ene;
        $preEquData['tdr'] = $tdr;
        $preEquData['max'] = $fft[1];
        $preEquData['fft'] = $fft[0];

        $monitorString = date('YmdHi').";{$deviceStats['preEqu']};{$deviceStats['width']};"
            ."{$cactiStats['minUsSNR']};{$cactiStats['avgUsSNR']};{$cactiStats['maxUsSNR']};"
            ."{$cactiStats['minUsPow']};{$cactiStats['avgUsPow']};{$cactiStats['maxUsPow']};"
            ."{$cactiStats['minDsSNR']};{$cactiStats['avgDsSNR']};{$cactiStats['maxDsSNR']};"
            ."{$cactiStats['minDsPow']};{$cactiStats['avgDsPow']};{$cactiStats['maxDsPow']};"
            ."{$cactiStats['minMuRef']};{$cactiStats['avgMuRef']};{$cactiStats['maxMuRef']};"
            ."{$cactiStats['T3Timeout']};{$cactiStats['T4Timeout']};"
            ."{$cactiStats['Corrected']};{$cactiStats['Uncorrectable']};\n";

        file_put_contents($deviceFile, json_encode(array_merge($deviceStats, $preEquData)));
        file_put_contents($monFile, $monitorString, FILE_APPEND);
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

        file_put_contents("$path/update.sql", $content, FILE_APPEND | LOCK_EX);
    }

    $cactiString = '';
    foreach ($cactiStats as $key => $val) {
        $cactiString .= is_numeric($val) ? "$key:$val " : "$key:NaN ";
    }

    return trim($cactiString);
}

function _threenibble($hexcall)
{
    $ret = [];
    $counter = 0;

    foreach ($hexcall as $hex) {
        $counter++;
        if ($counter < 49) {
            $hex = str_split($hex, 1);
            if (ctype_alpha($hex[1]) || $hex[1] > 7) {
                $hex[0] = 'F';
                $hex = implode('', $hex);
                $hex = preg_replace('/[^0-9A-Fa-f]/', '', $hex);
                $hex = strrev("$hex");
                $dec = array_values(array_slice(unpack('s', pack('h*', "$hex")), -1))[0];
                array_push($ret, $dec);
            } else {
                $hex[0] = 0;
                $hex = implode('', $hex);
                $hex = preg_replace('/[^0-9A-Fa-f]/', '', $hex);
                $hex = strrev("$hex");
                $dec = array_values(array_slice(unpack('s', pack('h*', "$hex")), -1))[0];
                array_push($ret, $dec);
            }
        }
    }

    return $ret;
}

function _fournibble($hexcall)
{
    $ret = [];
    $counter = 0;

    foreach ($hexcall as $hex) {
        $counter++;
        if ($counter < 49) {
            $hex = preg_replace('/[^0-9A-Fa-f]/', '', $hex);
            $hex = strrev("$hex");
            $dec = array_values(array_slice(unpack('s', pack('h*', "$hex")), -1))[0];
            array_push($ret, $dec);
        }
    }

    return $ret;
}

function _nePwr($decimal, $maintap)
{
    $pwr = [];
    $ans = implode('', array_keys($decimal, max($decimal)));
    if ($maintap == $ans) {
        $a2 = $decimal[$maintap];
        $b2 = $decimal[$maintap + 1];
        foreach (array_chunk($decimal, 2) as $val) {
            $a1 = $val[0];
            $b1 = $val[1];
            $pwr[] = ($a1 * $a2 - $b1 * $b2) / ($a2 ** 2 + $b2 ** 2);
            $pwr[] = ($a2 * $b1 + $a1 * $b2) / ($a2 ** 2 + $b2 ** 2);
        }
    } else {
        for ($i = 0; $i < 48; $i++) {
            $pwr[] = 0;
        }
    }

    return $pwr;
}

function _energy($pwr, $maintap, $energymain)
{
    $ene_db = [];
    //calculating the magnitude
    $pwr = array_chunk($pwr, 2);
    foreach ($pwr as $val) {
        $temp = 10 * log10($val[0] ** 2 + $val[1] ** 2);
        if (! (is_finite($temp))) {
            $temp = -100;
        }
        $ene_db[] = round($temp, 2);
    }

    return $ene_db;
}

function _tdr($ene, $energymain, $freq)
{
    if ($ene[$energymain] == -100) {
        $tdr = 0;
    } else {
        // propgagtion speed in cable networks (87% speed of light)
        $v = 0.87 * 299792458;
        unset($ene[$energymain]);
        $highest = array_keys($ene, max($ene));
        $highest = implode('', $highest);
        $tap_diff = abs($energymain - $highest);
        // 0.8 - Roll-off of filter; /2 -> round-trip (back and forth)
        $tdr = $v * $tap_diff / (0.8 * $freq) / 2;
        $tdr = round($tdr, 1);
    }

    return $tdr;
}

function _fft($pwr)
{
    $rea = [];
    $imag = [];
    $pwr = array_chunk($pwr, 2);
    foreach ($pwr as $val) {
        $rea[] = $val[0];
        $imag[] = $val[1];
    }

    for ($i = 0; $i < 104; $i++) {
        array_push($rea, 0);
        array_push($imag, 0);
    }

    for ($i = 0; $i < 248; $i++) {
        array_push($rea, array_shift($rea));
        array_push($imag, array_shift($imag));
    }

    require_once __DIR__.'/../../../../vendor/brokencube/fft/src/FFT.php';
    $ans = Brokencube\FFT\FFT::run($rea, $imag);
    ksort($ans[0]);
    ksort($ans[1]);
    for ($i = 0; $i < 64; $i++) {
        array_push($ans[0], array_shift($ans[0]));
        array_push($ans[1], array_shift($ans[1]));
    }

    $answer = array_map(function ($v1, $v2) {
        return 20 * log10(sqrt($v1 ** 2 + $v2 ** 2));
    }, $ans[0], $ans[1]);

    // stores the maximum amplitude value of the fft waveform
    $x = max($answer);
    $y = abs(min($answer));
    $maxamp = $x >= $y ? $x : $y;

    if (! (is_finite($maxamp))) {
        $maxamp = 0;
    }

    return [$answer, $maxamp];
}
