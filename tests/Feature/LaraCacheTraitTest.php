<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mostafaznv\LaraCache\DTOs\CacheData;
use Mostafaznv\LaraCache\DTOs\CacheStatus;
use Mostafaznv\LaraCache\Exceptions\CacheEntityDoesNotExist;
use Mostafaznv\LaraCache\Facades\LaraCache;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use function Spatie\PestPluginTestTime\testTime;

it('will throw exception if entity name is not defined during retrieving cache', function() {
    $this->expectException(CacheEntityDoesNotExist::class);

    TestModel::cache()->get('unknown-name');
});

it('will throw exception if entity name is not defined during updating cache', function() {
    $this->expectException(CacheEntityDoesNotExist::class);

    TestModel::cache()->update('unknown-name');
});

it('will not make cache if cache is disabled', function() {
    TestModel::cache()->disable();

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    createModel();

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    TestModel::cache()->enable();
});

it('will return default value if cache content is empty', function() {
    $cache = TestModel::cache()->get('empty.number');
    expect($cache)->toBe('empty value');

    $cache = TestModel::cache()->get('empty.array');
    expect($cache)->toBe('empty value');

    $cache = TestModel::cache()->get('empty.string');
    expect($cache)->toBe('empty value');

    $cache = TestModel::cache()->get('empty.bool');
    expect($cache)->toBe('empty value');

    $cache = TestModel::cache()->get('empty.null');
    expect($cache)->toBe('empty value');
});

it('will return content of each entity correctly', function() {
    $cache = TestModel::cache()->get('static.number');
    expect($cache)->toBe(1);

    $cache = TestModel::cache()->get('static.array');
    expect($cache)->toBe([1, 2]);

    $cache = TestModel::cache()->get('static.bool');
    expect($cache)->toBeTrue();
});

it('will retrieve cache using laraCache static method', function() {
    $cache = TestModel::laraCache()->get('static.number');
    expect($cache)->toBe(1);

    $cache = TestModel::laraCache()->get('static.array');
    expect($cache)->toBe([1, 2]);

    $cache = TestModel::laraCache()->get('static.bool');
    expect($cache)->toBeTrue();
});

it('will create cache after creating record', function() {
    $cache = TestModel::cache()->get('latest');
    expect($cache)->toBeNull();

    $cache = Cache::get('latest');
    expect($cache->value)->toBeNull();

    createModel();

    $cache = Cache::get('latest');
    expect($cache->value->name)->toBe('test-name');

    $cache = TestModel::cache()->get('latest');
    expect($cache)->name->toBe('test-name');
});

it('will update cache after updating record', function() {
    $model = createModel();

    $cache = TestModel::cache()->get('latest');
    expect($cache->name)->toBe('test-name');

    $model->name = 'new-test-name';
    $model->save();

    $cache = TestModel::cache()->get('latest');
    expect($cache->name)->toBe('new-test-name');
});

it('will update cache after deleting record', function() {
    $name = 'latest';
    $model = createModel();

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');

    $model->delete();

    $cache = Cache::get($name);
    expect($cache->value)->toBeNull();

    $cache = TestModel::cache()->get('latest');
    expect($cache)->toBeNull();
});

it('will update cache after restoring record', function() {
    $name = 'latest';
    $model = createModel();

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');

    $model->delete();

    $cache = Cache::get($name);
    expect($cache->value)->toBeNull();

    $cache = TestModel::cache()->get($name);
    expect($cache)->toBeNull();

    $model->restore();

    $cache = Cache::get($name);
    expect($cache->value->name)->toBe('test-name');

    $cache = TestModel::cache()->get('latest');
    expect($cache->name)->toBe('test-name');
});

it('will not create cache after creating record if refresh after create flag is false', function() {
    $name = 'latest.no-create';

    $hasCache = Cache::has($name);
    expect($hasCache)->toBeFalse();

    $model = createModel();

    $hasCache = Cache::has($name);
    expect($hasCache)->toBeFalse();


    $model->name = 'new-test-name';
    $model->save();

    $hasCache = Cache::has($name);
    expect($hasCache)->toBeTrue();
});

