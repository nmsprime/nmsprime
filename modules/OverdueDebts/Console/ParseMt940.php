<?php

namespace Modules\OverdueDebts\Console;

use Illuminate\Console\Command;
use Modules\OverdueDebts\Entities\Mt940Parser;
use Modules\BillingBase\Entities\SettlementRun;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ParseMt940 extends Command
{
    /**
     * The console command & table name, description
     *
     * @var string
     */
    public $name = 'debt:parse';
    protected $description = 'Parse MT940 SWIFT bank transaction file (.sta)';
    protected $signature = 'debt:parse {file} {voucherNr}';

    /**
     * Execute the console command
     *
     * Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
     */
    public function handle()
    {
        SettlementRun::orderBy('id', 'desc')->first()->update(['uploaded_at' => date('Y-m-d H:i:s')]);

        $parser = new Mt940Parser($this->output);

        $parser->parse($this->argument('file'), $this->argument('voucherNr'));
    }

    /**
     * Get the console command arguments / options
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['file', InputArgument::REQUIRED, 'Filepath of .sta containing the bank transactions'],
        ];
    }

    protected function getOptions()
    {
        return [
            // array('debug', null, InputOption::VALUE_OPTIONAL, 'Print Debug Output to Commandline (1 - Yes, 0 - No (Default))', 0),
        ];
    }
}
