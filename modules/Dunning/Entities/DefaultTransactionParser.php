<?php

namespace Modules\Dunning\Entities;

use ChannelLog;
use Illuminate\Support\Str;
use Modules\ProvBase\Entities\Contract;
use Modules\BillingBase\Entities\Invoice;
use Modules\BillingBase\Entities\BillingBase;
use Modules\BillingBase\Entities\SepaMandate;

class DefaultTransactionParser
{
    /**
     * Transaction Variables
     */
    private $amount;
    private $bank_fee;
    private $debt;
    private $description;
    private $holder;
    private $iban;
    private $invoiceNr;
    private $logMsg;
    private $mref;

    /**
     * Transaction that needs to be set on Class initialization.
     *
     * @var \Kingsquare\Banking\Transaction
     */
    private $transaction;

    // ABSCHLUSS
    protected $codesToDiscard = ['805'];
    protected $conf;
    protected $excludeRegexes;

    public $excludeRegexesRelPath = 'config/dunning/transferExcludes.php';

    /**
     * Designators in transfer reason and it's corresponding variable names for the mandatory entries
     * = Bezeichner
     *
     * @var array
     */
    public static $designators = [
        'ABWA+' => '',              // Abweichender SEPA Auftraggeber
        'ABWE+' => '',              // Abweichender SEPA Empfänger
        'BIC+' => '',               // SEPA BIC Auftraggeber
        'BREF+' => '',              // Bankreferenz, Instruction ID
        'COAM+' => 'bank_fee',      // Zinskompensationsbetrag
        'CRED+' => '',              // SEPA Creditor Identifier
        'DEBT+' => '',              // Originator Identifier
        'IBAN+' => '',              // SEPA IBAN Auftraggeber
        'EREF+' => 'invoiceNr',     // SEPA End to End-Referenz
        'KREF+' => '',              // Kundenreferenz
        'MREF+' => 'mref',          // SEPA Mandatsreferenz
        'OAMT+' => 'amount',        // Ursprünglicher Umsatzbetrag
        'RREF+' => '',              // Retourenreferenz
        'SVWZ+' => '',              // SEPA Verwendungszweck
// TODO: Move to specific TransactionParser
        'PURP+' => '',              // Volksbank Purpose ?
    ];

    /**
     * Check wheter the transaction is Credit or Debit and call the respective Function
     *
     * @return Modules\Dunning\Entities\Debt
     */
    public function parse(\Kingsquare\Banking\Transaction $transaction)
    {
        // Initialize Variables
        $this->debt = new Debt;
        $this->transaction = $transaction;

        $this->amount = $this->bank_fee = 0;
        $this->description = $this->holder = [];
        $this->iban = $this->invoiceNr = $this->logMsg = $this->mref = '';

        $action = $transaction->getDebitCredit() == 'D' ? 'parseDebit' : 'parseCredit';

        $this->$action();

        if ($this->debt instanceof Debt) {
            $this->debt->date = $this->transaction->getValueTimestamp('Y-m-d H:i:s');

            $this->debt->missing_amount = $this->debt->amount + $this->debt->total_fee;
        }

        // TODO: Dont add log entry if debt already exists

        return $this->debt;
    }

    /**
     * Set Debt Properties and create log output for Debit
     *
     * @return void|null
     */
    private function parseDebit()
    {
        if ($this->parseDescription() === false) {
            return $this->debt = null;
        }

        $this->logMsg = trans('dunning::messages.transaction.default.debit', [
            'holder' => $this->holder,
            'invoiceNr' => $this->invoiceNr,
            'mref' => $this->mref,
            'price' => number_format_lang($this->transaction->getPrice()),
            'iban' => $this->iban,
            'reason' => $this->description,
            ]);

        if ($this->setDebitDebtRelations() === false) {
            return $this->debt = null;
        }

        $this->debt->amount = $this->amount;
        $this->debt->bank_fee = $this->bank_fee;
        $this->debt->description = $this->description;
        $this->addFee();

        ChannelLog::debug('dunning', trans('dunning::messages.transaction.create')." $this->logMsg");
    }

