<?php

namespace Modules\HfcCustomer\Console;

use Illuminate\Console\Command;
use Modules\HfcReq\Entities\NetElement;
use Modules\HfcCustomer\Entities\ModemHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ClustersCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nms:clusters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get modem summary of all clusters';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nms:clusters {--o|output=all : What information should be returned. Available options are online|power|all}';

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
     * @return mixed
     */
    public function handle()
    {
        $ret = 'OK';
        $output = '';

        foreach (NetElement::withActiveModems()->get() as $netelement) {
            $state = ModemHelper::ms_state($netelement);
            if ($state == -1) {
                continue;
            }

            if ($state == 'CRITICAL' || ($state == 'WARNING' && $ret == 'OK')) {
                $ret = $state;
            }

            $warn_per = ModemHelper::$avg_warning_percentage / 100 * $netelement->modems_count;
            $crit_per = ModemHelper::$avg_critical_percentage / 100 * $netelement->modems_count;
            $warn_us = ModemHelper::$avg_warning_us;
            $crit_us = ModemHelper::$avg_critical_us;

            if ($this->option('output') === 'online' || $this->option('output') === 'all') {
                $output .= "'$netelement->name'=$netelement->modems_online_count;$warn_per;$crit_per;0;$netelement->modems_count ";
            }

            if ($this->option('output') === 'power' || $this->option('output') === 'all') {
                $output .= "'$netelement->name ($netelement->modemsUsPwrAvg dBuV, #crit:$netelement->modems_critical_count)'=$netelement->modemsUsPwrAvg;$warn_us;$crit_us ";
            }
        }

        $this->line("$ret | $output");

        if ($ret == 'CRITICAL') {
            return 2;
        }
        if ($ret == 'WARNING') {
            return 1;
        }

        return 0;
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
            // ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
