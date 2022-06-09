<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mostafaznv\LaraCache\DTOs\CacheStatus;
use Mostafaznv\LaraCache\Facades\LaraCache;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel2;
use function Spatie\PestPluginTestTime\testTime;

it('will retrieve cache', function() {
    $cache = LaraCache::retrieve(TestModel::class, 'static.array');

    expect($cache)->toBe([1, 2]);
});

it('will update cache', function() {
    createModel();

    $cache = LaraCache::retrieve(TestModel::class, 'latest');
    expect($cache->name)->toBe('test-name');

    DB::table('test_models')
        ->where('id', $cache->id)
        ->update([
            'name' => 'new-test-name'
        ]);

    $cache = LaraCache::retrieve(TestModel::class, 'latest');
    expect($cache->name)->toBe('test-name');

    LaraCache::update(TestModel::class, 'latest');

    $cache = LaraCache::retrieve(TestModel::class, 'latest');
    expect($cache->name)->toBe('new-test-name');
});

it('will update all cache entities', function() {
    createModel();

    $cache = LaraCache::retrieve(TestModel::class, 'latest');
    expect($cache->name)->toBe('test-name');

    $cache = LaraCache::retrieve(TestModel::class, 'list.forever');
    expect($cache)->toHaveCount(1);

    DB::table('test_models')->insert([
        'name'       => 'new-test-name',
        'content'    => 'content',
        'created_at' => now()->addSecond()
    ]);

    $cache = LaraCache::retrieve(TestModel::class, 'latest');
    expect($cache->name)->toBe('test-name');

    $cache = LaraCache::retrieve(TestModel::class, 'list.forever');
    expect($cache)->toHaveCount(1);

    LaraCache::updateAll(TestModel::class);

    $cache = LaraCache::retrieve(TestModel::class, 'latest');
    expect($cache->name)->toBe('new-test-name');

    $cache = LaraCache::retrieve(TestModel::class, 'list.forever');
    expect($cache)->toHaveCount(2);
});

it('will not make cache if cache is disabled', function() {
    LaraCache::disable(TestModel::class);

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    createModel();

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    LaraCache::enable(TestModel::class);
});

it('will delete cache', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

    $latestCache = LaraCache::retrieve(TestModel::class, 'latest');
    $dayCache = LaraCache::retrieve(TestModel::class, 'list.day');

    expect($latestCache->name)->toBe('test-name')
        ->and($dayCache)->toHaveCount(1);

    LaraCache::delete(TestModel::class, 'latest');
    LaraCache::delete(TestModel::class, 'list.day');

    $latestCache = LaraCache::retrieve(TestModel::class, 'latest', true);
    $dayCache = LaraCache::retrieve(TestModel::class, 'list.day', true);

    expect($latestCache->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($latestCache->value)->toBeNull()
        ->and($latestCache->expiration)->toBeNull()
        ->and($dayCache->status->equals(CacheStatus::DELETED()))
        ->and($dayCache->value)->toBeNull()
        ->and($dayCache->expiration)->toBe(1652831999);
});

