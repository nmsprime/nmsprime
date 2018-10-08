<?php

namespace Modules\HfcCustomer\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ModemRefreshCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nms:modem-refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the modem realtime status values';

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
     * Execute the console command.
     *
     * TODO: This straight forward algorithm will work for at least 3000 Modems
     *       (max timeout for offline modem is 100ms, schedule cycle is 5min=300s)
     *
     * NOTE: This command is added to laravel scheduling api to refresh all modem states every 5min.
     *
     * @return mixed
     */
    public function fire()
    {
        // numbers of modems
        $count = \Modules\ProvBase\Entities\Modem::count();
        if ($count == 0) {
            return;
        }

        // Setup / Calculate speed for algorithm
        // NOTE: This is a worst-case calculation, which means the schedule cycle will be finished faster
        //       because there will be most of the modems online and answering faster than $timeout.
        $cycle = 300; // schedule cycle for all modems in seconds
        $reserve = 30; // security window (in seconds) for one 5min cycle
        $mtpm = round(($cycle - $reserve) / ($count), 4); // max transmit per modem
        $mmt = 0.5; // modem max timeout in seconds
        $sleep = 0; // sleep time between modem requests in seconds, this will be adapted if running with schedule parameter

        if ($mtpm > $mmt) {
            $timeout = $mmt;

            if ($this->option('schedule')) {
                $sleep = round($mtpm - $timeout, 4);
            }
        } else {
            $timeout = $mtpm;
        }

        // Log
        $bar = $this->output->createProgressBar($count);
        $before = microtime(true);
        $this->info('modem state refresh started');
        $this->info("time calculation result for $count modems: per modem snmp timeout: $timeout s, sleep: $sleep s");
        \Log::info('modem state refresh started');
        \Log::info("time calculation result for $count modems: per modem snmp timeout: $timeout s, sleep: $sleep s");

        // foreach modem
        foreach (\Modules\ProvBase\Entities\Modem::all() as $modem) {
            // Refresh Modem State
            // take last value from cacti (fast path)
            $res = $modem->refresh_state_cacti();
            // something went wrong using cacti -> fallback to snmp request
            if ($res == -1) {
                $res = $modem->refresh_state();
            }

            // Log / Debug
            if ($this->option('debug')) {
                if ($res) {
                    $this->info('id: '.$modem->id.' result '.implode(', ', $res));
                } else {
                    $this->error('id: '.$modem->id.' offline');
                }
            } else {
                $bar->advance();
            }

            usleep($sleep * 1000 * 1000);
        }

        // Log
        $after = microtime(true);
        $this->info("\n".'modem state refresh finished after '.round($after - $before, 2).' ys');
        \Log::info('modem state refresh finished after '.round($after - $before, 2).' ys');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            // ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['debug', null, InputOption::VALUE_OPTIONAL, 'debug on: show each entry with snmp result', false],
            ['schedule', null, InputOption::VALUE_OPTIONAL, 'call from schedule context, uses schedule time setting', false],
        ];
    }
}
