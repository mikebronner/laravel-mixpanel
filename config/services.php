<?php

return [
    'mixpanel' => [
        'host' => env("MIXPANEL_HOST"),
        'token' => env('MIXPANEL_TOKEN'),
        'enable-default-tracking' => true,
        'consumer' => 'socket',
        'connect-timeout' => 2,
        'timeout' => 2,
        "data_callback_class" => null,
    ]
];
