<?php

use Mostafaznv\LaraCache\Actions\DeleteGroupCacheAction;
use Mostafaznv\LaraCache\Enums\CacheStatus;
use Mostafaznv\LaraCache\Exceptions\CacheGroupNotExist;
use Mostafaznv\LaraCache\Exceptions\CacheGroupValueIsNotValid;
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
});


it('will throw an exception if group doesnt exist in config file', function() {
    DeleteGroupCacheAction::make()->run('test-group');
})->throws(CacheGroupNotExist::class);

it('will throw an exception if group property is not an array', function() {
    config()->set('laracache.groups.test-group', 'test');

    DeleteGroupCacheAction::make()->run('test-group');
})->throws(CacheGroupValueIsNotValid::class);

it('will throw an exception if model key in group item is not set', function() {
    config()->set('laracache.groups.test-group', [
        [
            'entities' => [
                'list.forever', 'list.week'
            ],
        ]
    ]);

    DeleteGroupCacheAction::make()->run('test-group');
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

    DeleteGroupCacheAction::make()->run('test-group');
})->throws(CacheGroupValueIsNotValid::class);

it('will throw an exception if entities key in group item is not set', function() {
    config()->set('laracache.groups.test-group', [
        [
            'model'    => [TestModel::class],
        ]
    ]);

    DeleteGroupCacheAction::make()->run('test-group');
})->throws(CacheGroupValueIsNotValid::class);

it('will throw an exception if entities key in group item is not an array', function() {
    config()->set('laracache.groups.test-group', [
        [
            'model'    => [TestModel::class],
            'entities' => 'list.forever',
        ]
    ]);

    DeleteGroupCacheAction::make()->run('test-group');
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
        $cache = TestModel::cache()->get($entity, true);
        expect($cache->status)->toBe(CacheStatus::CREATED);
    }

    foreach ($entities2 as $entity) {
        $cache = TestModel2::cache()->get($entity, true);
        expect($cache->status)->toBe(CacheStatus::CREATED);
    }

    DeleteGroupCacheAction::make()->run('test-group');


    foreach ($entities1 as $entity) {
        $cache = TestModel::cache()->get($entity, true);
        expect($cache->status)->toBe(CacheStatus::DELETED);
    }

    foreach ($entities2 as $entity) {
        $cache = TestModel2::cache()->get($entity, true);
        expect($cache->status)->toBe(CacheStatus::DELETED);
    }
});
