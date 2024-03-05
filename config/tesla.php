<?php

return [
    'providers' => [
        'Tessie' => [
            'class' => \App\APIService\Tessie::class,
            'config' => [
                'url' => env('TESSIE_URL', 'https://api.tessie.com'),
            ],
        ],
    ]
];