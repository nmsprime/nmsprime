<?php

namespace Modules\OverdueDebts\Console;

use ChannelLog;
use Illuminate\Console\Command;
use Modules\OverdueDebts\Entities\Debt;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ImportDebtCommand extends Command
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
     * @var object Global overdue debts config
     */
    private $conf;

    /**
     * @var array Entries for log messages
     */
    private $blocked = [];
    private $errors = [];

    /**
     * CSV column position definitions
     */
    const C_NR = 0;
    const VOUCHER_NR = 4;
    const DATE = 5;
    const AMOUNT = 7;
    const MISSING_AMOUNT = 9;
    const DESC = 10;
    const DUN_DATE = 11;
    const INDICATOR = 12;

    /**
     * Execute the console command
     *
     * Create Invoices, Sepa xml file(s), Accounting and Booking record file(s)
     */
    public function handle()
    {
        $arr = file($this->argument('file'));

        unset($arr[0]);

        $num = count($arr);
        if (! $num) {
            $this->error("Empty file\n");

            return;
        }

        $this->conf = \Modules\OverdueDebts\Entities\OverdueDebts::first();

        Debt::where('id', '>', 0)->withTrashed()->forceDelete();

        // Output
        $bar = $this->output->createProgressBar($num);
        echo "Import overdue debts\n";
        \Log::info("Import $num overdue debts");
        $bar->start();

        foreach ($arr as $line) {
            $bar->advance();
            $this->block = false;
            $line = str_getcsv($line, ';');

            $this->currentContract = \Modules\ProvBase\Entities\Contract::where('number', $line[self::C_NR])->first();

            if (! $this->currentContract) {
                $this->errors[] = $line[self::C_NR];

                continue;
            }

            $debt = $this->addDebt($line);

            //  Check & block internet access
            $this->blockInet($debt);
        }

        $bar->finish();
        echo "\n";

        $this->log();
    }

    /**
     * @param array line
     */
    private function addDebt($line)
    {
        $fee = $indicator = 0;
        if ($line[self::INDICATOR] > 0 && $line[self::INDICATOR] < 4) {
            $indicator = $line[self::INDICATOR];
            $fee = $this->conf->{'dunning_charge'.$indicator};
        } elseif ($line[self::INDICATOR] > 4) {
            $indicator = 4;
        }

        $debt = Debt::create([
            'contract_id' => $this->currentContract->id,
            'voucher_nr' => $line[self::VOUCHER_NR],
            // TODO
            // 'number' => $line[self::INVOICE_NR],
            'amount' => str_replace(',', '.', $line[self::AMOUNT]),
            'missing_amount' => str_replace(',', '.', $line[self::MISSING_AMOUNT]) + $fee,
            'date' => date('Y-m-d', strtotime($line[self::DATE])),
            'dunning_date' => $line[self::DUN_DATE] ? date('Y-m-d', strtotime($line[self::DUN_DATE])) : null,
            'description' => $line[self::DESC],
            'indicator' => $indicator,
            'total_fee' => $fee,
        ]);

        return $debt;
    }

    private function blockInet($debt)
    {
        if (// Block if threshhold is exceeded
            ($this->currentContract->getResultingDebt() >= $this->conf->import_inet_block_amount) ||
            // Block if more than num OPs
            ($this->currentContract->debts()->count() >= $this->conf->import_inet_block_debts) ||
            // Block if dunning indicator is too high
            ($debt->indicator >= $this->conf->import_inet_block_indicator)
        ) {
            foreach ($this->currentContract->modems as $modem) {
                $modem->internet_access = 0;

                $this->blocked[] = $this->currentContract->number;

                $modem->save();
            }
        }

    }

    private function log()
    {
        // Log contracts that could not be found
        if ($this->errors) {
            $msg = trans('overduedebts::messages.import.contractsMissing', ['numbers' => implode(', ', $this->errors)]);
            ChannelLog::warning('overduedebts', $msg);
            $this->error($msg);
        }

        // Log contracts where internet access was blocked during import
        if ($this->blocked) {
            $msg = trans('overduedebts::messages.import.contractsBlocked', ['numbers' => implode(', ', $this->blocked)]);
            ChannelLog::info('overduedebts', $msg);
            $this->info($msg);
        }
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
