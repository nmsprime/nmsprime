<?php

namespace Modules\BillingBase\Console;

use File;
use Illuminate\Console\Command;
use Modules\BillingBase\Entities\CdrGetter;
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
    protected $signature = 'billing:cdr {--date=} {--sr=}';

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
     *
     * TODO: Just execute the get() functions of the new {Provider}CdrController classes from the Note of function
     *      SettlementRunCommand::_get_cdr_data()
     */
    public function handle()
    {
        \ChannelLog::debug('billing', 'Get Call Data Records');

        $arg = null;

        if ($this->option('date')) {
            $arg = $this->option('date');
        } elseif ($this->option('sr')) {
            $arg = $this->option('sr');
        }

        CdrGetter::get($arg);

        system('chown -R apache '.storage_path('app/data/billingbase/'));
        system('chown -R apache '.storage_path('app/tmp/'));
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
            ['sr', null, InputOption::VALUE_OPTIONAL, 'SettlementRun-ID', null],
        ];
    }
}
