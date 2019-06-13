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
    include_once dirname(__FILE__).'/../include/global.php';

    array_shift($_SERVER['argv']);

    echo call_user_func_array('ss_docsis_cmts_cm_count', $_SERVER['argv']);
}

function ss_docsis_cmts_cm_count($hostname, $snmp_community, $snmp_version, $snmp_port, $snmp_timeout)
{
    $snmp_auth_username = '';

    $snmp_auth_password = '';

    $snmp_auth_protocol = '';

    $snmp_priv_passphrase = '';

    $snmp_priv_protocol = '';

    $snmp_context = '';

    $oid = '.1.3.6.1.2.1.10.127.1.3.3.1.9'; //docsIfCmtsCmStatusValue

    $other = 0;

    $ranging = 0;

    $rangingAborted = 0;

    $rangingComplete = 0;

    $ipComplete = 0;

    $registrationComplete = 0;

    $accessDenied = 0;

    $cms = reindex(cacti_snmp_walk($hostname, $snmp_community, $oid, $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, read_config_option('snmp_retries'), SNMP_POLLER));

    //print_r($cms);

    $totals = array_count_values($cms);

    /*Fields defined in docsIfCmtsCmStatusValue */

    for ($i = 0; ($i < count($totals)); $i++) {
        $row = each($totals);

        if ($row['key'] == 1) {
            $other = $row['value'];
        } elseif ($row['key'] == 2) {
            $ranging = $row['value'];
        } elseif ($row['key'] == 3) {
            $rangingAborted = $row['value'];
        } elseif ($row['key'] == 4) {
            $rangingComplete = $row['value'];
        } elseif ($row['key'] == 5) {
            $ipComplete = $row['value'];
        } elseif ($row['key'] == 6) {
            $registrationComplete = $row['value'];
        } elseif ($row['key'] == 7) {
            $accessDenied = $row['value'];
        }
    }

    return "other:$other "."ranging:$ranging "."rangingAborted:$rangingAborted "."rangingComplete:$rangingComplete "."ipComplete:$ipComplete "."registrationComplete:$registrationComplete "."accessDenied:$accessDenied";
}

function reindex($arr)
{
    $return_arr = [];

    for ($i = 0; ($i < count($arr)); $i++) {
        $return_arr[$i] = $arr[$i]['value'];
    }

    return $return_arr;
}
