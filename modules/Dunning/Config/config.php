<?php

namespace Modules\Dunning\Entities;

return [
    'name' => 'Dunning',
    // Change the type by inserting DEBT_MGMT_TYPE=csv in /etc/nmsprime/env/dunning.php
    'debtMgmtType' => env('DEBT_MGMT_TYPE', 'sta'),
    'MenuItems' => [
        'Debt' => [
            'link'	=> 'Debt.result',
            'icon'	=> 'fa-usd',
            'class' => Debt::class,
        ],
    ],
];
