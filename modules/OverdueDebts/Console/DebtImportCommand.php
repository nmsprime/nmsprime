<?php

namespace Modules\OverdueDebts\Console;

use Illuminate\Console\Command;
use Modules\OverdueDebts\Entities\DebtImport;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DebtImportCommand extends Command
{
    /**
     * The console command & table name, description
     *
     * @var string
     */
    public $name = 'debt:import';

    protected $description = 'Import overdue debts from csv';

    protected $signature = 'debt:import {file}
        {--test : Only run as test and don\'t actually block internet access of customers}';

    /**
     * Execute the console command
     *
     * Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
     */
    public function handle()
    {
        $runAsTest = $this->option('test') ? true : false;

        $debtImport = new DebtImport($this->argument('file'), $runAsTest, $this->output);

        $debtImport->run();
    }

    /**
     * Get the console command arguments / options
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['file', InputArgument::REQUIRED, 'Filepath of CSV with data to import'],
        ];
    }
}
