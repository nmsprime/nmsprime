<?php

/* do NOT run this script through a web browser */
if (! isset($_SERVER['argv'][0]) || isset($_SERVER['REQUEST_METHOD']) || isset($_SERVER['REMOTE_ADDR'])) {
    die('<br><strong>This script is only meant to run at the command line.</strong>');
}

$no_http_headers = true;

/* display No errors */
error_reporting(0);

include_once dirname(__FILE__).'/../lib/snmp.php';

if (! isset($called_by_script_server)) {
    if (file_exists(dirname(__FILE__).'/../include/global.php')) {
        include_once dirname(__FILE__).'/../include/global.php';
    } else {
        include_once dirname(__FILE__).'/../include/config.php';
    }
    array_shift($_SERVER['argv']);
    echo call_user_func_array('ss_docsis_stats', $_SERVER['argv']);
}

function ss_docsis_stats($hostname, $snmp_community, $snmp_version, $snmp_port, $snmp_timeout, $snmpv3_auth_username, $snmpv3_auth_password)
{
    if (($snmp_version == '1' | $snmp_version == '2')) {
        $snmpv3_auth_username = '';
        $snmpv3_auth_password = '';
        $snmpv3_auth_protocol = '';
        $snmpv3_priv_passphrase = '';
        $snmpv3_priv_protocol = '';
    }

    $result = '';

    $oids = [
                'docsIfDownChannelPower' =>      '.1.3.6.1.2.1.10.127.1.1.1.1.6.3',
                'docsIfSigQSignalNoise' =>       '.1.3.6.1.2.1.10.127.1.1.4.1.5.3',
                'docsIfSigQMicroreflections' =>  '.1.3.6.1.2.1.10.127.1.1.4.1.6.3',
                'docsIfCmRangingTimeout' =>      '.1.3.6.1.2.1.10.127.1.2.1.1.4.2',
                'docsIfCmStatusTxPower' =>       '.1.3.6.1.2.1.10.127.1.2.2.1.3.2',
                'docsIfCmStatusResets' =>        '.1.3.6.1.2.1.10.127.1.2.2.1.4.2',
                'docsIfCmStatusLostSyncs' =>     '.1.3.6.1.2.1.10.127.1.2.2.1.5.2',
                'docsIfCmStatusT1Timeouts' =>    '.1.3.6.1.2.1.10.127.1.2.2.1.10.2',
                'docsIfCmStatusT2Timeouts' =>    '.1.3.6.1.2.1.10.127.1.2.2.1.11.2',
                'docsIfCmStatusT3Timeouts' =>    '.1.3.6.1.2.1.10.127.1.2.2.1.12.2',
                'docsIfCmStatusT4Timeouts' =>    '.1.3.6.1.2.1.10.127.1.2.2.1.13.2',
                ];

    for ($i = 0; $i < (count($oids)); $i++) {
        $row = each($oids);
        $var = (cacti_snmp_get($hostname, $snmp_community, $row['value'], $snmp_version, $snmpv3_auth_username, $snmpv3_auth_password, $snmp_port, $snmp_timeout, SNMP_POLLER));
        $var = trim($var);
        preg_match("/(-?\d*\.?\d*)/", $var, $matches);
        $result = is_numeric($matches[1]) ? ($result.$row['key'].':'.$matches[1].' ') : ($result.$row['key'].':NaN ');
    }

    return trim($result);
}
