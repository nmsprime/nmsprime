<?php

namespace Modules\BillingBase\Console;

use Illuminate\Console\Command;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\SettlementRun;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\BillingBase\Providers\SettlementRunData;

class SettlementRunCommand extends Command
{
    /**
     * The console command & table name, description, data arrays
     *
     * @var string
     */
    public $name = 'billing:settlementrun';
    protected $description = 'Execute/Create SettlementRun - create Direct Debit/Credit XML, invoices and accounting/booking record files';
    protected $signature = 'billing:settlementrun {acc?}';

    /**
     * Execute the console command
     *
     * Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
     */
    public function handle()
    {
        $sr = $this->getOrCreateSettlementRun();
        $sepaacc = $this->argument('acc') ? SepaAccount::findOrFail($this->argument('acc')) : null;

        $sr->execute($sepaacc, $this->output);

        system('chmod -R 0700 '.$sr->directory);
        system('chown -R apache '.$sr->directory);
    }

    /**
     * Create new SettlementRun model or get it from database when command is executed from command line
     */
    private function getOrCreateSettlementRun()
    {
        $dates = SettlementRunData::getDate();
        $sr = SettlementRun::where('year', '=', $dates['Y'])->where('month', '=', (int) $dates['lastm'])->orderBy('id', 'desc')->first();

        if (! $sr || ! $sr->getAttribute('id')) {
            $sr = new SettlementRun;
            // Disable observer to not queue this command again - Note: Disable via observer_enable=false doesn't work
            $sr->flushEventListeners();
            $sr = $sr->create(['year' => $dates['Y'], 'month' => SettlementRunData::getDate('lastm')]);
            // Enable observer again
            $sr->observe(new \Modules\BillingBase\Entities\SettlementRunObserver);
        }

        return $sr;
    }

    /**
     * Get the console command arguments / options
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['acc', InputArgument::OPTIONAL, 'SEPA-Account-ID: Run command for Specific account'],
        ];
    }

    protected function getOptions()
    {
        return [
            // array('debug', null, InputOption::VALUE_OPTIONAL, 'Print Debug Output to Commandline (1 - Yes, 0 - No (Default))', 0),
        ];
    }
}