it('will not update cache after updating record if refresh after update flag is false', function() {
    $name = 'latest.no-update';
    $model = createModel();

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');

    $model->name = 'new-test-name';
    $model->save();

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');
});

it('will not delete cache after deleting record if refresh after delete flag is false', function() {
    $name = 'latest.no-delete';
    $model = createModel();

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');

    $model->delete();

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');
});

it('will not restore cache after restoring record if refresh after restore flag is false', function() {
    $name = 'latest.no-restore';
    $model = createModel();

    $cache = Cache::get($name);
    expect($cache->value->name)->toBe('test-name');

    $model->delete();

    $cache = Cache::get($name);
    expect($cache->value)->toBeNull();

    $model->restore();

    $cache = Cache::get($name);
    expect($cache->value)->toBeNull();
});

it('will store cache entity forever', function() {
    createModel();

    $cache = TestModel::cache()->get('list.forever', true);

    expect($cache->value)->toHaveCount(1)
        ->and($cache->expiration)->toBeNull();
});

it('will store cache till end of day', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

    $cache = TestModel::cache()->get('list.day', true);

    expect($cache->value)->toHaveCount(1)
        ->and($cache->expiration)->toBe(1652831999);
});

it('will store cache till end of week', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

    $cache = TestModel::cache()->get('list.week', true);

    expect($cache->value)->toHaveCount(1)
        ->and($cache->expiration)->toBe(1653177599);
});

it('will store cache with ttl', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

    $cache = TestModel::cache()->get('list.ttl', true);

    expect($cache->value)->toHaveCount(1)
        ->and($cache->expiration)->toBe(1652791534);
});

it('will update cache manually', function() {
    $model = createModel();

    $cache = TestModel::cache()->get('latest');
    expect($cache->name)->toBe('test-name');

    DB::table('test_models')->where('id', $model->id)->update([
        'name' => 'new-test-name'
    ]);

    $cache = TestModel::cache()->get('latest');
    expect($cache->name)->toBe('test-name');

    TestModel::cache()->update('latest');

    $cache = TestModel::cache()->get('latest');
    expect($cache->name)->toBe('new-test-name');
});

it('will update all cache entities manually', function() {
    createModel();

    $cache = TestModel::cache()->get('latest');
    expect($cache->name)->toBe('test-name');

    $cache = TestModel::cache()->get('list.forever');
    expect($cache)->toHaveCount(1);

    DB::table('test_models')->insert([
        'name'       => 'new-test-name',
        'content'    => 'content',
        'created_at' => now()->addSecond()
    ]);

    $cache = TestModel::cache()->get('latest');
    expect($cache->name)->toBe('test-name');

    $cache = TestModel::cache()->get('list.forever');
    expect($cache)->toHaveCount(1);

    TestModel::cache()->updateAll();

    $cache = TestModel::cache()->get('latest');
    expect($cache->name)->toBe('new-test-name');

    $cache = TestModel::cache()->get('list.forever');
    expect($cache)->toHaveCount(2);
});

it('will throw exception if entity name is not defined during deleting cache', function() {
    $this->expectException(CacheEntityDoesNotExist::class);

    TestModel::cache()->delete('unknown-name');
});

it('will delete cache manually', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

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

it('will delete cache manually forever', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

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
    createModel();

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

it('will return cache data if with cache data flag is true', function() {
    createModel();

    $cache = TestModel::cache()->get('latest', true);

    expect($cache)->toBeInstanceOf(CacheData::class);
});

it('will store all cache entities in laracache.list', function() {
    $list = LaraCache::list();
    expect($list)->toBeArray()->toHaveCount(0);

    createModel();

    $list = LaraCache::list();
    expect($list)->toHaveCount(1)
        ->and($list[TestModel::class])->toHaveCount(16)
        ->and($list[TestModel::class])->toContain('list.ttl', 'empty.number', 'latest.no-update');
});

it('will delete all cache entities of a model', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

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

it('will delete all cache entities of a model forever', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

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
