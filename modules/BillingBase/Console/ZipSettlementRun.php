<?php

namespace Modules\BillingBase\Console;

use Storage;
use Illuminate\Console\Command;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\BillingBase\Entities\SettlementRun;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\BillingBase\Entities\SettlementRunZipper;

class ZipSettlementRun extends Command
{
    /**
     * The console command & table name
     *
     * @var string
     */
    public $name = 'billing:zip';
    protected $description = 'Build Zip file with all relevant Accounting Files of specified SettlementRun';
    protected $signature = 'billing:zip {sepaacc_id?} {--settlementrun=} {--postal-invoices}';

    /**
     * @var object  SettlementRun the ZIP shall be built for
     */
    private $settlementrun;

    /**
     * @var bool    If true -> concatenate invoices to single PDF that need to be sent via post
     */
    private $postalInvoices;

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
     * Execute the console command
     *
     * Package all Files in SettlementRun's accounting files directory
     * NOTE: this Command is called in SettlementRunCommand!
     *
     * @author Nino Ryschawy
     */
    public function handle()
    {
        $conf = BillingBase::first();
        \App::setLocale($conf->userlang);

        $settlementrun = $this->getRelevantSettlementRun();

        if (! $settlementrun) {
            $msg = 'Could not find SettlementRun for last month or wrong SettlementRun ID specified!';
            \ChannelLog::error('billing', $msg);
            echo "$msg\n";

            return;
        }

        $zipper = new SettlementRunZipper($settlementrun, $this->output);

        if ($this->option('postal-invoices')) {
            $zipper->fire(null, true);
        } else {
            $sepaacc = null;
            if ($this->argument('sepaacc_id')) {
                $sepaacc = SepaAccount::find($this->argument('sepaacc_id'));
            }

            $zipper->fire($sepaacc);
        }

        system('chmod -R 0700 '.$settlementrun->directory);
        system('chown -R apache '.$settlementrun->directory);
        Storage::delete('tmp/accCmdStatus');
    }

    private function getRelevantSettlementRun()
    {
        if ($this->option('settlementrun')) {
            return SettlementRun::find($this->option('settlementrun'));
        }

        // Default
        $time = strtotime('last month');

        return SettlementRun::where('month', date('m', $time))->where('year', date('Y', $time))->orderBy('id', 'desc')->first();
    }

    /**
     * Get the console command arguments / options
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            // ['cycle', InputArgument::OPTIONAL, '1 - without TV, 2 - only TV'],
        ];
    }

    protected function getOptions()
    {
        return [
            // ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
