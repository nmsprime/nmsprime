<?php

return [
    'addedDebts' => 'Add debts to the following :count contracts: :numbers',
    'amountExceeded' => 'The cumulated amount of the placed deposits exceeds the amount of the debt.',
    'clearParentId' => 'Cleared relation to debt as it doesnt make sense as both amounts are positive/negative.',
    'csvImportActive' => 'The import of overdue debts is currently running. Please wait until this process has finished.',
    'import' => [
        'block' => 'Block internet access and add Dunning charges...',
        'columnCountError' => 'Wrong count of columns in the CSV. The count must be :number.',
        'contractsBlocked' => 'The internet access of the modems of the following :count contracts was blocked during import: :numbers',
        'contractsBlockedTest' => 'The internet access of the modems of the following :count contracts would have been blocked during import: :numbers',
        'contractsMissing' => 'Could not find contract(s) with the following contract number(s) of the uploaded file: :numbers.',
        'count' => 'Import :number overdue debts.',
        'runAsTest' => 'The debt import only runs as test. Internet access of the customers will not be blocked.',
    ],
    'parse' => [
        'start' => 'Parse bank transaction file',
        'transactions' => 'Add debts from transactions',
    ],
    'parseMt940Failed' => 'Error on parsing the uploaded file. See logfile. (:msg)',
    'staParsingActive' => 'The bank transaction file is currently parsed. Please wait until this process has finished.',
    'transaction' => [
        'create' => 'Create debt because of',
        'credit' => [
            // 'diff' => [
            //     'contractInvoice' => 'Contract :contract and invoice number from transfer reason do not belong to the same contract. (Invoice belongs to contract :invoice)',
            //     'contractSepa' => 'Transfer reason contains contract nr :contract but sepamandate belongs to contract :sepamandate',
            //     'invoiceSepa' => 'Found sepamandate belongs to contract :sepamandate, but the found invoice belongs to contract :invoice',
            // ],
            // 'missAll' => 'Neither contract, nor invoice, nor sepa mandate could be found',
            'missInvoice' => 'Transfer reason contains invoice number that does not belong to NMSPrime',
            'multipleContracts' => 'NMSPrime actually considers neither pre- nor suffix of the contract number to determine the contract possibly related to the transaction.',
            'noInvoice' => [
                'contract' => 'The transfer could belong to contract number :contract of the transfer reason.',
                'default' => 'The (correct) invoice number is missing in the transfer reason.',
                'notFound' => 'The given invoice number :number of the transfer reason could not be found in the system.',
                'sepa' => 'The transfer could belong to the contract :contract of the found IBAN.',
                'special' => 'Add debt to contracts :numbers found via contract number of the transfer reason or the IBAN besides missing the invoice number, because the last invoice amount being the same as the transaction amount for each contract.',
            ],
        ],
        'debit' => [
            'diffContractSepa' => 'SEPA mandate and invoice number belong to different contract',
            'missSepaInvoice' => 'Neither SepaMandate nor invoice nr could be found in the database',
        ],
        'default' => [
            'debit' => 'Debit-Transaction of :date of \':holder\' with invoice NR \':invoiceNr\', SepaMandate reference \':mref\', price :price IBAN :iban and transfer reason \':reason\'',
            'credit' => 'Credit transfer of :date of :holder with price :price, IBAN :iban and transfer reason :reason',
        ],
        'exists' => 'Ignore :debitCredit transaction as debt was already imported. (Price :price; Description :description)',
    ],
];
