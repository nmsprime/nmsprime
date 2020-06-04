<?php

    return [
        'name' => 'HfcBase',
        'link' => 'HfcBase.index',
        'MenuItems' => [],
        'icinga' => [
            'api' => [
                'user' => env('ICINGA_API_USER', 'root'),
                'password' => env('ICINGA_API_PASSWORD', 'icinga'),
                'url' => env('ICINGA_API_URL', 'https://localhost:5665/v1/'),
            ],
        ],
    ];
