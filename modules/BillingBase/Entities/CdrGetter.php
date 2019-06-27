<?php

namespace Modules\BillingBase\Entities;

use Storage;
use ChannelLog;
use Modules\BillingBase\Providers\Currency;
use Modules\BillingBase\Providers\SettlementRunData;

class CdrGetter
{
    /**
     * Supported providers
     *
     * @var array
     */
    protected static $providers = [
        'ENVIA',
        'PURTEL',
        'HLKOMM',
    ];

    public function __construct()
    {
        $lang = SettlementRunData::getConf('userlang');

        if ($lang) {
            \App::setLocale($lang);
        }
    }

    /**
     * Download Call Data Record files from providers
     *
     * @param mixed     DateString in Format 'Y-m' or Integer for SettlementRun ID
     */
    public static function get($arg = 0)
    {
        $time = 0;

        // Handle argument
        if ($arg) {
            if (is_int($arg)) {
                $sr = SettlementRun::findOrFail($arg);
                $time = strtotime($sr->year.'-'.str_pad($sr->month, 2, '0', STR_PAD_LEFT));
            } elseif (is_string($arg)) {
                $time = strtotime($arg);

                if ($time === false) {
                    $msg = trans('billingbase::messages.cdr.wrongArgument');
                    ChannelLog::debug('billing', $msg);
                    echo "$msg\n";
                }
            }
        }

        // Get CSVs
        foreach (self::$providers as $name) {
            $filepath = self::get_cdr_pathname(strtolower($name), $time);

            if (is_file($filepath)) {
                \ChannelLog::info('billing', "$name CDR already loaded");
                continue;
            }

            $class = '\\Modules\\BillingBase\\Entities\\'.ucwords(strtolower($name)).'Cdr';
            $class = self::getClassNameFromProvider($name);

            $ret = $class::get($time);

            if ($ret == -1) {
                echo "Error: Failed to load $name CDR\n";
                // \ChannelLog::error('billing', trans('messages.cdr_missing_reseller_data'));
                // throw new Exception(trans('messages.cdr_missing_reseller_data'));
            }
        }
    }

    /**
     * Parse stored Call Data Record files
     *
     * NOTE/TODO: 1000 Phonecalls need a bit more than 1 MB memory - if files get too large and we get memory
     *  problems again, we should probably save calls to database and get them during command when needed
     *
     * @return array    [contract_id_1 => [phonr_nr, time, duration, ...],
     *                   contract_id_2 => [...],
     *                   ...]
     */
    public function parse()
    {
        $calls = $calls_total = [];

        foreach (self::$providers as $name) {
            $className = self::getClassNameFromProvider($name);
            $class = new $className;

            $calls = $class->parse();

            if (! $calls) {
                continue;
            }

            foreach ($calls as $cnr => $entries) {
                foreach ($entries as $entry) {
                    $calls_total[$cnr][] = $entry;
                }
            }
        }

        if (! $calls_total) {
            ChannelLog::warning('billing', 'No Call Data Records available for this Run!');
        }

        return $calls_total;
    }

    /**
     * @param string
     * @return string
     */
    public static function getClassNameFromProvider($provider)
    {
        return __NAMESPACE__.'\\'.ucwords(strtolower($provider)).'Cdr';
    }

    /**
     * @return int  Timestamp to build directory pathname from
     */
    public static function get_time_of_dir($timestamp = 0)
    {
        $offset = SettlementRunData::getConf('cdr_offset');

        return $timestamp ? strtotime("first day of +$offset month", $timestamp) : strtotime('first day of last month');
    }

    /**
     * @return int  Timestamp to build file pathname from
     */
    public static function get_time_of_file($timestamp = 0)
    {
        $offset = SettlementRunData::getConf('cdr_offset');

        return $timestamp ?: strtotime('first day of -'.($offset + 1).' month');
    }

    /**
     * Public function to get CDR's pathname dependent a provider - should be used
     *  everywhere else in source code where pathname is needed (SettlementRunCommand, Invoice, ...) to be consistent
     *
     * @param  string   envia|hlkomm|purtel
     * @param  int  Optional timestamp if you want a previous CDR (with records of that timestamps month)
     * @param  int  Optional if already fetched from DB to improve performance
     * @return string   Absolute path and filename of csv
     */
    public static function get_cdr_pathname($provider, $timestamp = 0)
    {
        // $provider = strtolower(str_replace('Cdr', '', explode('\\', get_class($this)))[3]);
        //  ChannelLog::error('billing', 'Wrong argument in function '.__FUNCTION__.'. Could not find provider.');

        $time_dir = self::get_time_of_dir($timestamp);
        $time_file = self::get_time_of_file($timestamp);

        $dir = SettlementRun::get_absolute_accounting_dir_path($time_dir);

        return "$dir/".\App\Http\Controllers\BaseViewController::translate_label('Call_Data_Records').'_'.strtolower($provider).'_'.date('Y_m', $time_file).'.csv';
    }

