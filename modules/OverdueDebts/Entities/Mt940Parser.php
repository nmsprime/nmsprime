<?php

namespace Modules\OverdueDebts\Entities;

use ChannelLog;
use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\SettlementRun;

class Mt940Parser
{
    protected $output;

    public function __construct($output = null)
    {
        $this->output = $output;
    }

    /**
     * Parse an uploaded SWIFT-/Mt940.sta bank transaction file and assign Debts from it to appropriate contracts
     *
     * @param string
     */
    public function parse($filepath, $voucherNr)
    {
        $contracts = $contractsSpecial = [];
        $parser = new \Kingsquare\Parser\Banking\Mt940();

        $mt940 = file_get_contents($filepath);
        $transactionParser = new TransactionParser($mt940);

        $parseInfo = trans('overduedebts::messages.parse.start');
        if ($this->output) {
            echo "$parseInfo\n";
        } else {
            SettlementRun::push_state(0, $parseInfo);
        }

        // Handle wrong file format
        try {
            $statements = $parser->parse($mt940);
        } catch (\Exception $e) {
            ChannelLog::error('overduedebts', trans('overduedebts::messages.parseMt940Failed', ['msg' => $e->getMessage()]).' In: '.$e->getFile());

            return;
        }

        $num = 0;
        foreach ($statements as $statement) {
            $num += count($statement->getTransactions());
        }

        $parseInfo = trans('overduedebts::messages.parse.transactions');

        if ($this->output) {
            echo "$parseInfo\n";
            $bar = $this->output->createProgressBar($num);
            $bar->start();
        } else {
            SettlementRun::push_state(0, $parseInfo);
            $i = 0;
        }

        foreach ($statements as $statement) {
            foreach ($statement->getTransactions() as $transaction) {
                $debt = new Debt;
                $debt->voucher_nr = $voucherNr;

                $debt = $transactionParser->parse($transaction, $debt);

                if ($this->output) {
                    $bar->advance();
                } else {
                    $i++;
                    if (! ($i % 10)) {
                        SettlementRun::push_state((int) $i / $num * 100, $parseInfo);
                    }
                }

                // only for analysis during development!
                // $transactions[$transaction->getDebitCredit()][] = ['price' => $transaction->getPrice(), 'code' => $transaction->getTransactionCode(), 'description' => explode('?', $transaction->getDescription())];

                if (! $debt) {
                    continue;
                }

                $debt->save();

                if ($debt->addedBySpecialMatch) {
                    $contractsSpecial[] = $debt->contract_id;
                } else {
                    $contracts[] = $debt->contract_id;
                }
            }
        }

        if ($this->output) {
            $bar->finish();
            echo "\n";
        } else {
            SettlementRun::push_state(100, 'Finished');
        }

        // Summary log messages
        if ($contracts) {
            $numbers = Contract::whereIn('id', $contracts)->pluck('number')->all();
            ChannelLog::info('overduedebts', trans('overduedebts::messages.addedDebts', ['count' => count($numbers), 'numbers' => implode(', ', $numbers)]));
        }

        if ($contractsSpecial) {
            $numbers = Contract::whereIn('id', $contractsSpecial)->pluck('number')->all();
            ChannelLog::notice('overduedebts', trans('overduedebts::messages.transaction.credit.noInvoice.special', ['numbers' => implode(', ', $numbers)]));
        }

        // d($transactions, $statements, str_replace(':61:', "\r\n---------------\r\n:61:", $mt940));
    }

    /**
     * @param array
     */
    public static function addDebtButton($data)
    {
        if (isset($data['_token'])) {
            unset($data['_token']);
        }

        ksort($data);

        $html = '';
        $html = '<button class="btn btn-link btn-xs addDebt" type="submit" value=\''.json_encode($data).'\'>'.
        $html .= trans('overduedebts::view.debt.add').'</button>';

        return $html;
    }

    public static function addedDebtInfo()
    {
        // TODO?: Show link to added debt
        // return '<a href='.route('Debt.edit', $debtId).' class="label label-info">'.trans("overduedebts::view.debt.added").'</a>';
        return '<span class="label label-info">'.trans('overduedebts::view.debt.added').'</span>';
    }
}
