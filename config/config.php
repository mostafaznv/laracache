<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | Defines the cache driver to be used by LaraCache.
    | If set to null, LaraCache will fall back to the application's default
    | cache store configured in `config/cache.php`.
    |
    */

    'driver' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache List Key
    |--------------------------------------------------------------------------
    |
    | Key used by LaraCache to maintain a registry of entity names. This list
    | enables operations across all registered entities (for example, bulk
    | updates or deletions).
    |
    */

    'laracache-list' => 'laracache.list',

    /*
    |--------------------------------------------------------------------------
    | First Day of Week
    |--------------------------------------------------------------------------
    |
    | Specifies the first day of the week. This value may vary by region.
    | Uses a Carbon constant for clarity and consistency.
    |
    */

    'first-day-of-week' => \Carbon\Carbon::SUNDAY,

    /*
    |--------------------------------------------------------------------------
    | Last Day of Week
    |--------------------------------------------------------------------------
    |
    | Specifies the last day of the week. Adjust this value to match your
    | regional or business conventions.
    |
    */

    'last-day-of-week' => \Carbon\Carbon::SATURDAY,

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Configuration for background processing. Use queues to offload heavy or
    | long-running cache operations. Set `status` to true to enable queuing.
    |
    */

    'queue' => [
        'status'     => false,
        'name'       => 'default',
        'connection' => null
    ],

    /*
    |--------------------------------------------------------------------------
    | Debounce
    |--------------------------------------------------------------------------
    |
    | Debounce settings control deferred refresh behavior to prevent frequent
    | or redundant cache refreshes. `wait` is specified in seconds. Debounce
    | can be processed via a dedicated queue configuration below.
    |
    */

    'debounce' => [
        'status' => false,
        'wait'   => 5, // in seconds

        'queue' => [
            'name'       => 'default',
            'connection' => null
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Groups
    |--------------------------------------------------------------------------
    |
    | Define logical groups of entities to perform collective operations.
    | Example structure:
    |
    | 'groups' => [
    |    'group-1' => [
    |        [
    |            'model' => \App\Models\User::class,
    |            'entities' => [
    |                'featured', 'latest', 'popular'
    |            ],
    |        ],
    |        [
    |            'model' => \App\Models\Article::class,
    |            'entities' => [],
    |        ]
    |    ],
    |
    |    'group-2' => [
    |        [
    |            'model' => \App\Models\Product::class,
    |            'entities' => [
    |                'latest', 'popular'
    |            ],
    |        ],
    |        [
    |            'model' => \App\Models\Article::class,
    |            'entities' => [],
    |        ]
    |    ],
    | ],
    |
    */

    'groups' => [],
];
