<?php

return [

    'cdr' => [
        'missingEvn' => 'Missing CDR of :provider.',
        'wrongArgument' => 'Wrong format of date option. Execute the command for last month. (Default case)',
        ],
    'settlementrun' => [
        'state' => [
            'clean' => 'Clean up directory...',
            'concatInvoices' => 'Concatenate invoices',
            'concatPostalInvoices' => 'Concatenate postal invoices...',
            'createInvoices' => 'Create Invoices',
            'finish' => 'Finished',
            'getData' => 'Get data...',
            'loadData' => 'Load Data...',
            'parseCdr' => 'Parse call data record file(s)...',
            'zip' => 'Create ZIP file...',
        ],
    ],
    'zip' => [
        'noPostal' => 'No invoices for postal delivery. Stop.',
    ],

];
