<?php

use Mostafaznv\LaraCache\Actions\DeleteCacheAction;
use Mostafaznv\LaraCache\DTOs\CacheStatus;
use Mostafaznv\LaraCache\DTOs\CommandData;
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


it('will delete all entities of all models if multiple models sent to the action', function() {
    $names = [
        'list.day', 'list.week', 'list.forever', 'latest'
    ];

    foreach ($names as $name) {
        $cache = TestModel::cache()->get($name, true);
        expect($cache->status->equals($this->created))->toBeTrue();

        $cache = TestModel2::cache()->get($name, true);
        expect($cache->status->equals($this->created))->toBeTrue();
    }

    DeleteCacheAction::make()->run(
        CommandData::make([TestModel::class, TestModel2::class])
    );

    foreach ($names as $name) {
        $cache = TestModel::cache()->get($name, true);
        expect($cache->status->equals($this->deleted))->toBeTrue();

        $cache = TestModel2::cache()->get($name, true);
        expect($cache->status->equals($this->deleted))->toBeTrue();
    }
});

it('will delete all entities of passed model if entities argument is empty', function() {
    $names = [
        'list.day', 'list.week', 'list.forever', 'latest'
    ];

    foreach ($names as $name) {
        $cache = TestModel::cache()->get($name, true);
        expect($cache->status->equals($this->created))->toBeTrue();
    }

    DeleteCacheAction::make()->run(
        CommandData::make([TestModel::class])
    );

    foreach ($names as $name) {
        $cache = TestModel::cache()->get($name, true);
        expect($cache->status->equals($this->deleted))->toBeTrue();
    }
});

it('will delete only specified entities if one model is sent to the action', function() {
    $names = [
        'list.week', 'list.forever', 'latest'
    ];

    $cache = TestModel::cache()->get('list.day', true);
    expect($cache->status->equals($this->created))->toBeTrue();

    DeleteCacheAction::make()->run(
        CommandData::make([TestModel::class], ['list.day'])
    );

    foreach ($names as $name) {
        $cache = TestModel::cache()->get($name, true);
        expect($cache->status->equals($this->created))->toBeTrue();
    }

    $cache = TestModel::cache()->get('list.day', true);
    expect($cache->status->equals($this->deleted))->toBeTrue();
});
