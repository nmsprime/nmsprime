<?php

namespace Modules\ProvVoipEnvia\Entities;

return [
    'name' => 'envia TEL',
    'MenuItems' => [
        'envia TEL orders' => [
            'link'	=> 'EnviaOrder.index',
            'icon'	=> 'fa-shopping-cart',
            'class' => EnviaOrder::class,
        ],
        'envia TEL contracts' => [
            'link'	=> 'EnviaContract.index',
            'icon'	=> 'fa-handshake-o',
            'class' => EnviaContract::class,
        ],
    ],
];
