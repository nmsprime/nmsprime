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
    protected $signature = 'debt:import {file}';

    /**
     * Execute the console command
     *
     * Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
     */
    public function handle()
    {
        $debtImport = new DebtImport($this->argument('file'), $this->output);

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

    protected function getOptions()
    {
        return [
            // array('debug', null, InputOption::VALUE_OPTIONAL, 'Print Debug Output to Commandline (1 - Yes, 0 - No (Default))', 0),
        ];
    }
}
