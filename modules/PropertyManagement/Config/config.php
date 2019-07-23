<?php

namespace Modules\PropertyManagement\Entities;

return [
    'name' => 'PropertyManagement',
    'MenuItems' => [
        'Node' => [
            'link'	=> 'Node.index',
            'icon'	=> 'fa-share-alt-square',
            'class' => Node::class,
        ],
        'Realty' => [
            'link'	=> 'Realty.index',
            'icon'	=> 'fa-building-o',
            'class' => Realty::class,
        ],
    ],
];
