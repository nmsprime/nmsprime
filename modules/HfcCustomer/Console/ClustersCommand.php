<?php

namespace Modules\HfcCustomer\Console;

use Illuminate\Console\Command;
use Modules\HfcReq\Entities\NetElement;
use Modules\HfcCustomer\Helpers\ModemStateAnalysis;

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

        foreach (NetElement::withActiveModems()->get() as $netelement) {
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
                if ($this->isPercentageWarningOrCritical($modemPercentage, $status)) {
                    $status = $modemStateAnalysis->get();
                }
                $output .= "'{$netelement->id}_{$netelement->name}'={$netelement->modems_online_count};{$warn_per};{$crit_per};0;{$netelement->modems_count} ";
            }

            if ($this->option('output') === 'power' || $this->option('output') === 'all') {
                if ($this->isPowerWarningOrCritical($netelement->modemsUsPwrAvg, $status)) {
                    $status = $modemStateAnalysis->get();
                }

                $output .= "'{$netelement->id}_{$netelement->name} ({$netelement->modemsUsPwrAvg} dBuV, #crit:{$netelement->modems_critical_count})'={$netelement->modemsUsPwrAvg};{$warn_us};{$crit_us} ";
            }
        }

        $this->line("$status | $output");

        return $lookup[$status];
    }

    /**
     * Determine if state needs to change. State can always be 'CRITICAL', but
     * 'WARNING' can only occur when state was 'OK'. This is for the case when
     * the critical threshhold is higher than the warning threshhold.
     *
     * @param int $percentage
     * @param string $status
     * @return bool
     */
    protected function isPercentageWarningOrCritical(int $percentage, string $status): bool
    {
        return $percentage <= config('hfccustomer.threshhold.avg.percentage.critical') ||
            ($percentage <= config('hfccustomer.threshhold.avg.percentage.warning') && $status == 'OK');
    }

    /**
     * Determine if state needs to change. State can always be 'CRITICAL', but
     * 'WARNING' can only occur when state was 'OK'. This is for the case when
     * the warning threshhold is higher than the critical threshhold. (UsPwr)
     *
     * @param int $usPowerAvg
     * @param string $status
     * @return bool
     */
    protected function isPowerWarningOrCritical(int $usPowerAvg, string $status): bool
    {
        return $usPowerAvg >= config('hfccustomer.threshhold.avg.us.critical') ||
            ($usPowerAvg >= config('hfccustomer.threshhold.avg.us.warning') && $status == 'OK');
    }
}
