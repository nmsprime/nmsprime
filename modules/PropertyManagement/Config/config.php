<?php

namespace Modules\PropertyManagement\Entities;

return [
    'name' => trans('propertymanagement::view.menuName'),
    'MenuItems' => [
        'Node' => [
            'link'	=> 'Node.index',
            'icon'	=> 'fa-share-alt-square',
            'class' => Node::class,
        ],
    ],
];