    /**
     * Check if there's no mismatch in relation of contract to sepamandate or invoice
     * Set relation IDs on debt object if all is correct
     *
     * @return bool     true on success, false on mismatch
     */
    private function setDebitDebtRelations()
    {
        // Get SepaMandate by iban & mref
        $sepamandate = SepaMandate::withTrashed()
            ->where('reference', $this->mref)->where('iban', $this->iban)
            ->orderBy('deleted_at')->orderBy('valid_from', 'desc')->first();

        if ($sepamandate) {
            $this->debt->sepamandate_id = $sepamandate->id;
        }

        // Get Invoice
        $invoice = Invoice::where('number', $this->invoiceNr)->where('type', 'Invoice')->first();

        if ($invoice) {
            $this->debt->invoice_id = $invoice->id;

            // Check if Transaction refers to same Contract via SepaMandate and Invoice
            if ($sepamandate && $sepamandate->contract_id != $invoice->contract_id) {
                // As debits are structured by NMSPrime in sepa xml - this is actually an error and should not happen
                ChannelLog::warning('dunning', trans('view.Discard')." $this->logMsg. ".trans('dunning::messages.transaction.debit.diffContractSepa'));

                return false;
            }

            // Assign Debt by invoice number (or invoice number and SepaMandate)
            $this->debt->contract_id = $invoice->contract_id;
        } else {
            if (! $sepamandate) {
                ChannelLog::info('dunning', trans('view.Discard')." $this->logMsg. ".trans('dunning::messages.transaction.debit.missSepaInvoice'));

                return false;
            }

            // Assign Debt by sepamandate
            $this->debt->contract_id = $sepamandate->contract_id;
        }

        return true;
    }

    /**
     * If there is a Fee set in Global Config, add it to the Debt
     *
     * @return void
     */
    private function addFee()
    {
        if (! $this->debt instanceof Debt) {
            return;
        }

        // lazy loading of global dunning conf
        $this->getConf();

        $this->debt->total_fee = $this->debt->bank_fee;
        if ($this->conf['fee']) {
            $this->debt->total_fee = $this->conf['total'] ? $this->conf['fee'] : $this->debt->bank_fee + $this->conf['fee'];
        }
    }

    /**
     * Set Debt Properties and create log output for Credit
     *
     * @return void
     */
    private function parseCredit()
    {
        if ($this->parseDescription() === false) {
            return $this->debt = null;
        }

        $this->logMsg = trans('dunning::messages.transaction.default.credit', [
                'holder' => $this->holder,
                'price' => number_format_lang($this->transaction->getPrice()),
                'iban' => $this->iban,
                'reason' => $this->description,
            ]);

        $numbers = $this->searchNumbers();

        if ($numbers['exclude']) {
            ChannelLog::info('dunning', trans('view.Discard')." $this->logMsg. ".trans('dunning::messages.transaction.credit.missInvoice'));

            return $this->debt = null;
        }

        $numbers['eref'] = $this->invoiceNr ?? null;
        $numbers['mref'] = $this->mref ?? null;

        if ($this->setCreditDebtRelations($numbers) === false) {
            return $this->debt = null;
        }

        $this->debt->amount = -1 * $this->transaction->getPrice();
        $this->debt->description = $this->description;

        ChannelLog::debug('dunning', trans('dunning::messages.transaction.create')." $this->logMsg");
    }

