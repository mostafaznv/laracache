<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | The default mechanism for handling cache storage.
    |
    | If you keep this option null, LaraCache will use the default cache
    | storage from config/cache.php
    |
    */

    'driver' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache List Key
    |--------------------------------------------------------------------------
    |
    | LaraCache uses a separate list to store name of all entities. using these
    | keys, we can perform some actions to all entities (such as update or delete them)
    |
    */

    'laracache-list' => 'laracache.list',

    /*
    |--------------------------------------------------------------------------
    | First Day of Week
    |--------------------------------------------------------------------------
    |
    | In some regions, saturday is first day of the week and in another regions
    | it may be different
    |
    */

    'first-day-of-week' => \Carbon\Carbon::SUNDAY,

    /*
    |--------------------------------------------------------------------------
    | Last Day of Week
    |--------------------------------------------------------------------------
    |
    | In some regions, friday is last day of the week and in another regions
    | it may be different
    |
    */

    'last-day-of-week' => \Carbon\Carbon::SATURDAY,

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Sometimes caching process is very heavy, so you have to queue the process and do it in background.
    |
    */

    'queue' => false,
];
