<?php

use Illuminate\Support\Facades\Cache;
use Mostafaznv\LaraCache\DTOs\CacheStatus;
use Mostafaznv\LaraCache\Exceptions\CacheEntityDoesNotExist;
use Mostafaznv\LaraCache\Facades\LaraCache;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel2;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function() {
    testTime()->freeze('2022-05-17 12:43:34');

    $this->model = createModel();
});

it('will throw exception if entity name is not defined during deleting cache', function() {
    $this->expectException(CacheEntityDoesNotExist::class);

    TestModel::cache()->delete('unknown-name');
});

it('will delete cache after deleting record', function() {
    $name = 'latest';

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');

    $this->model->delete();

    $facadeCache = Cache::get($name);
    $cache = TestModel::cache()->get($name);

    expect($cache)->toBeNull()
        ->and($facadeCache->value)->toBeNull();
});

it('will not delete cache after deleting record if refresh-after-delete flag is false', function() {
    $name = 'latest.no-delete';

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');

    $this->model->delete();

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');
});

it('will delete cache manually', function() {
    $latestCache = TestModel::cache()->get('latest');
    $dayCache = TestModel::cache()->get('list.day', true);

    expect($latestCache->name)->toBe('test-name')
        ->and($dayCache->expiration)->toBe(1652831999);

    TestModel::cache()->delete('latest');
    TestModel::cache()->delete('list.day');

    $latestCache = TestModel::cache()->get('latest', true);
    $dayCache = TestModel::cache()->get('list.day', true);

    expect($latestCache->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($latestCache->value)->toBeNull()
        ->and($latestCache->expiration)->toBeNull()
        ->and($dayCache->value)->toBeNull()
        ->and($dayCache->expiration)->toBe(1652831999);
});

it('will delete cache manually using facade', function() {
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

it('will delete cache manually forever', function() {
    $weekCache = TestModel::cache()->get('list.week', true);
    $dayCache = TestModel::cache()->get('list.day', true);

    expect($weekCache->expiration)->toBe(1653177599)
        ->and($dayCache->expiration)->toBe(1652831999);

    TestModel::cache()->delete('list.week');
    TestModel::cache()->delete('list.day', true);

    $weekCache = TestModel::cache()->get('list.week', true);
    $dayCache = TestModel::cache()->get('list.day', true);

    expect($weekCache->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($weekCache->expiration)->toBe(1653177599)
        ->and($dayCache->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($dayCache->expiration)->toBeNull();
});

it('will delete cache manually forever using facade', function() {
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

it('will return default value after deleting cache item', function() {
    $cache = TestModel::cache()->get('static.number', true);

    expect($cache->status->equals(CacheStatus::CREATED()))->toBeTrue()
        ->and($cache->value)->toBe(1);

    TestModel::cache()->delete('static.number');

    $cache = TestModel::cache()->get('static.number', true);

    expect($cache->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($cache->value)->toBe('default-value');
});

it('will not delete other entities during deleting an entity manually', function() {
    $latestCache = TestModel::cache()->get('latest');
    $dayCache = TestModel::cache()->get('list.day');

    expect($latestCache->name)->toBe('test-name')
        ->and($dayCache)->toHaveCount(1);

    TestModel::cache()->delete('latest');

    $latestCache = TestModel::cache()->get('latest', true);
    $dayCache = TestModel::cache()->get('list.day', true);

    expect($latestCache->status->equals(CacheStatus::DELETED()))->toBeTrue()
        ->and($latestCache->value)->toBeNull()
        ->and($dayCache->status->equals(CacheStatus::CREATED()))->toBeTrue()
        ->and($dayCache->value)->toHaveCount(1);
});

it('will delete all cache entities of a model', function() {
    $weekCache = TestModel::cache()->get('list.week', true);
    $dayCache = TestModel::cache()->get('list.day', true);
    $latestCache = TestModel::cache()->get('latest', true);

    expect($weekCache->expiration)->toBe(1653177599)
        ->and($dayCache->expiration)->toBe(1652831999)
        ->and($latestCache->expiration)->toBeNull();

    TestModel::cache()->deleteAll();

    $weekCache = TestModel::cache()->get('list.week', true);
    $dayCache = TestModel::cache()->get('list.day', true);
    $latestCache = TestModel::cache()->get('latest', true);

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

it('will delete all cache entities of a model using facade', function() {
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
    $weekCache = TestModel::cache()->get('list.week', true);
    $dayCache = TestModel::cache()->get('list.day', true);
    $latestCache = TestModel::cache()->get('latest', true);

    expect($weekCache->expiration)->toBe(1653177599)
        ->and($dayCache->expiration)->toBe(1652831999)
        ->and($latestCache->expiration)->toBeNull();

    TestModel::cache()->deleteAll(true);

    $weekCache = TestModel::cache()->get('list.week', true);
    $dayCache = TestModel::cache()->get('list.day', true);
    $latestCache = TestModel::cache()->get('latest', true);

    expect($weekCache->expiration)->toBeNull()
        ->and($dayCache->expiration)->toBeNull()
        ->and($latestCache->expiration)->toBeNull();
});

it('will delete all cache entities of a model forever using facade', function() {
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
    createModel2();

    $weekCache1 = LaraCache::retrieve(TestModel::class, 'list.week', true);
    $dayCache1 = LaraCache::retrieve(TestModel::class, 'list.day', true);
    $latestCache1 = LaraCache::retrieve(TestModel::class, 'latest', true);
    $weekCache2 = LaraCache::retrieve(TestModel2::class, 'list-2.week', true);
    $dayCache2 = LaraCache::retrieve(TestModel2::class, 'list-2.day', true);
    $latestCache2 = LaraCache::retrieve(TestModel2::class, 'latest-2', true);

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
    $weekCache2 = LaraCache::retrieve(TestModel2::class, 'list-2.week', true);
    $dayCache2 = LaraCache::retrieve(TestModel2::class, 'list-2.day', true);
    $latestCache2 = LaraCache::retrieve(TestModel2::class, 'latest-2', true);

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
    createModel2();

    $weekCache1 = LaraCache::retrieve(TestModel::class, 'list.week', true);
    $dayCache1 = LaraCache::retrieve(TestModel::class, 'list.day', true);
    $latestCache1 = LaraCache::retrieve(TestModel::class, 'latest', true);
    $weekCache2 = LaraCache::retrieve(TestModel2::class, 'list-2.week', true);
    $dayCache2 = LaraCache::retrieve(TestModel2::class, 'list-2.day', true);
    $latestCache2 = LaraCache::retrieve(TestModel2::class, 'latest-2', true);

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
    $weekCache2 = LaraCache::retrieve(TestModel2::class, 'list-2.week', true);
    $dayCache2 = LaraCache::retrieve(TestModel2::class, 'list-2.day', true);
    $latestCache2 = LaraCache::retrieve(TestModel2::class, 'latest-2', true);

    expect($weekCache1->expiration)->toBeNull()
        ->and($dayCache1->expiration)->toBeNull()
        ->and($latestCache1->expiration)->toBeNull()
        ->and($weekCache2->expiration)->toBeNull()
        ->and($dayCache2->expiration)->toBeNull()
        ->and($latestCache2->expiration)->toBeNull();
});
