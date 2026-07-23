<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Grace Distance
    |--------------------------------------------------------------------------
    |
    | The default grace distance in meters. If a user's pin falls outside a
    | polygon but within this distance from the polygon's edge, it will still
    | be considered a match (Grace Match).
    |
    */
    'default_grace_distance' => 50,

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | The Time-To-Live (TTL) for caching the active coverage zones in seconds.
    | 3600 seconds = 1 hour. It automatically invalidates when zones change.
    |
    */
    'cache_ttl' => 3600,

    /*
    |--------------------------------------------------------------------------
    | Smart Logging Settings
    |--------------------------------------------------------------------------
    |
    | If enabled, the engine will log coverage verification requests based on
    | the conditions below. Disable this in extremely high-traffic environments
    | if DB load is an issue.
    |
    */
    'log' => [
        'enabled' => true,
        
        // Only log requests that took longer than this many milliseconds.
        'slow_request_ms' => 30,
        
        // Log requests that matched via Grace Distance.
        'log_grace_matches' => true,
        
        // Log requests that did not match any zone.
        'log_no_matches' => true,
        
        // Log EVERYTHING (override). Set to false in production.
        'log_all' => false,
    ],

];
