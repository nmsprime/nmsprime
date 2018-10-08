<?php

namespace Modules\BillingBase\Console;

use File;
use Chumper\Zipper\Zipper;
use Illuminate\Console\Command;
use App\Http\Controllers\BaseViewController;
use Modules\BillingBase\Entities\BillingBase;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class cdrCommand extends Command
{
    /**
     * The console command & table name
     *
     * @var string
     */
    protected $name = 'billing:cdr';
    protected $description = 'Get Call Data Records from envia TEL/HLKomm (dependent of Array keys in Environment file) - optional argument: month (integer - load file up to 12 months in past)';
    protected $signature = 'billing:cdr {--date=}';

    /**
     * Timestamps set initially to build URL's and CDR-pathnames
     */
    protected $timestamp; 		// = timestamp of date option
    protected $time_of_dir;
    protected $time_of_file;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command - Get CSV from Provider Interface if not yet done
     */
    public function fire()
    {
        $lang = BillingBase::first()->userlang;
        if ($lang) {
            \App::setLocale($lang);
        }

        \ChannelLog::debug('billing', 'Get Call Data Records');
        $missing = true;

        // Set the time the user wants the CDRs for globally for easier URL building in all functions
        $offset = BillingBase::first()->cdr_offset;
        $this->timestamp = $this->option('date') ? strtotime($this->option('date')) : 0;
        $this->time_of_dir = self::_get_time_of_dir($offset, $this->timestamp);
        $this->time_of_file = self::_get_time_of_file($offset, $this->timestamp);

        foreach (self::get_cdr_pathnames($this->timestamp, $offset) as $provider => $filepath) {
            $missing = false;

            if (is_file($filepath)) {
                \ChannelLog::info('billing', "$provider CDR already loaded");
                continue;
            }

            $ret = $this->{'_load_'.$provider.'_cdr'}();

            if ($ret == -1) {
                echo "Error: Failed to load $provider CDR\n";
            }
        }

        if ($missing) {
            \ChannelLog::error('billing', trans('messages.cdr_missing_reseller_data'));
            throw new Exception(trans('messages.cdr_missing_reseller_data'));
        }

        // chown in case command was called from commandline as root
        system('chown -R apache '.storage_path('app/data/billingbase/'));
        system('chown -R apache '.storage_path('app/tmp/'));
    }

    /**
     * Public function to get CDR's pathname dependent a provider - should be used
     *  everywhere else in source code where pathname is needed (accountingCommand, Invoice, ...) to be consistent
     *
     * @param  string   envia|hlkomm|purtel
     * @param  int 	Optional timestamp if you want a previous CDR (with records of that timestamps month)
     * @param  int  Optional if already fetched from DB to improve performance
     * @return string 	Absolute path and filename of csv
     */
    public static function get_cdr_pathname($provider, $timestamp = 0, $offset = null)
    {
        if ($offset === null) {
            $offset = BillingBase::first()->cdr_offset;
        }
        $time_dir = self::_get_time_of_dir($offset, $timestamp);
        $time_file = self::_get_time_of_file($offset, $timestamp);

        $dir = accountingCommand::get_absolute_accounting_dir_path($time_dir);

        return "$dir/".BaseViewController::translate_label('Call_Data_Records').'_'.$provider.'_'.date('Y_m', $time_file).'.csv';
    }

    /**
     * Get list of all call data record files important for this system (dependent of environment variables)
     *
     * NOTE: dont forget array key in environment file in form: {provider-name}_RESELLER_USERNAME  (all letters upper case)
     *
     * @param  int 	Optional timestamp if you want a previous CDR (with records of that timestamps month)
     * @param  int  Optional if already fetched from DB to improve performance
     * @return array 	of Strings - Absolute path and filenames of CSVs
     */
    public static function get_cdr_pathnames($timestamp = 0, $offset = null)
    {
        // Add new Providers here!
        $providers = ['envia', 'hlkomm', 'purtel'];
        $arr = [];

        foreach ($providers as $provider) {
            $key = $provider == 'envia' ? 'PROVVOIPENVIA_' : strtoupper($provider);

            // NOTE: - use env() to parse environmment variables as the super global variable is not set in cronjobs
            if (! env("$key".'_RESELLER_USERNAME')) {
                continue;
            }

            $arr[$provider] = self::get_cdr_pathname($provider, $timestamp, $offset);
        }

        return $arr;
    }

    /**
     * @return int 	Timestamp to build directory pathname from
     */
    private static function _get_time_of_dir($offset, $timestamp = 0)
    {
        return $timestamp ? strtotime("first day of +$offset month", $timestamp) : strtotime('first day of last month');
    }

    /**
     * @return int 	Timestamp to build file pathname from
     */
    private static function _get_time_of_file($offset, $timestamp = 0)
    {
        return $timestamp ?: strtotime('first day of -'.($offset + 1).' month');
    }

    /**
     * Load Call Data Records from envia TEL Interface and save file to accounting directory of appropriate date
     *
     * @return int 		0 on success, -1 on error
     */
    private function _load_envia_cdr()
    {
        $user = env('PROVVOIPENVIA__RESELLER_USERNAME');
        $password = env('PROVVOIPENVIA__RESELLER_PASSWORD');
        $customer = env('PROVVOIPENVIA__RESELLER_CUSTOMER');

        try {
            \ChannelLog::debug('billing', "GET: https://$user:$password@portal.enviatel.de/vertrieb2/reseller/evn/$customer/".date('Y/m', $this->time_of_file));
            $data = file_get_contents("https://$user:$password@portal.enviatel.de/vertrieb2/reseller/evn/$customer/".date('Y/m', $this->time_of_file));
        } catch (\Exception $e) {
            \ChannelLog::alert('billing', 'CDR-Import: Could not get Call Data Records from envia TEL for month: '.date('m', $this->time_of_file), ["portal.enviatel.de/vertrieb2/reseller/evn/$customer/".date('Y/m', $this->time_of_file)]);

            return -1;
        }

        $tmp_file = 'cdr.zip';
        \Storage::put("tmp/$tmp_file", $data);

        $tmp_dir = storage_path('app/tmp');
        $target_dir = accountingCommand::get_absolute_accounting_dir_path($this->time_of_dir);
        $target_filepath = self::get_cdr_pathname('envia', $this->timestamp);

        if (! is_dir($target_dir)) {
            mkdir($target_dir, 0744, true);
        }

        $zipper = new Zipper;
        $zipper->make("$tmp_dir/$tmp_file")->extractTo($tmp_dir);

        // TODO: Rename File
        $files = \Storage::files('tmp');

        foreach ($files as $name) {
            if (strpos($name, date('m.Y', $this->time_of_file)) !== false && strpos($name, 'AsciiEVN.txt') !== false && strpos($name, 'xxxxxxx') !== false) {
                rename(storage_path('app/'.$name), $target_filepath);
                break;
            }
        }

        \ChannelLog::debug('billing', "Successfully stored call data records in $target_filepath");
        echo "New file: $target_filepath\n";

        // execute phonenumber filter command if it exists (e.g. on mablx10, olblx10)
        $filter = storage_path('app/config/billingbase/filter-cdr.sh');
        if (file_exists($filter)) {
            \ChannelLog::info('billing', "Filtered phonenumbers in $target_filepath via $filter");
            system("$filter $target_filepath");
        }

        \Storage::delete("tmp/$tmp_file");
    }

    /**
     * Load Call Data Records from HLKomm Interface and save to accounting directory of appropriate date
     *
     * @return int 		0 on success, -1 on error
     */
    private function _load_hlkomm_cdr()
    {
        \ChannelLog::debug('billing', 'Load HL Komm Call Data Records');

        $user = env('HLKOMM_RESELLER_USERNAME');
        $password = env('HLKOMM_RESELLER_PASSWORD');

        // establish ftp connection and login
        $ftp_server = 'ftp.hlkomm.net';
        $ftp_dir = 'elektronische_Rechnungen/004895';

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
            if (strpos($fname, date('Ym', strtotime('first day of next month', $this->time_of_file))) !== false && strpos($fname, '_EVN.txt') !== false) {
                $remote_fname = "$ftp_dir/$fname";
                break;
            }
        }

        if (! isset($remote_fname)) {
            \ChannelLog::error('billing', 'No CDR file on ftp server that matches naming conventions', [__FUNCTION__]);

            return -1;
        }

        $target_dir = accountingCommand::get_absolute_accounting_dir_path($this->time_of_dir);
        $target_filepath = self::get_cdr_pathname('hlkomm', $this->timestamp);

        if (! is_dir($target_dir)) {
            mkdir($target_dir, 0744, true);
        }

        if (ftp_get($ftp_conn, $target_filepath, $remote_fname, FTP_BINARY)) {
            \ChannelLog::debug('billing', 'Successfully stored HlKomm CDR', [$target_filepath]);
            echo "New file: $target_filepath\n";
        } else {
            \ChannelLog::error('billing', 'Could not retrieve CDR file from ftp server', [__FUNCTION__]);

            return -1;
        }

        ftp_close($ftp_conn);
    }

    /**
     * Load Call Data Records from PURTel Interface and save to accounting directory of appropriate date
     *
     * @return int 		0 on success, -1 on error
     */
    private function _load_purtel_cdr()
    {
        \ChannelLog::debug('billing', 'Load PURTel Call Data Records');

        $user = env('PURTEL_RESELLER_USERNAME');
        $password = env('PURTEL_RESELLER_PASSWORD');

        $from = date('Ym01', $this->time_of_file);
        $to = date('Ymd', strtotime('last day of', $this->time_of_file));

        $url = "https://ipcom.purtel.com/index.php?super_passwort=$password&evn_bis=$to&super_username=$user&erweitert=1&action=getcdr&lastid=1&evn_von=$from";

        try {
            \ChannelLog::debug('billing', "GET $url");
            $data = file_get_contents($url);
        } catch (\Exception $e) {
            \ChannelLog::alert('billing', 'CDR-Import: Could not get Call Data Records from PURTel for month: '.date('m', $this->time_of_file));

            return -1;
        }

        $target_dir = accountingCommand::get_absolute_accounting_dir_path($this->time_of_dir);
        $target_filepath = self::get_cdr_pathname('purtel', $this->timestamp);

        if (! is_dir($target_dir)) {
            mkdir($target_dir, 0744, true);
        }

        \File::put($target_filepath, $data);
        echo "New file: $target_filepath\n";

        \ChannelLog::debug('billing', "Successfully stored PURTel Call Data Records in $target_filepath");
    }

    /**
     * Get the console command arguments / options
     *
     * @return array
     */
    protected function getArguments()
    {
        // return [
            // ['date', InputArgument::OPTIONAL, "e.g.: '2018-02-01' or '2018-02'"],
        // ];
    }

    /**
     * NOTE:
     * If date is specified all records of the dates month are saved to the output-file
     * Without a date all records from last month or rather some months before are saved to output-file
     * dependent on CDR-to-Invoice time offset/difference in months specified in BillingBase's global config
     */
    protected function getOptions()
    {
        return [
            ['date', null, InputOption::VALUE_OPTIONAL, "e.g.: '2018-02-01' or '2018-02'", null],
        ];
    }
}
