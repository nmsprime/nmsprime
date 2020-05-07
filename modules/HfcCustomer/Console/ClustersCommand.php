<?php

namespace Modules\HfcCustomer\Console;

use Illuminate\Console\Command;
use Modules\HfcReq\Entities\NetElement;
use Modules\HfcCustomer\Entities\Utility\ModemStateAnalysis;

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
     * All netelements with active modems as childrem in their tree
     *
     * @var Illuminate\Database\Eloquent\Collection
     */
    protected $netelements;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->netelements = NetElement::withActiveModems()->get();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $status = 'OK';
        $output = '';
        $lookup = [
            'OK' => 0,
            'WARNING' => 1,
            'CRITICAL' => 2,
        ];

        foreach ($this->netelements as $netelement) {
            if ($netelement->modems_count == 0) {
                continue;
            }

            $modemStateAnalysis = new ModemStateAnalysis($netelement->modems_online_count, $netelement->modems_count, $netelement->modemsUsPwrAvg);
            $modemPercentage = $netelement->modems_online_count / $netelement->modems_count * 100;
            $warn_per = config('hfccustomer.threshhold.avg.percentage.warning') / 100 * $netelement->modems_count;
            $crit_per = config('hfccustomer.threshhold.avg.percentage.critical') / 100 * $netelement->modems_count;
            $warn_us = config('hfccustomer.threshhold.avg.us.warning');
            $crit_us = config('hfccustomer.threshhold.avg.us.critical');

            if ($this->option('output') === 'online' || $this->option('output') === 'all') {
                if (
                    $modemPercentage < config('hfccustomer.threshhold.avg.percentage.critical') ||
                    ($modemPercentage < config('hfccustomer.threshhold.avg.percentage.warning') && $status == 'OK')
                ) {
                    $status = $modemStateAnalysis->get();
                }
                $output .= "'$netelement->name'=$netelement->modems_online_count;$warn_per;$crit_per;0;$netelement->modems_count ";
            }

            if ($this->option('output') === 'power' || $this->option('output') === 'all') {
                if (
                    $netelement->modemsUsPwrAvg > $crit_us ||
                    ($netelement->modemsUsPwrAvg > $warn_us && $status == 'OK')
                ) {
                    $status = $modemStateAnalysis->get();
                }

                $output .= "'$netelement->name ($netelement->modemsUsPwrAvg dBuV, #crit:$netelement->modems_critical_count)'=$netelement->modemsUsPwrAvg;$warn_us;$crit_us ";
            }
        }

        $this->line("$status | $output");

        return $lookup[$status];
    }
}
