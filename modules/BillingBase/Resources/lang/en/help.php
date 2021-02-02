<?php

return [
    'BillingBase' => [
        'adaptItemStart'    => 'When active the begin of all assigned items of a contract will be set to the contracts start date when this date is changed.',
        'cdr_offset'        => "TAKE CARE: incrementing this when having data from settlement runs leads to overwritten CDRs during next run - make sure to save/rename the history!\n\nExample: Set to 1 if Call Data Records from June belong to Invoices of July, Zero if it's the same month, 2 if CDRs of January belong to Invoices of March.",
        'cdr_retention'     => 'Months that Call Data Records may/have to be kept save',
        'extra_charge'      => 'Additional mark-on to purchase price. Currently only usable with provider HlKomm!',
        'fluid_dates'       => 'Check this box if you want to add tariffs with uncertain start and/or end date. If checked two new checkboxes (Valid from fixed, Valid to fixed) will appear on Item\'s edit/create page. Check out their help messages for further explanation!',
        'invoiceNrStart'    => 'Invoice Number Counter starts every new year with this number',
        'ItemTermination'   => 'Allow Customers only to terminate booked products on last day of month',
        'MandateRef'        => "A Template can be built with sql columns of contract or mandate table - possible fields: \n",
        'rcd'               => 'Is also the date of value. Can also be set specifically for a contract on contract page',
        'showAGs'           => 'Adds a select list with contact persons to the contract page. The list has to be stored in appropriate Storage directory - check source code!',
        'SplitSEPA'         => 'Sepa Transfers are split to different XML-Files dependent of their transfer type',
    ],
    'product' => [
        'recordMonthly' => 'With activated checkbox the corresponding items will be recorded monthly on the invoice. The unit price will be calculated automatically. This is only relevant for yearly charged products.',
    ],
    'sepaAccount' => [
        'invoiceHeadline'   => 'Replaces Headline in Invoices created for this Costcenter',
        'invoiceText'       => 'The Text of the separate four \'Invoice Text\'-Fields is automatically chosen dependent on the total charge and SEPA Mandate and is set in the appropriate Invoice for the Customer. It is possible to use all data field keys of the Invoice Class as placeholder in the form of {fieldname} to build a kind of template. These are replaced by the actual value of the Invoice.',
    ],
    'sepaMandate' => [
        'holder' => 'The name of the holder must not contain a semicolon.',
    ],
    'texTemplate'           => 'TeX Template',
];
