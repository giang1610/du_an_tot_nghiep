<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "pusher", "ably", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'pusher'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [
        'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY', 'local'),
        'secret' => env('PUSHER_APP_SECRET', 'local'),
        'app_id' => env('PUSHER_APP_ID', 'local'),
        'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
        'useTLS' => false, 
        'host' => '127.0.0.1',
        'port' => 6001,
        'scheme' => 'http',
        ],
        
            ],
            'log' => [
            'driver' => 'log',
        ],

    ]

];
