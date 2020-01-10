<?php

namespace Modules\BillingBase\Entities;

use ChannelLog;
use Modules\BillingBase\Providers\SettlementRunData;

class PurtelCdr extends CdrGetter
{
    /**
     * Load Call Data Records from PURTel Interface and save to accounting directory of appropriate date
     *
     * @param int       timestamp
     * @return int      0|1 success|error
     */
    public static function get($time = 0)
    {
        if (! env('PURTEL_RESELLER_USERNAME')) {
            return;
        }

        $timeOfFile = self::get_time_of_file($time);
        $timeOfDir = self::get_time_of_dir($time);

        \ChannelLog::debug('billing', 'Load PURTel Call Data Records');

        $user = env('PURTEL_RESELLER_USERNAME');
        $password = env('PURTEL_RESELLER_PASSWORD');

        $from = date('Ym01', $timeOfFile);
        $to = date('Ymd', strtotime('last day of', $timeOfFile));

        $url = "https://ipcom.purtel.com/index.php?super_passwort=$password&evn_bis=$to&super_username=$user&erweitert=1&action=getcdr&lastid=1&evn_von=$from";

        try {
            \ChannelLog::debug('billing', "GET $url");
            $data = file_get_contents($url);
        } catch (\Exception $e) {
            \ChannelLog::alert('billing', 'CDR-Import: Could not get Call Data Records from PURTel for month: '.date('m', $timeOfFile));

            return -1;
        }

        $target_dir = SettlementRun::get_absolute_accounting_dir_path($timeOfDir);
        $target_filepath = self::get_cdr_pathname('purtel', $time);

        if (! is_dir($target_dir)) {
            mkdir($target_dir, 0744, true);
        }

        \File::put($target_filepath, $data);
        echo "New file: $target_filepath\n";

        \ChannelLog::debug('billing', "New file $target_filepath");
    }

    /**
     * Parse PurTel CSV
     *
     * NOTE: Username to phonenumber combination must never change!
     *
     * @return array    [contract_id/contract_number => [Calling Number, Date, Starttime, Duration, Called Number, Price], ...]
     */
    public function parse()
    {
        if (! env('PURTEL_RESELLER_USERNAME')) {
            return;
        }

        $filepath = self::get_cdr_pathname('purtel');

        if (! is_file($filepath)) {
            \ChannelLog::error('billing', trans('billingbase::messages.cdr.missingEvn', ['provider' => 'PurTel']));

            return;
        }

        ChannelLog::debug('billing', 'Parse PurTel Call Data Records CSV');

        $csv = file($filepath);
        $calls = [[]];

        if (! $csv) {
            ChannelLog::warning('billing', 'Empty envia call data record file');

            return $calls;
        }

        // skip first line of csv (column description)
        unset($csv[0]);

        $logged = $phonenumbers = $unassigned = $mismatches = [];
        $price = $count = 0;
        $customer_nrs = self::get_customer_nrs();
        $registrar = 'purtel.com';
        $cdr_first_day_of_month = date('Y-m-01', strtotime('first day of -'.(1 + SettlementRunData::getConf('cdr_offset')).' month'));

        // get phonenumbers because only username is given in CDR.csv
        $phonenumbers_db = $this->get_phonenumbers($registrar);

        // Identification and comparison is done via unique username of phonenr and customer number (contract number must be equal to external customer nr)
        foreach ($phonenumbers_db as $p) {
            $phonenumbers[$p->username] = $p->prefix_number.$p->number;
            $contractnrs[$p->username][] = $p->contractnr;
        }

        // this is needed for backward compatibility to old system
        $cdr_nr_prefix_replacements = self::getCdrNrPrefixReplacements();

        foreach ($csv as $line) {
            $arr = str_getcsv($line, ';');

            $customer_nr = str_replace($cdr_nr_prefix_replacements, '', $arr[7]);
            $username = $arr[2];
            $date = explode(' ', $arr[1]);

            if (! isset($phonenumbers[$username])) {
                // ChannelLog::warning('billing', "Phonenr of contract $customer_nr with username $username not found in DB. Calling number will not appear on invoice.");
                $phonenumbers[$username] = ' - ';
            }

            $data = [
                'calling_nr' => $phonenumbers[$username],
                'date'      => $date[0],
                'starttime' => $date[1],
                'duration'  => gmdate('H:i:s', $arr[4]),
                'called_nr' => $arr[3],
                'price'     => $arr[10] / 100,
            ];

            if (in_array($customer_nr, $customer_nrs)) {
                $calls[$customer_nr][] = $data;

                // check and log if phonenumber does not exist or does not belong to contract
                if (! isset($contractnrs[$username])) {
                    $mismatches[$customer_nr][$data['calling_nr']] = 'missing';
                } elseif (! in_array($customer_nr, $contractnrs[$username])) {
                    $mismatches[$customer_nr][$data['calling_nr']] = 'mismatch';
                }
            } else {
                // cumulate price of calls that can not be assigned to any contract
                if (! isset($unassigned[$arr[7]][$data['calling_nr']])) {
                    $unassigned[$arr[7]][$data['calling_nr']] = ['count' => 0, 'price' => 0];
                }

                $unassigned[$arr[7]][$data['calling_nr']]['count'] += 1;
                $unassigned[$arr[7]][$data['calling_nr']]['price'] += $data['price'];
            }
        }

        $this->_log_phonenumber_mismatches($mismatches, 'PurTel');
        $this->_log_unassigned_calls($unassigned);

        if ($logged) {
            ChannelLog::notice('billing', 'Purtel-CSV: Discard calls from customer numbers '.implode(', ', $logged).' (still km3 customer - from Drebach)');
        }

        $this->_log_unassigned_calls($unassigned);

        // warning when there are approx 5 times more customers then calls
        if ($calls && (count($phonenumbers_db) > 5 * count($calls))) {
            ChannelLog::warning('billing', 'Very little data in purtel call data record file ('.count($csv).' records). Possibly missing data!');
        }

        return $calls;
    }
}
