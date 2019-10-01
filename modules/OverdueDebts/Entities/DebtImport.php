<?php

namespace Modules\OverdueDebts\Entities;

use ChannelLog;
use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\SettlementRun;

class DebtImport
{
    /**
     * @var object Output interface to command line & progress bar
     */
    public $output;
    private $bar;

    /**
     * @var string Path to csv file
     */
    private $path;

    /**
     * @var object Global overdue debts config
     */
    private $conf;

    /**
     * @var array contract numbers that could not be found
     */
    private $notFound = [];

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

    public function __construct($path, $output = null)
    {
        $this->path = $path;
        $this->output = $output;
    }

    /**
     * Import overdue debts from financial accounting software csv file
     */
    public function run()
    {
        SettlementRun::orderBy('id', 'desc')->first()->update(['uploaded_at' => date('Y-m-d H:i:s')]);
        $this->conf = \Modules\OverdueDebts\Entities\OverdueDebts::first();

        $this->addDebts();

        $contracts = $this->getContracts();

        $this->addDunningCharge($contracts);
        $this->blockInet($contracts);

        $this->log();
    }

    /**
     * Create all debts from each line in CSV
     */
    private function addDebts()
    {
        $arr = file($this->path);

        // Remove headline if exists
        if (! preg_match('/\d/', $arr[0][0])) {
            unset($arr[0]);
        }

        $num = count($arr);
        if (! $num) {
            $msg = 'Empty file';
            ChannelLog::error('overduedebts', $msg);
            if ($this->output) {
                $this->output->error("$msg\n");
            }

            exit(-1);
        }

        Debt::truncate();

        // Output
        $importInfo = trans('overduedebts::messages.import.count', ['number' => $num]);
        ChannelLog::info('overduedebts', $importInfo);
        if ($this->output) {
            $bar = $this->output->createProgressBar($num);
            echo "Import overdue debts\n";
            $bar->start();
        }

        foreach ($arr as $i => $line) {
            if ($this->output) {
                $bar->advance();
            } else {
                SettlementRun::push_state((int) $i / $num * 100, $importInfo);
            }

            $this->block = false;
            $line = str_getcsv($line, ';');

            $contract = Contract::where('number', $line[self::C_NR])->first();

            if (! $contract) {
                $this->notFound[] = $line[self::C_NR];

                continue;
            }

            $debt = $this->addDebt($line, $contract);
        }

        if ($this->output) {
            $bar->finish();
            echo "\n";
        } else {
            SettlementRun::push_state(100, $importInfo);
            // SettlementRun::push_state(100, 'Finished');
        }
    }

    /**
     * @param array line
     */
    private function addDebt($line, $contract)
    {
        // TODO: Only consider invoice numbers that match NMSPrime format - but therefore there must be another column in CSV
        // preg_match('/2\d{3}\/\d+\/\d+/i', $this->description, $matchInvoice);

        $indicator = null;
        if ($line[self::INDICATOR] > 0 && $line[self::INDICATOR] <= 3) {
            // Add dunning charge to each debt separately - kept here in case it's needed again in future
            // $fee = $this->conf->{'dunning_charge'.$indicator};

            $indicator = $line[self::INDICATOR];
        } elseif ($line[self::INDICATOR] > 3) {
            $indicator = 3;
        }

        $debt = Debt::create([
            'contract_id' => $contract->id,
            'voucher_nr' => $line[self::VOUCHER_NR],
            // TODO
            // 'number' => $line[self::INVOICE_NR],
            'amount' => str_replace(',', '.', $line[self::AMOUNT]),
            'missing_amount' => str_replace(',', '.', $line[self::MISSING_AMOUNT]),
            'date' => date('Y-m-d', strtotime($line[self::DATE])),
            'dunning_date' => $line[self::DUN_DATE] ? date('Y-m-d', strtotime($line[self::DUN_DATE])) : null,
            'description' => $line[self::DESC],
            'indicator' => $indicator,
            // 'total_fee' => $fee,
        ]);

        return $debt;
    }

    /**
     * Get all contracts having minimum one debt
     *
     * @return Collection
     */
    private function getContracts()
    {
        $msg = trans('overduedebts::messages.import.block');
        if ($this->output) {
            echo "$msg\n";
            $this->bar = $this->output->createProgressBar(100);
            $this->bar->start();
        } else {
            SettlementRun::push_state(0, $msg);
        }

        return Contract::join('debt', 'contract.id', '=', 'debt.contract_id')
            ->select('contract.*')
            ->groupBy('contract.number')
            ->with('debts')
            ->get();
    }

    private function addDunningCharge($contracts)
    {
        if ($this->output) {
            $this->bar->advance(33);
        } else {
            SettlementRun::push_state(33, trans('overduedebts::messages.import.block'));
        }

        // Add dunning charge only once for a contract with debts to the debt with the highest dunning indicator
        foreach ($contracts as $c) {
            $debt = $c->debts->sortByDesc('indicator')->first();

            // Check if there are multiple debts with the same indicator -> take first added then
            if ($c->debts->where('indicator', $debt->indicator)->count() > 1) {
                $debt = $c->debts->where('indicator', $debt->indicator)->sortBy('date')->first();
            }

            $debt->total_fee += $this->conf->{'dunning_charge'.$debt->indicator};

            $debt->save();
        }
    }

    /**
     * Block internet access of customers where one of the thresholds are exceeded
     *  (e.g. Amount >= 50, Num positive debts >= 2, dunning indicator = 3)
     */
    private function blockInet($contracts)
    {
        if ($this->output) {
            $this->bar->advance(33);
        } else {
            SettlementRun::push_state(66, trans('overduedebts::messages.import.block'));
        }

        $blocked = [];

        foreach ($contracts as $c) {
            $posDebtCount = $c->debts->where('missing_amount', '>', 0)->count();

            $totalAmount = 0;
            foreach ($c->debts as $debt) {
                $totalAmount += $debt->missing_amount;
            }

            $highestIndicator = $c->debts->sortByDesc('indicator')->first()->indicator;

            if (// Amount threshold is exceeded
                ($this->conf->import_inet_block_amount && ($totalAmount >= $this->conf->import_inet_block_amount)) ||
                // More than max num of positive debts
                ($this->conf->import_inet_block_debts && ($posDebtCount >= $this->conf->import_inet_block_debts)) ||
                // Highest dunning indicator too high
                ($this->conf->import_inet_block_indicator && ($highestIndicator >= $this->conf->import_inet_block_indicator))
            ) {
                foreach ($c->modems as $modem) {
                    $blocked[] = $c->number;

                    $modem->internet_access = 0;
                    $modem->save();
                }
            }
        }

        if ($this->output) {
            $this->bar->finish();
            echo "\n";
        } else {
            SettlementRun::push_state(100, 'Finished');
        }

        // Log contracts where internet access was blocked during import
        if ($blocked) {
            $msg = trans('overduedebts::messages.import.contractsBlocked', ['count' => count($blocked), 'numbers' => implode(', ', $blocked)]);
            ChannelLog::info('overduedebts', $msg);
            if ($this->output) {
                $this->output->note($msg);
            }
        }
    }

    private function log()
    {
        // Log contracts that could not be found
        if ($this->notFound) {
            $msg = trans('overduedebts::messages.import.contractsMissing', ['numbers' => implode(', ', $this->notFound)]);
            ChannelLog::warning('overduedebts', $msg);
            if ($this->output) {
                $this->output->error($msg);
            }
        }
    }
}