    /**
     * Set contract_id of debt only if a correct invoice number is given in the transfer reason
     *
     * NOTE: Invoice number is mandatory as transaction could otherwise have a totally different intention
     *  e.g. like costs for electrician or sth else not handled by NMSPrime
     *
     * @return bool     true on success, false otherwise
     */
    private function setCreditDebtRelations($numbers)
    {
        $this->invoiceNr = $this->invoiceNr ?: $numbers['eref'];
        $invoice = Invoice::where('number', $this->invoiceNr)->where('type', 'Invoice')->first();

        if ($invoice) {
            $this->debt->contract_id = $invoice->contract_id;
            $this->debt->invoice_id = $invoice->id;

            return true;
        }

        $this->logMsg = trans('view.Discard')." $this->logMsg.";
        $hint = '';

        if ($this->invoiceNr) {
            $this->logMsg .= ' '.trans('dunning::messages.transaction.credit.noInvoice.notFound', ['number' => $this->invoiceNr]);
        } else {
            $this->logMsg .= ' '.trans('dunning::messages.transaction.credit.noInvoice.default');
        }

        // Give hints to what contract the transaction could be assigned
        // Or still add debt if it's almost secure that the transaction belongs to the customer and NMSPrime
        if ($numbers['contractNr']) {
            $contracts = Contract::where('number', 'like', '%'.$numbers['contractNr'].'%')->get();

            if ($contracts->count() > 1) {
                // As the prefix und suffix of the contract number is not considered it's possible to finde multiple contracts
                // When that happens we need to take this into consideration
                ChannelLog::error('dunning', trans('dunning::messages.transaction.credit.multipleContracts', ['number' => $numbers['contractNr']]));
            } elseif ($contracts->count() == 1) {
                // Create debt only if contract number is found and amount is same like in last invoice
                $ret = $this->addDebtBySpecialMatch($contracts->first());

                if ($ret) {
                    return true;
                }

                $hint .= ' '.trans('dunning::messages.transaction.credit.noInvoice.contract', ['contract' => $numbers['contractNr']]);
            }
        }

        $sepamandate = SepaMandate::where('iban', $this->iban)
            ->orderBy('valid_from', 'desc')
            ->where('valid_from', '<=', $this->transaction->getValueTimestamp('Y-m-d'))
            ->where(whereLaterOrEqual('valid_to', $this->transaction->getValueTimestamp('Y-m-d')))
            ->with('contract')
            ->first();

        if ($sepamandate) {
            // Create debt only if contract number is found and amount is same like in last invoice
            $ret = $this->addDebtBySpecialMatch($sepamandate->contract);

            if ($ret) {
                return true;
            }

            $hint .= ' '.trans('dunning::messages.transaction.credit.noInvoice.sepa', ['contract' => $sepamandate->contract->number]);
        }

        if ($hint) {
            ChannelLog::notice('dunning', $this->logMsg.$hint);
        } else {
            ChannelLog::info('dunning', $this->logMsg);
        }

        return false;
    }

    /**
     * Add debt even if reference to invoice can not be established in the special cases of
     *  (1) found contract number
     *  (2) found sepa mandate
     *  AND corresponding invoice amount is the same as the amount of the transaction
     *
     * @return bool
     *
     * TODO: Add debt for Debits with Customer nr and total debt = transaction amount
     */
    private function addDebtBySpecialMatch($contract)
    {
        $this->getConf();

        $invoice = Invoice::where('contract_id', $contract->id)
            ->where('type', 'Invoice')
            ->where('created_at', '<', $this->transaction->getValueTimestamp('Y-m-d'))
            ->orderBy('created_at', 'desc')->first();

        if (! $invoice || (round($invoice->charge * (1 + $this->conf['tax'] / 100), 2) != $this->transaction->getPrice())) {
            return false;
        }

        $this->debt->contract_id = $contract->id;
        // Collect debts added in special case and show only one log message at end
        $this->debt->addedBySpecialMatch = true;

        return true;
    }

    /**
     * Load dunning config model and store relevant properties in global property
     *
     * @return void
     */
    private function getConf()
    {
        if (! is_null($this->conf)) {
            return;
        }

        $dunningConf = Dunning::first();
        $billingConf = BillingBase::first();

        $this->conf = [
            'fee' => $dunningConf->fee,
            'total' => $dunningConf->total,
            'tax' => $billingConf->tax,
        ];
    }

    /**
     * Search for contract and invoice number in credit transfer reason to assign debt to appropriate customer
     *
     * @return array
     */
    private function searchNumbers()
    {
        // Match examples: Rechnungsnummer|Rechnungsnr|RE.-NR.|RG.-NR.|RG 2018/3/48616
        // preg_match('/R(.*?)((n(.*?)r)|G)(.*?)(\d{4}\/\d+\/\d+)/i', $this->reason, $matchInvoice);
        // $invoiceNr = $matchInvoice ? $matchInvoice[6] : '';

        // Match invoice numbers in NMSPrime default format: Year/CostCenter-ID/incrementing number - examples 2018/3/48616, 2019/15/201
        preg_match('/2\d{3}\/\d+\/\d+/i', $this->description, $matchInvoice);
        $this->invoiceNr = $matchInvoice ? $matchInvoice[0] : '';

        // Match examples: Kundennummer|Kd-Nr|Kd.nr.|Kd.-Nr.|Kn|Customernr|Cr|Cnr|Knr 13451
        preg_match('/[CK](.*?)[ndr](.*?)([1-7]\d{4})/i', $this->description, $matchContract);
        $contractNr = $matchContract ? $matchContract[3] : 0;

        // Special invoice numbers that ensure that transaction definitely doesn't belong to NMSPrime
        if (is_null($this->excludeRegexes)) {
            $this->getExcludeRegexes();
        }

        $exclude = '';
        foreach ($this->excludeRegexes as $regex => $group) {
            preg_match($regex, $this->description, $matchInvoiceSpecial);

            if ($matchInvoiceSpecial) {
                $exclude = $matchInvoiceSpecial[$group];

                break;
            }
        }

        return [
            'contractNr' => $contractNr,
            'exclude' => $exclude,
        ];
    }

