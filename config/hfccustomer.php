<?php

namespace Modules\HfcCustomer\Entities;

return [
    'name' => 'HFC',
    'MenuItems' => [
        'Modem Pos System' => [
            'link'	=> 'Mpr.index',
            'icon'	=> 'fa-hdd-o',
            'class' => Mpr::class,
        ],
    ],
];
