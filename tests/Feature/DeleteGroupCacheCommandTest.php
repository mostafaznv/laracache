<?php

use Illuminate\Support\Facades\Artisan;
use Mostafaznv\LaraCache\DTOs\CacheStatus;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel2;

beforeEach(function() {
    $records = [
        [
            'name'    => 'test-model 1',
            'content' => 'content 1'
        ],
        [
            'name'    => 'test-model 2',
            'content' => 'content 2'
        ]
    ];

    foreach ($records as $record) {
        TestModel::query()->create($record);
        TestModel2::query()->create($record);
    }

    $this->created = CacheStatus::CREATED();
    $this->deleted = CacheStatus::DELETED();
});


it('will delete all models and entities of the group', function() {
    config()->set('laracache.groups.test-group', [
        [
            'model'    => TestModel::class,
            'entities' => [
                'list.forever', 'list.week'
            ],
        ],
        [
            'model'    => TestModel2::class,
            'entities' => [],
        ]
    ]);

    $names = [
        "List Forever\nDeleted", "List Week\nDeleted", "List Day\nDeleted", "Latest\nDeleted"
    ];


    Artisan::call('laracache:delete-group', [
        'group' => 'test-group'
    ]);

    $output = Artisan::output();

    expect($output)
        ->toBeString()
        ->toContain($names[0])
        ->toContain($names[1])
        ->toContain($names[2])
        ->toContain($names[3]);
});