    /**
     * Check for Regular Expressions to exclude.
     *
     * @return void
     */
    private function getExcludeRegexes()
    {
        if (\Storage::exists($this->excludeRegexesRelPath)) {
            $this->excludeRegexes = include storage_path("app/$this->excludeRegexesRelPath");
        }

        if (! $this->excludeRegexes) {
            $this->excludeRegexes = [];
        }
    }

    /**
     * Parse one mt940 transaction
     *
     * @return bool
     */
    protected function parseDescription()
    {
        $descriptionArray = explode('?', $this->transaction->getDescription());
        $reason = [];

        if ($this->discardTransactionType($descriptionArray[0])) {
            return false;
        }

        foreach ($descriptionArray as $line) {
            $key = substr($line, 0, 2);
            $line = substr($line, 2);

            // Transfer reason is 20 to 29
            if (intval($key) >= 20 && intval($key) <= 29) {
                $ret = $this->getVarFromDesignator($line);

                if ($ret['varName'] == 'description') {
                    $reason[] = $ret['value'];
                } else {
                    $varName = $ret['varName'];
                    $this->$varName = $ret['value'];
                }

                continue;
            }

            if ($key == '31') {
                $this->iban = utf8_encode($line);
                continue;
            }

            if (in_array($key, ['32', '33'])) {
                $this->holder[] = $line;
                continue;
            }

            // 60 to 63
            if (preg_match('/^6[0-3]/', $key)) {
                $reason[] = $line;

                continue;
            }
        }

        $this->holder = utf8_encode(implode('', $this->holder));
        $this->description = substr(utf8_encode(implode('', $reason)), 0, 255);

        return true;
    }

    /**
     * Determine whether a debit transaction should be discarded dependent of transaction code or GVC (Geschäftsvorfallcode)
     * See https://www.hettwer-beratung.de/sepa-spezialwissen/sepa-technische-anforderungen/sepa-gesch%C3%A4ftsvorfallcodes-gvc-mt-940/
     * These transactions can never belong to NMSPrime
     *
     * @param  string
     * @return bool
     */
    private function discardTransactionType($code)
    {
        if (in_array($code, $this->codesToDiscard)) {
            return true;
        }

        return false;
    }

    /**
     * Parse a Transaction description line for (1) mandatory informations and (2) transfer reason
     *
     * @param  string   line of transfer reason without beginning number
     * @return array
     */
    private function getVarFromDesignator($line)
    {
        if (! Str::startsWith($line, array_keys(self::$designators))) {
            // Descriptions without designator
            return ['varName' => 'description', 'value' => $line];
        }

        foreach (self::$designators as $key => $varName) {
            // Descriptions with designator
            if (Str::startsWith($line, $key) && ! $varName) {
                return ['varName' => 'description', 'value' => str_replace($key, '', $line)];
            }

            // Mandatory variables
            if (Str::startsWith($line, $key) && $varName) {
                if (in_array($key, ['COAM+', 'OAMT+'])) {
                    // Get fee and amount
                    $value = trim(str_replace($key, '', $line));
                    $value = str_replace(',', '.', $value);
                } else {
                    // Get mref and invoice nr
                    if ($key == 'EREF+') {
                        $key = ['EREF+', 'RG '];
                    }

                    $value = utf8_encode(trim(str_replace($key, '', $line)));
                }

                return ['varName' => $varName, 'value' => $value];
            }
        }
    }
}
