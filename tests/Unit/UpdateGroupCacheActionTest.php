<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mostafaznv\LaraCache\Actions\UpdateGroupCacheAction;
use Mostafaznv\LaraCache\Exceptions\CacheGroupNotExist;
use Mostafaznv\LaraCache\Exceptions\CacheGroupValueIsNotValid;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel2;

beforeEach(function() {
    $records = [
        [
            'name'    => 'test-model-1',
            'content' => 'content 1'
        ],
        [
            'name'    => 'test-model-2',
            'content' => 'content 2'
        ]
    ];

    DB::table((new TestModel())->getTable())->insert($records);
    DB::table((new TestModel2())->getTable())->insert($records);
});


it('will throw an exception if group doesnt exist in config file', function() {
    UpdateGroupCacheAction::make()->run('test-group');
})->throws(CacheGroupNotExist::class);

it('will throw an exception if group property is not an array', function() {
    config()->set('laracache.groups.test-group', 'test');

    UpdateGroupCacheAction::make()->run('test-group');
})->throws(CacheGroupValueIsNotValid::class);

it('will throw an exception if model key in group item is not set', function() {
    config()->set('laracache.groups.test-group', [
        [
            'entities' => [
                'list.forever', 'list.week'
            ],
        ]
    ]);

    UpdateGroupCacheAction::make()->run('test-group');
})->throws(CacheGroupValueIsNotValid::class);

it('will throw an exception if model key in group item is not a string', function() {
    config()->set('laracache.groups.test-group', [
        [
            'model'    => [TestModel::class],
            'entities' => [
                'list.forever', 'list.week'
            ],
        ]
    ]);

    UpdateGroupCacheAction::make()->run('test-group');
})->throws(CacheGroupValueIsNotValid::class);

it('will throw an exception if entities key in group item is not set', function() {
    config()->set('laracache.groups.test-group', [
        [
            'model'    => [TestModel::class],
        ]
    ]);

    UpdateGroupCacheAction::make()->run('test-group');
})->throws(CacheGroupValueIsNotValid::class);

it('will throw an exception if entities key in group item is not an array', function() {
    config()->set('laracache.groups.test-group', [
        [
            'model'    => [TestModel::class],
            'entities' => 'list.forever',
        ]
    ]);

    UpdateGroupCacheAction::make()->run('test-group');
})->throws(CacheGroupValueIsNotValid::class);

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

    $entities1 = ['list.forever', 'list.week'];
    $entities2 = ['list.day', 'list.week', 'list.forever', 'latest'];

    foreach ($entities1 as $entity) {
        $hasCache = Cache::has("test-model.$entity");
        expect($hasCache)->toBeFalse();
    }

    foreach ($entities2 as $entity) {
        $hasCache = Cache::has("test-model2.$entity");
        expect($hasCache)->toBeFalse();
    }

    UpdateGroupCacheAction::make()->run('test-group');

    foreach ($entities1 as $entity) {
        $hasCache = Cache::has("test-model.$entity");
        expect($hasCache)->toBeTrue();
    }

    foreach ($entities2 as $entity) {
        $hasCache = Cache::has("test-model2.$entity");
        expect($hasCache)->toBeTrue();
    }
});
