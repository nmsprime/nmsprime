<?php

namespace Modules\HfcSnmp\Entities;

$config = [
    'link' => null,
    'parent' => 'HfcReq',
    'MenuItems' => [
        'MibFile' => [
            'link' => 'MibFile.index',
            'icon'	=> 'fa-file-o',
            'class' => MibFile::class,
        ],
    ],
];

if (\Module::collections()->has('HfcBase')) {
    $config['link'] = 'HfcBase.index';
    $config['parent'] = 'HfcBase';
}

return $config;
