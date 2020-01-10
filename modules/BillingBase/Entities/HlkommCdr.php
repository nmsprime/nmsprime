<?php

namespace Modules\BillingBase\Entities;

use ChannelLog;
use Modules\BillingBase\Providers\BillingConf;

class HlkommCdr extends CdrGetter
{
    /**
     * Load Call Data Records from HLKomm Interface and save to accounting directory of appropriate date
     *
     * @param int       timestamp
     * @return int      0 on success, -1 on error
     */
    public static function get($time = 0)
    {
        if (! env('HLKOMM_RESELLER_USERNAME')) {
            return;
        }

        \ChannelLog::debug('billing', 'Load HL Komm Call Data Records');

        $user = env('HLKOMM_RESELLER_USERNAME');
        $customer = env('HLKOMM_RESELLER_CUSTOMER');
        $password = env('HLKOMM_RESELLER_PASSWORD');

        $timeOfFile = self::get_time_of_file($time);
        $timeOfDir = self::get_time_of_dir($time);

        // establish ftp connection and login
        $ftp_server = 'ftp.hlkomm.net';
        // TODO: get Customer number (004895) from env
        $ftp_dir = "elektronische_Rechnungen/$customer";

        $ftp_conn = ftp_connect($ftp_server);
        if (! $ftp_conn) {
            \ChannelLog::error('billing', 'Load-CDR: Could not establish ftp connection!', [__FUNCTION__]);

            return -1;
        }

        $login = ftp_login($ftp_conn, $user, $password);
        // enable passive mode for client-to-server connections
        ftp_pasv($ftp_conn, true);
        $file_list = ftp_nlist($ftp_conn, $ftp_dir);
        // $file_list = ftp_rawlist($ftp_conn, ".");

        // find correct filename
        foreach ($file_list as $fname) {
            if (strpos($fname, date('Ym', strtotime('first day of next month', $timeOfFile))) !== false && strpos($fname, '_EVN.txt') !== false) {
                $remote_fname = "$ftp_dir/$fname";
                break;
            }
        }

        if (! isset($remote_fname)) {
            \ChannelLog::error('billing', 'No CDR file on ftp server that matches naming conventions', [__FUNCTION__]);

            return -1;
        }

        $target_dir = SettlementRun::get_absolute_accounting_dir_path($timeOfDir);
        $target_filepath = self::get_cdr_pathname('hlkomm', $time);

        if (! is_dir($target_dir)) {
            mkdir($target_dir, 0744, true);
        }

        if (ftp_get($ftp_conn, $target_filepath, $remote_fname, FTP_BINARY)) {
            \ChannelLog::debug('billing', "New file $target_filepath");
            echo "New file: $target_filepath\n";
        } else {
            \ChannelLog::error('billing', 'Could not retrieve CDR file from ftp server', [__FUNCTION__]);

            return -1;
        }

        ftp_close($ftp_conn);
    }

    /**
     * Parse HLKomm CSV
     *
     * @return array    [contract_id/contract_number => [Calling Number, Date, Starttime, Duration, Called Number, Price], ...]
     */
    public function parse()
    {
        if (! env('HLKOMM_RESELLER_USERNAME')) {
            return;
        }

        $filepath = self::get_cdr_pathname('hlkomm');

        if (! is_file($filepath)) {
            \ChannelLog::error('billing', trans('billingbase::messages.cdr.missingEvn', ['provider' => 'HlKomm']));

            return;
        }

        $csv = file($filepath);
        $calls = [[]];

        if (! $csv) {
            ChannelLog::warning('billing', 'Empty hlkomm call data record file');

            return [[]];
        }

        // skip first 5 lines (descriptions)
        unset($csv[0], $csv[1], $csv[2], $csv[3], $csv[4]);

        $config = BillingBase::first();
        $unassigned = [];

        // get phonenr to contract_id listing - needed because only phonenr is mentioned in csv
        // BUG: Actually when a phonenumber is deleted on date 1.5. and then the same number is assigned to another contract, all
        // records of 1.4.-30.4. would be assigned to the new contract that actually hasn't done any call yet
        // As precaution we warn the user when he changes or creates a phonenumber so that this bug would be affected
        $phonenumbers_db = $this->get_phonenumbers('sip.hlkomm.net');

        foreach ($phonenumbers_db as $value) {
            if ($value->username) {
                if (substr($value->username, 0, 4) == '0049') {
                    $phonenrs[substr_replace($value->username, '49', 0, 4)] = $value->contract_id;
                }
            }
        }

        // create structured array
        foreach ($csv as $line) {
            $line = str_getcsv($line, "\t");
            $phonenr1 = $line[4].$line[5].$line[6];         // calling nr
            $phonenr2 = $line[7].$line[8].$line[9];         // called nr

            $data = [
                'calling_nr' => $phonenr1,
                'date'      => $line[0],
                'starttime' => $line[1],
                'duration'  => $line[10],
                'called_nr' => $phonenr2,
                'price'     => str_replace(',', '.', $line[13]),
            ];

            // calculate price with hlkomms distance zone
            // $a[5] = strpos($line[3], 'Mobilfunk national') !== false ? $a[5] * ($config->voip_extracharge_mobile_national / 100 + 1) : $a[5] * ($config->voip_extracharge_default / 100 + 1);
            $data['price'] = $line[15] == '990711' ? $data['price'] * ($config->voip_extracharge_mobile_national / 100 + 1) : $data['price'] * ($config->voip_extracharge_default / 100 + 1);

            if (isset($phonenrs[$phonenr1])) {
                $calls[$phonenrs[$phonenr1]][] = $data;
            } elseif (isset($phonenrs[$phonenr2])) {
                // our phonenr is the called nr - TODO: proof if this case can actually happen - normally this shouldnt be the case
                $calls[$phonenrs[$phonenr2]][] = $data;
            } else {
                // there is a phonenr entry in csv that doesnt exist in our db - this case should never happen
                if (! isset($unassigned[$phonenr1])) {
                    $unassigned[$phonenr1] = ['count' => 0, 'price' => 0];
                }

                $unassigned[$phonenr1]['count'] += 1;
                $unassigned[$phonenr1]['price'] += $data['price'];
            }
        }

        foreach ($unassigned as $pn => $arr) {
            $price = \App::getLocale() == 'de' ? number_format($arr['price'], 2, ',', '.') : number_format($arr['price'], 2, '.', ',');
            ChannelLog::error('billing', trans('messages.cdr_missing_phonenr', ['phonenr' => $pn, 'count' => $arr['count'], 'price' => $price, 'currency' => BillingConf::currency()]));
        }

        return $calls;
    }
}
