<?php

return [
    'debt' => [
        'total_fee' => 'Dunning fee included.',
    ],
    'fee' => 'Fee of the provider for return debit notes.',
    'import' => [
        'amount' => 'If a customers cumulated debts (missing amounts) are higher than the here inserted amount, the customers internet access will be blocked during the debt import.',
        'debts' => 'If the count of open debts for the contract matches the here inserted number the internet access for this customer will be automatically blocked during debt import (from bank accounting software).',
        'indicator' => 'Is there a debt imported (via debt import in settlement run) with the here inserted dunning indicator the internet access will be automatically blocked for the customer.',
    ],
    'testCsvUpload' => 'Customers internet access will not be blocked. It will just add the debts and show which customers would have been blocked.',
    'total' => 'If checked the inserted fee is always handled as absolute/total amount for every return debit note. The fee is not added separately to the fee of the bank.',
];