it('will delete cache forever', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

    $weekCache = LaraCache::retrieve(TestModel::class, 'list.week', true);
    $dayCache = LaraCache::retrieve(TestModel::class, 'list.day', true);

    expect($weekCache->expiration)->toBe(1653177599)
        ->and($dayCache->expiration)->toBe(1652831999);

    LaraCache::delete(TestModel::class, 'list.week');
    LaraCache::delete(TestModel::class, 'list.day', true);

    $weekCache = LaraCache::retrieve(TestModel::class, 'list.week', true);
    $dayCache = LaraCache::retrieve(TestModel::class, 'list.day', true);

    expect($weekCache->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($weekCache->expiration)->toBe(1653177599)
        ->and($dayCache->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($dayCache->expiration)->toBeNull();
});

it('will delete all cache entities of a model', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

    $weekCache = LaraCache::retrieve(TestModel::class, 'list.week', true);
    $dayCache = LaraCache::retrieve(TestModel::class, 'list.day', true);
    $latestCache = LaraCache::retrieve(TestModel::class, 'latest', true);

    expect($weekCache->expiration)->toBe(1653177599)
        ->and($dayCache->expiration)->toBe(1652831999)
        ->and($latestCache->expiration)->toBeNull();

    LaraCache::deleteAll(TestModel::class);

    $weekCache = LaraCache::retrieve(TestModel::class, 'list.week', true);
    $dayCache = LaraCache::retrieve(TestModel::class, 'list.day', true);
    $latestCache = LaraCache::retrieve(TestModel::class, 'latest', true);

    expect($weekCache->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($weekCache->expiration)->toBe(1653177599)
        ->and($weekCache->value)->toBeNull()
        ->and($dayCache->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($dayCache->expiration)->toBe(1652831999)
        ->and($dayCache->value)->toBeNull()
        ->and($latestCache->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($latestCache->expiration)->toBeNull()
        ->and($dayCache->value)->toBeNull();
});

it('will delete all cache entities of a model forever', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

    $weekCache = LaraCache::retrieve(TestModel::class, 'list.week', true);
    $dayCache = LaraCache::retrieve(TestModel::class, 'list.day', true);
    $latestCache = LaraCache::retrieve(TestModel::class, 'latest', true);

    expect($weekCache->expiration)->toBe(1653177599)
        ->and($dayCache->expiration)->toBe(1652831999)
        ->and($latestCache->expiration)->toBeNull();

    LaraCache::deleteAll(TestModel::class, true);

    $weekCache = LaraCache::retrieve(TestModel::class, 'list.week', true);
    $dayCache = LaraCache::retrieve(TestModel::class, 'list.day', true);
    $latestCache = LaraCache::retrieve(TestModel::class, 'latest', true);

    expect($weekCache->expiration)->toBeNull()
        ->and($dayCache->expiration)->toBeNull()
        ->and($latestCache->expiration)->toBeNull();
});

it('will delete all cache entities that stored with laracache', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();
    createModel2();

    $weekCache1 = LaraCache::retrieve(TestModel::class, 'list.week', true);
    $dayCache1 = LaraCache::retrieve(TestModel::class, 'list.day', true);
    $latestCache1 = LaraCache::retrieve(TestModel::class, 'latest', true);
    $weekCache2 = LaraCache::retrieve(TestModel2::class, 'list.week', true);
    $dayCache2 = LaraCache::retrieve(TestModel2::class, 'list.day', true);
    $latestCache2 = LaraCache::retrieve(TestModel2::class, 'latest', true);

    expect($weekCache1->expiration)->toBe(1653177599)
        ->and($dayCache1->expiration)->toBe(1652831999)
        ->and($latestCache1->expiration)->toBeNull()
        ->and($weekCache2->expiration)->toBe(1653177599)
        ->and($dayCache2->expiration)->toBe(1652831999)
        ->and($latestCache2->expiration)->toBeNull();

    LaraCache::deleteAll();

    $weekCache1 = LaraCache::retrieve(TestModel::class, 'list.week', true);
    $dayCache1 = LaraCache::retrieve(TestModel::class, 'list.day', true);
    $latestCache1 = LaraCache::retrieve(TestModel::class, 'latest', true);
    $weekCache2 = LaraCache::retrieve(TestModel2::class, 'list.week', true);
    $dayCache2 = LaraCache::retrieve(TestModel2::class, 'list.day', true);
    $latestCache2 = LaraCache::retrieve(TestModel2::class, 'latest', true);

    expect($weekCache1->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($weekCache1->expiration)->toBe(1653177599)
        ->and($weekCache1->value)->toBeNull()
        ->and($dayCache1->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($dayCache1->expiration)->toBe(1652831999)
        ->and($dayCache1->value)->toBeNull()
        ->and($latestCache1->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($latestCache1->expiration)->toBeNull()
        ->and($dayCache1->value)->toBeNull()
        ->and($weekCache2->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($weekCache2->expiration)->toBe(1653177599)
        ->and($weekCache2->value)->toBeNull()
        ->and($dayCache2->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($dayCache2->expiration)->toBe(1652831999)
        ->and($dayCache2->value)->toBeNull()
        ->and($latestCache2->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($latestCache2->expiration)->toBeNull()
        ->and($dayCache2->value)->toBeNull();
});

it('will delete all cache entities that stored with laracache forever', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();
    createModel2();

    $weekCache1 = LaraCache::retrieve(TestModel::class, 'list.week', true);
    $dayCache1 = LaraCache::retrieve(TestModel::class, 'list.day', true);
    $latestCache1 = LaraCache::retrieve(TestModel::class, 'latest', true);
    $weekCache2 = LaraCache::retrieve(TestModel2::class, 'list.week', true);
    $dayCache2 = LaraCache::retrieve(TestModel2::class, 'list.day', true);
    $latestCache2 = LaraCache::retrieve(TestModel2::class, 'latest', true);

    expect($weekCache1->expiration)->toBe(1653177599)
        ->and($dayCache1->expiration)->toBe(1652831999)
        ->and($latestCache1->expiration)->toBeNull()
        ->and($weekCache2->expiration)->toBe(1653177599)
        ->and($dayCache2->expiration)->toBe(1652831999)
        ->and($latestCache2->expiration)->toBeNull();

    LaraCache::deleteAll(forever: true);

    $weekCache1 = LaraCache::retrieve(TestModel::class, 'list.week', true);
    $dayCache1 = LaraCache::retrieve(TestModel::class, 'list.day', true);
    $latestCache1 = LaraCache::retrieve(TestModel::class, 'latest', true);
    $weekCache2 = LaraCache::retrieve(TestModel2::class, 'list.week', true);
    $dayCache2 = LaraCache::retrieve(TestModel2::class, 'list.day', true);
    $latestCache2 = LaraCache::retrieve(TestModel2::class, 'latest', true);

    expect($weekCache1->expiration)->toBeNull()
        ->and($dayCache1->expiration)->toBeNull()
        ->and($latestCache1->expiration)->toBeNull()
        ->and($weekCache2->expiration)->toBeNull()
        ->and($dayCache2->expiration)->toBeNull()
        ->and($latestCache2->expiration)->toBeNull();
});
