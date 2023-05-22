<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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


it('will update all entities of all models if multiple models sent to the action', function() {

    $names = [
        'list.day', 'list.week', 'list.forever', 'latest'
    ];

    foreach ($names as $name) {
        $hasCache = Cache::has("test-model.$name");
        expect($hasCache)->toBeFalse();

        $hasCache = Cache::has("test-model-2.$name");
        expect($hasCache)->toBeFalse();
    }

    Artisan::call('laracache:update', [
        '--model' => [
            TestModel::class, TestModel2::class
        ]
    ]);

    foreach ($names as $name) {
        $hasCache = Cache::has("test-model.$name");
        expect($hasCache)->toBeTrue();

        $hasCache = Cache::has("test-model2.$name");
        expect($hasCache)->toBeTrue();
    }
});

it('will update all entities of passed model if entities argument is empty', function() {
    $names = [
        'list.day', 'list.week', 'list.forever', 'latest'
    ];

    foreach ($names as $name) {
        $hasCache = Cache::has("test-model.$name");
        expect($hasCache)->toBeFalse();
    }

    Artisan::call('laracache:update', [
        '-m' => [
            TestModel::class
        ]
    ]);

    foreach ($names as $name) {
        $hasCache = Cache::has("test-model.$name");
        expect($hasCache)->toBeTrue();
    }
});

it('will update only specified entities if one model is sent to the action', function() {
    $names = [
        'list.week', 'list.forever', 'latest'
    ];

    $hasCache = Cache::has('test-model.list.day');
    expect($hasCache)->toBeFalse();

    Artisan::call('laracache:update', [
        '--model'  => [
            TestModel::class
        ],
        '--entity' => [
            'list.day'
        ]
    ]);

    foreach ($names as $name) {
        $hasCache = Cache::has("test-model.$name");
        expect($hasCache)->toBeFalse();
    }

    $hasCache = Cache::has('test-model.list.day');
    expect($hasCache)->toBeTrue();
});

it('will print the name of updated cache entities in the console', function() {
    $entities = [
        'list.week', 'list.forever', 'latest'
    ];

    $names = [
        "List Week\nUpdated", "List Forever\nUpdated", "Latest\nUpdated"
    ];


    Artisan::call('laracache:update', [
        '--model'  => [
            TestModel::class
        ],
        '--entity' => $entities
    ]);

    $output = Artisan::output();

    expect($output)
        ->toBeString()
        ->toContain($names[0])
        ->toContain($names[1])
        ->toContain($names[2]);
});

