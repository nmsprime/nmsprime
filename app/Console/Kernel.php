<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * NOTE: the withoutOverlapping() statement is just for security reasons
     * and should never be required. But if a task hangs up, this will
     * avoid starting many parallel tasks. (Torsten Schmidt)
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /* $schedule->command('inspire') */
        /* 		 ->hourly(); */

        // comment the following in to see the time shifting behaviour of the scheduler;
        // watch App\Console\Commands\TimeDeltaChecker for more informations
        /* $schedule->command('main:time_delta') */
        /* ->everyMinute(); */

        // define some helpers
        $is_first_day_of_month = (date('d') == '01') ? true : false;

        // calculate an offset that can be used to time-shift cron commands
        // (e.g. to distribute load on external APIs)
        // use like: ->dailyAt(date("H:i", strtotime("04:04 + $time_offset min")));
        $key = getenv('APP_KEY') ?: 'n/a';
        $hash = sha1($key);
        $subhash = substr($hash, -2);   // [00..ff] => [0..255]
        $time_offset = hexdec($subhash) % 32;    // offset in [0..31] minutes

        $schedule->command('queue:checkup')->everyMinute();

        // Remove all Log Entries older than 90 days
        $schedule->call('\App\GuiLog@cleanup')->weekly();

        // Parse News from repo server and save to local JSON file
        if (\Module::collections()->has('Dashboard')) {
            $schedule->call('\Modules\Dashboard\Http\Controllers\DashboardController@newsLoadToFile')->hourly();
        }

        // Command to remove obsolete data in storage
        $schedule->command('main:storage_cleaner')->dailyAt('04:18');

        if (\Module::collections()->has('ProvVoip')) {

            // Update database table carriercode with csv data if necessary
            $schedule->command('provvoip:update_carrier_code_database')
                ->dailyAt('04:23');

            // Update database table ekpcode with csv data if necessary
            $schedule->command('provvoip:update_ekp_code_database')
                ->dailyAt('04:28');

            // Update database table trcclass with csv data if necessary
            $schedule->command('provvoip:update_trc_class_database')
                ->dailyAt('04:33');
        }

        if (\Module::collections()->has('ProvVoipEnvia')) {

            // Update status of envia orders
            // Do this at the very beginning of a day
            $schedule->command('provvoipenvia:update_envia_orders')
                ->dailyAt(date('H:i', strtotime("04:04 + $time_offset min")));
            /* ->everyMinute(); */

            // Get envia TEL customer reference for contracts without this information
            $schedule->command('provvoipenvia:get_envia_customer_references')
                ->dailyAt(date('H:i', strtotime("01:13 + $time_offset min")));

            // Get/update envia TEL contracts
            $schedule->command('provvoipenvia:get_envia_contracts_by_customer')
                ->dailyAt(date('H:i', strtotime("01:18 + $time_offset min")));

            // Process envia TEL orders (do so after getting envia contracts)
            $schedule->command('provvoipenvia:process_envia_orders')
                ->dailyAt(date('H:i', strtotime("03:18 + $time_offset min")));

            // Get envia TEL contract reference for phonenumbers without this information or inactive linked envia contract
            // on first of a month: run in complete mode
            // do so after provvoipenvia:process_envia_orders as we need the old references there
            if ($is_first_day_of_month) {
                $tmp_cmd = 'provvoipenvia:get_envia_contract_references complete';
            } else {
                $tmp_cmd = 'provvoipenvia:get_envia_contract_references';
            }
            $schedule->command($tmp_cmd)
                ->dailyAt(date('H:i', strtotime("03:23 + $time_offset min")));

            // Update voice data
            // on first of a month: run in complete mode
            if ($is_first_day_of_month) {
                $tmp_cmd = 'provvoipenvia:update_voice_data complete';
            } else {
                $tmp_cmd = 'provvoipenvia:update_voice_data';
            }
            $schedule->command($tmp_cmd)
                ->dailyAt(date('H:i', strtotime("01:23 + $time_offset min")));
        }

        // ProvBase Schedules
        if (\Module::collections()->has('ProvBase')) {
            // Rebuid all Configfiles
            // $schedule->command('nms:configfile')->dailyAt('00:50')->withoutOverlapping();

            // Reload DHCP on clock change (daylight saving)
            // [0] minute, [1] hour, [2] day, [3] month, [4] day of week, [5] year
            $day1 = date('d', strtotime('last sunday of march'));
            $day2 = date('d', strtotime('last sunday of oct'));
            $schedule->command('nms:dhcp')->cron("0 4 $day1 3 0");
            $schedule->command('nms:dhcp')->cron("0 4 $day2 10 0");

            // Contract - network access, item dates, internet (qos) & voip tariff changes
            // important!! daily conversion has to be run BEFORE monthly conversion
            // commands within one call of “artisan schedule:run” should be processed sequentially (AFAIR)
            // but to force the order we add runtimes: ten minutes difference should be more than enough
            // TODO: ckeck if this is really needed
            $schedule->command('nms:contract daily')->daily()->at('00:03');
            $schedule->command('nms:contract monthly')->monthly()->at('00:13');
            $schedule->call(function () {
                foreach (\Modules\ProvBase\Entities\NetGw::where('type', 'cmts')->get() as $cmts) {
                    $cmts->store_us_snrs();
                }

                foreach (\Modules\ProvBase\Entities\NetGw::where('type', 'olt')->where('ssh_auto_prov', '1')->get() as $olt) {
                    $olt->runSshAutoProv();
                }
            })->everyFiveMinutes();

            // refresh the online state of all PPP device
            $schedule->call('\Modules\ProvBase\Entities\Modem@refreshPPP')->everyFiveMinutes();

            // update firmware version + model strings of all modems once a day
            $schedule->call('\Modules\ProvBase\Entities\Modem@update_model_firmware')->daily();

            // Hardware support check for modems and CMTS
            $schedule->command('nms:hardware-support')->twiceDaily(10, 14);
        }

        // Automatic Power Control based on measured SNR
        if (\Module::collections()->has('HfcReq')) {
            $schedule->command('nms:agc')->everyMinute();
        }

        // Clean Up of HFC Base
        if (\Module::collections()->has('HfcBase')) {
            $schedule->command('nms:icingadata')->cron('4-59/5 * * * *');

            // Rebuid all Configfiles
            $schedule->call(function () {
                \Storage::deleteDirectory(\Modules\HfcBase\Http\Controllers\TreeTopographyController::$path_rel);
                \Storage::deleteDirectory(\Modules\HfcBase\Http\Controllers\TreeErdController::$path_rel);
            })->hourly();
        }

        // Clean Up of HFC Customer
        if (\Module::collections()->has('HfcCustomer')) {
            // Rebuid all Configfiles
            $schedule->call(function () {
                \Storage::deleteDirectory(\Modules\HfcCustomer\Http\Controllers\CustomerTopoController::$path_rel);
            })->hourly();

            // Modem Positioning System
            // TODO: this can be removed in nmsprime > 2.6.0
            $schedule->command('nms:mps')->dailyAt('00:23');
        }

        if (\Module::collections()->has('ProvMon')) {
            $schedule->command('nms:cacti')->daily();
        } else {
            $schedule->call(function () {
                \Queue::push(new \Modules\ProvBase\Jobs\SetModemsOnlineStatusJob());
            })->everyFiveMinutes();
        }

        // TODO: improve
        $schedule->call(function () {
            exec('chown -R apache '.storage_path('logs'));
        })->dailyAt('00:01');

        // Create monthly Billing Files and reset flags
        if (\Module::collections()->has('BillingBase')) {
            // Remove all old CDRs & Invoices

            $schedule->call('\Modules\BillingBase\Helpers\BillingAnalysis@saveIncomeToJson')->dailyAt('00:07');
            $schedule->call('\Modules\BillingBase\Helpers\BillingAnalysis@saveContractsToJson')->hourly();
            $schedule->call('\Modules\BillingBase\Entities\Invoice@cleanup')->monthly();
            // Reset payed_month column for yearly charged items for january settlementrun (in february)
            $schedule->call(function () {
                \Modules\BillingBase\Entities\Item::where('payed_month', '!=', '0')->update(['payed_month' => '0', 'updated_at' => date('Y-m-d H:i:s')]);
                \Log::info('Reset all items payed_month flag to 0');
            })->cron('10 0 1 2 *');
        }

        if (\Module::collections()->has('ProvVoip')) {
            $schedule->command('provvoip:phonenumber')->daily()->at('00:13');
        }

        if (\Module::collections()->has('VoipMon')) {
            $schedule->command('voipmon:match_records')->everyFiveMinutes();
            $schedule->command('voipmon:delete_old_records')->daily();
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');

        $this->load(__DIR__.'/Commands');
    }
}
