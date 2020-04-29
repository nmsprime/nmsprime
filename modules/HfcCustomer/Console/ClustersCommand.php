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
        $perf = '';
        foreach (NetElement::withActiveModems()->get() as $netelement) {
            $state = ModemHelper::ms_state($netelement);
            if ($state == -1) {
                continue;
            }

            if ($state == 'CRITICAL' || ($state == 'WARNING' && $ret == 'OK')) {
                $ret = $state;
            }

            $num = $netelement->modems_online_count;
            $numa = $netelement->modems_count;
            $cm_cri = $netelement->modems_critical_count;
            $avg = $netelement->modemsUsPwrAvg;
            $warn_per = ModemHelper::$avg_warning_percentage / 100 * $numa;
            $crit_per = ModemHelper::$avg_critical_percentage / 100 * $numa;
            $warn_us = ModemHelper::$avg_warning_us;
            $crit_us = ModemHelper::$avg_critical_us;

            $perf .= "'$netelement->name'=$num;$warn_per;$crit_per;0;$numa ";
            $perf .= "'$netelement->name ($avg dBuV, #crit:$cm_cri)'=$avg;$warn_us;$crit_us ";
        }
        echo $ret.' | '.$perf;

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