    /**
     * Get list of all call data record files important for this system (dependent of environment variables)
     *
     * NOTE: dont forget array key in environment file in form: {provider-name}_RESELLER_USERNAME  (all letters upper case)
     *
     * @param  int  Optional timestamp if you want a previous CDR (with records of that timestamps month)
     * @param  int  Optional if already fetched from DB to improve performance
     * @return array    of Strings - Absolute path and filenames of CSVs
     */
    public static function get_cdr_pathnames($timestamp = 0)
    {
        // Add new Providers here!
        $arr = [];

        foreach (self::$providers as $provider) {
            $key = $provider == 'ENVIA' ? 'PROVVOIPENVIA_' : strtoupper($provider);

            // NOTE: - use env() to parse environmment variables as the super global variable is not set in cronjobs
            if (! env("$key".'_RESELLER_USERNAME')) {
                continue;
            }

            $arr[$provider] = self::get_cdr_pathname($provider, $timestamp);
        }

        return $arr;
    }

    /**
     * Log all phonenumbers that actually do not belong to the identifier/contract number labeled in CSV
     *
     * @param array      [customer_id][phonenr] => true
     */
    protected function _log_phonenumber_mismatches($mismatches, $provider)
    {
        foreach ($mismatches as $contract_nr => $pns) {
            foreach ($pns as $p => $type) {

                // NOTE: type actually can be missing or mismatch
                ChannelLog::warning('billing', trans("messages.phonenumber_$type", [
                    'provider' => $provider,
                    'contractnr' => $contract_nr,
                    'phonenr' => $p,
                    ]));
            }
        }
    }

    /**
     * Log all cumulated prices of calls from specific phonenumbers that could not be assigned to any contract
     *
     * @param array      [customer_id][phonenr] => [count, price]
     */
    protected function _log_unassigned_calls($unassigned)
    {
        foreach ($unassigned as $customer_nr => $pns) {
            foreach ($pns as $p => $arr) {
                $price = \App::getLocale() == 'de' ? number_format($arr['price'], 2, ',', '.') : number_format($arr['price'], 2, '.', ',');

                ChannelLog::warning('billing', trans('messages.cdr_discarded_calls', [
                    'contractnr' => $customer_nr,
                    'count' => $arr['count'],
                    'phonenr' => $p,
                    'price' => $price,
                    'currency' => Currency::get(),
                    ]));
            }
        }
    }

    /**
     * Get Array of formerly used prefixes of external customer numbers on provider side (EnviaTel, PurTel, ...)
     * These prefixes have to be removed to establish the connection to the customers in NMSPrime
     *
     * @return array    default is an empty array (file does not exist)
     */
    protected static function getCdrNrPrefixReplacements()
    {
        $relFilePath = 'config/billingbase/cdr-nr-prefix-replacements';

        if (! Storage::exists($relFilePath)) {
            return [];
        }

        $cdr_nr_prefix_replacements = explode(PHP_EOL, Storage::get($relFilePath));

        array_filter($cdr_nr_prefix_replacements, function ($value) {
            return $value !== '';
        });

        return $cdr_nr_prefix_replacements;
    }

    protected static function get_customer_nrs()
    {
        $customer_nrs = [];

        $numbers = \DB::table('contract')->select(['id', 'number'])->whereNull('deleted_at')->get();

        foreach ($numbers as $num) {
            $customer_nrs[] = $num->id;
            $customer_nrs[] = $num->number;
        }

        return $customer_nrs;
    }

    /**
     * Get list of all phonenumbers of all contracts belonging to a specific registrar
     *
     * @return array
     */
    protected function get_phonenumbers($registrar, $withEmptyRegistrar = true)
    {
        $cdr_first_day_of_month = date('Y-m-01', strtotime('first day of -'.(1 + SettlementRunData::getConf('cdr_offset')).' month'));

        if ($withEmptyRegistrar) {
            $whereCondition = function ($query) use ($registrar) {
                $query
                ->where('sipdomain', 'like', "%$registrar%")
                ->orWhereNull('sipdomain')
                ->orWhere('sipdomain', '=', '');
            };
        } else {
            $whereCondition = function ($query) use ($registrar) {
                $query
                ->where('sipdomain', 'like', "%$registrar%");
            };
        }

        return \DB::table('phonenumber as p')
            ->join('mta', 'p.mta_id', '=', 'mta.id')
            ->join('modem', 'modem.id', '=', 'mta.modem_id')
            ->join('contract as c', 'c.id', '=', 'modem.contract_id')
            ->where($whereCondition)
            ->where(function ($query) use ($cdr_first_day_of_month) {
                $query
                ->whereNull('p.deleted_at')
                ->orWhere('p.deleted_at', '>=', $cdr_first_day_of_month);
            })
            ->select('modem.contract_id', 'c.number as contractnr', 'c.create_invoice', 'p.*')
            ->orderBy('p.deleted_at', 'asc')->orderBy('p.created_at', 'desc')
            // ->limit(50)
            ->get();
    }
}
