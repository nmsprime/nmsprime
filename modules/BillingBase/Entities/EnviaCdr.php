<?php

namespace Modules\BillingBase\Entities;

use ChannelLog;
use Chumper\Zipper\Zipper;

class EnviaCdr extends CdrGetter
{
    /**
     * Load Call Data Records from EnviaTel Interface and save to accounting directory of appropriate date
     *
     * @param int       timestamp
     * @return int      0|1 success|error
     */
    public static function get($time = 0)
    {
        if (! env('PROVVOIPENVIA__RESELLER_USERNAME')) {
            return;
        }

        $timeOfFile = self::get_time_of_file($time);
        $timeOfDir = self::get_time_of_dir($time);

        $user = env('PROVVOIPENVIA__RESELLER_USERNAME');
        $password = env('PROVVOIPENVIA__RESELLER_PASSWORD');
        $customer = env('PROVVOIPENVIA__RESELLER_CUSTOMER');

        if (! $user || ! $password || ! $customer) {
            ChannelLog::error('billing', 'Missing EnviaTel authentification data in environment file');
        }

        try {
            ChannelLog::debug('billing', "GET: https://$user:$password@portal.enviatel.de/vertrieb2/reseller/evn/$customer/".date('Y/m', $timeOfFile));
            $context = stream_context_create(['http' => ['header'  => 'Authorization: Basic '.base64_encode("$user:$password")]]);
            $data = file_get_contents("https://portal.enviatel.de/vertrieb2/reseller/evn/$customer/".date('Y/m', $timeOfFile), false, $context);
        } catch (\Exception $e) {
            ChannelLog::alert('billing', 'CDR-Import: Could not get Call Data Records from envia TEL for month: '.date('m', $timeOfFile), ["portal.enviatel.de/vertrieb2/reseller/evn/$customer/".date('Y/m', $timeOfFile)]);

            return -1;
        }

        $tmp_file = 'cdr.zip';
        \Storage::put("tmp/$tmp_file", $data);

        $tmp_dir = storage_path('app/tmp');
        $target_dir = SettlementRun::get_absolute_accounting_dir_path($timeOfDir);
        $target_filepath = self::get_cdr_pathname('envia', $time);

        if (! is_dir($target_dir)) {
            mkdir($target_dir, 0744, true);
        }

        $zipper = new Zipper;
        $zipper->make("$tmp_dir/$tmp_file")->extractTo($tmp_dir);

        // TODO: Rename File
        $files = \Storage::files('tmp');

        foreach ($files as $name) {
            if (strpos($name, date('m.Y', $timeOfFile)) !== false && strpos($name, 'AsciiEVN.txt') !== false && strpos($name, 'xxxxxxx') !== false) {
                rename(storage_path('app/'.$name), $target_filepath);
                break;
            }
        }

        ChannelLog::debug('billing', "New file $target_filepath");
        echo "New file: $target_filepath\n";

        // execute phonenumber filter command if it exists (e.g. on mablx10, olblx10)
        $filter = storage_path('app/config/billingbase/filter-cdr.sh');
        if (file_exists($filter)) {
            ChannelLog::info('billing', "Filtered phonenumbers in $target_filepath via $filter");
            system("$filter $target_filepath");
        }

        \Storage::delete("tmp/$tmp_file");
    }

    /**
     * Parse envia TEL CSV and Check if customerNr to Phonenr assignment exists
     *
     * @return array  [contract_id/contract_number => [Calling Number, Date, Starttime, Duration, Called Number, Price], ...]
     */
    public function parse()
    {
        if (! env('PROVVOIPENVIA__RESELLER_USERNAME')) {
            return;
        }

        $filepath = self::get_cdr_pathname('envia');

        if (! is_file($filepath)) {
            ChannelLog::error('billing', trans('billingbase::messages.cdr.missingEvn', ['provider' => 'EnviaTel']));

            return;
        }

        ChannelLog::debug('billing', 'Parse envia TEL Call Data Records CSV');

        $csv = file($filepath);
        $calls = [[]];

        if (! $csv) {
            ChannelLog::error('billing', 'Empty envia call data record file');

            return $calls;
        }

        $pns = $this->get_phonenumbers('sip.enviatel.net')->all();
        $pns2 = $this->get_phonenumbers('verbindet.net', false)->all();

        $pns = array_merge($pns, $pns2);

        foreach ($pns as $pn) {
            $pn_customer[substr_replace($pn->prefix_number, '49', 0, 1).$pn->number][] = $pn->contractnr;
        }

        // this is needed for backward compatibility to old system
        $cdr_nr_prefix_replacements = self::getCdrNrPrefixReplacements();

        // skip first line of csv (column description)
        unset($csv[0]);
        $price = $count = 0;
        $unassigned = $mismatches = [];
        $customer_nrs = self::get_customer_nrs();

        foreach ($csv as $line) {
            $arr = str_getcsv($line, ';');

            // replace prefixes of enviatel customer numbers that not exist in NMSPrime
            $customer_nr = str_replace($cdr_nr_prefix_replacements, '', $arr[0]);

            $data = [
                'calling_nr' => $arr[3],
                'date'      => substr($arr[4], 4).'-'.substr($arr[4], 2, 2).'-'.substr($arr[4], 0, 2),
                'starttime' => $arr[5],
                'duration'  => $arr[6],
                'called_nr' => $arr[7],
                'price'     => str_replace(',', '.', $arr[10]),
            ];

            // extend $data for other providers
            // attention: envia sends latin-1 – we have to convert to UTF-8…
            if ($arr[11] != '016-envia TEL') {
                $data['called_nr'] = ['enviaCDR', $arr[11], $arr[7], $arr[8], $arr[9]];
            }

            if (in_array($customer_nr, $customer_nrs)) {
                $calls[$customer_nr][] = $data;

                // check and log if phonenumber does not exist or does not belong to contract
                if (! isset($pn_customer[$data['calling_nr']])) {
                    $mismatches[$customer_nr][$data['calling_nr']] = 'missing';
                } elseif (! in_array($customer_nr, $pn_customer[$data['calling_nr']])) {
                    $mismatches[$customer_nr][$data['calling_nr']] = 'mismatch';
                }
            } else {
                // cumulate price of calls that can not be assigned to any contract
                if (! isset($unassigned[$arr[0]][$data['calling_nr']])) {
                    $unassigned[$arr[0]][$data['calling_nr']] = ['count' => 0, 'price' => 0];
                }

                $unassigned[$arr[0]][$data['calling_nr']]['count'] += 1;
                $unassigned[$arr[0]][$data['calling_nr']]['price'] += $data['price'];
            }
        }

        $this->_log_phonenumber_mismatches($mismatches, 'EnviaTel');
        $this->_log_unassigned_calls($unassigned);

        // warning when there are 5 times more customers then calls
        if ($csv && (count($customer_nrs) > 10 * count($csv))) {
            ChannelLog::warning('billing', 'Very little data in enviatel call data record file ('.count($csv).' records). Possibly missing data!');
        }

        return $calls;
    }
}
