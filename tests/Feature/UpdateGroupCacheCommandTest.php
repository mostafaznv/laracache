<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel2;

beforeEach(function() {
    DB::table((new TestModel())->getTable())
        ->insert([
            [
                'name'    => 'test-model-1-1',
                'content' => 'content 1-1'
            ],
            [
                'name'    => 'test-model-1-2',
                'content' => 'content 1-2'
            ]
        ]);

    DB::table((new TestModel2())->getTable())
        ->insert([
            [
                'name'    => 'test-model-2-1',
                'content' => 'content 2-1'
            ],
            [
                'name'    => 'test-model-2-2',
                'content' => 'content 2-2'
            ]
        ]);
});


it('will update all models and entities of the group', function() {
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
        "List Forever\nUpdated", "List Week\nUpdated", "List Day\nUpdated", "Latest\nUpdated"
    ];


    Artisan::call('laracache:update-group', [
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

