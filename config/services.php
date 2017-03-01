<?php

return [
    'mixpanel' => [
        'token' => env('MIXPANEL_TOKEN', 'add your token to your env file'),
        'enable-default-tracking' => true,
        'consumer' => 'socket',
        'connect-timeout' => 2,
        'timeout' => 2,
    ]
];
