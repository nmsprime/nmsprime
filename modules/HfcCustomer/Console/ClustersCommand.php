<?php

namespace Modules\HfcCustomer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\HfcReq\Entities\NetElement;
use Modules\HfcCustomer\Helpers\ModemStateAnalysis;

class ClustersCommand extends Command
{
    /**
     * Warning upstream threshhold that is read out from config.
     *
     * @var int
     */
    public $warningUsThreshhold;

    /**
     * Critical upstream threshhold that is read out from config.
     *
     * @var int
     */
    public $criticalUsThreshhold;

    /**
     * Warning offline threshhold that is read out from config.
     *
     * @var int
     */
    public $warningPercentage;

    /**
     * critical offline threshhold that is read out from config.
     *
     * @var int
     */
    public $criticalPercentage;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $perfData = [];

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
    protected $signature = 'nms:clusters
        {--o|output=all : What information should be returned. Available options are online|power|all}';

    public $lookup = [
        'OK' => 0,
        'WARNING' => 1,
        'CRITICAL' => 2,
    ];

    public function __construct()
    {
        $this->warningUsThreshhold = config('hfccustomer.threshhold.avg.us.warning');
        $this->criticalUsThreshhold = config('hfccustomer.threshhold.avg.us.critical');
        $this->warningPercentage = config('hfccustomer.threshhold.avg.percentage.warning');
        $this->criticalPercentage = config('hfccustomer.threshhold.avg.percentage.critical');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (NetElement::withActiveModems()->get() as $netelement) {
            if ($netelement->modems_count == 0) {
                continue;
            }

            $modemStateAnalysis = new ModemStateAnalysis($netelement->modems_online_count, $netelement->modems_count, $netelement->modemsUsPwrAvg);

            if ($this->option('output') === 'online' || $this->option('output') === 'all') {
                $warningThreshhold = $this->warningPercentage / 100 * $netelement->modems_count;
                $criticalThreshhold = $this->criticalPercentage / 100 * $netelement->modems_count;
                $modemPercentage = $netelement->modems_online_count / $netelement->modems_count * 100;
                $status = $this->isPercentageWarningOrCritical($modemPercentage) ? $modemStateAnalysis->getOnline() : 'OK';

                $perfData[] = "{$status}|'{$netelement->id}_{$netelement->name}'={$netelement->modems_online_count};{$warningThreshhold};{$criticalThreshhold};0;{$netelement->modems_count}\n";
            }

            if ($this->option('output') === 'power' || $this->option('output') === 'all') {
                $status = $this->isPowerWarningOrCritical($netelement->modemsUsPwrAvg) ? $modemStateAnalysis->getPower() : 'OK';

                $perfData[] = "{$status}|'{$netelement->id}_{$netelement->name} ({$netelement->modemsUsPwrAvg} dBuV, #crit:{$netelement->modems_critical_count})'={$netelement->modemsUsPwrAvg};{$this->warningUsThreshhold};{$this->criticalUsThreshhold}\n";
            }
        }

        Storage::disk('tempfs')->put("icinga2/clusters_{$this->option('output')}.csv", $perfData);
    }

    /**
     * Determine the state of the cluster/bubble. To save some resources the
     * check should only run, when Percentage is lower than warning.
     *
     * @param int $percentage
     * @return bool
     */
    protected function isPercentageWarningOrCritical(int $percentage): bool
    {
        return $percentage <= $this->warningPercentage;
    }

    /**
     * Determine the state of the cluster/bubble. To save some resources the
     * check should only run, when UsPwr is higher than warning.
     *
     * @param int $usPowerAvg
     * @return bool
     */
    protected function isPowerWarningOrCritical(int $usPowerAvg): bool
    {
        return $usPowerAvg >= $this->warningUsThreshhold;
    }
}
