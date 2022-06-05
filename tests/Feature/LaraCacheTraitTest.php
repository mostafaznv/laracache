<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use function Spatie\PestPluginTestTime\testTime;

it('will throw exception if entity name is not defined during retrieving cache', function() {
    $this->expectException(Exception::class);

    TestModel::retrieveCache('unknown-name');
});

it('will throw exception if entity name is not defined during updating cache', function() {
    $this->expectException(Exception::class);

    TestModel::updateCache('unknown-name');
});

it('will not make cache if cache is disabled', function() {
    TestModel::disableCache();

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    createModel();

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    TestModel::enableCache();
});

it('will return default value if cache content is empty', function() {
    $cache = TestModel::retrieveCache('empty.number');
    expect($cache)->toBe('empty value');

    $cache = TestModel::retrieveCache('empty.array');
    expect($cache)->toBe('empty value');

    $cache = TestModel::retrieveCache('empty.string');
    expect($cache)->toBe('empty value');

    $cache = TestModel::retrieveCache('empty.bool');
    expect($cache)->toBe('empty value');

    $cache = TestModel::retrieveCache('empty.null');
    expect($cache)->toBe('empty value');
});

it('will return content of each entity correctly', function() {
    $cache = TestModel::retrieveCache('static.number');
    expect($cache)->toBe(1);

    $cache = TestModel::retrieveCache('static.array');
    expect($cache)->toBe([1, 2]);

    $cache = TestModel::retrieveCache('static.bool');
    expect($cache)->toBeTrue();
});

it('will create cache after creating record', function() {
    $cache = TestModel::retrieveCache('latest');
    expect($cache)->toBeNull();

    createModel();

    $cache = TestModel::retrieveCache('latest');
    expect($cache)->toBeTruthy()
        ->name->toBe('test-name');
});

it('will update cache after updating record', function() {
    $model = createModel();

    $cache = TestModel::retrieveCache('latest');
    expect($cache->name)->toBe('test-name');

    $model->name = 'new-test-name';
    $model->save();

    $cache = TestModel::retrieveCache('latest');
    expect($cache->name)->toBe('new-test-name');
});

it('will update cache after deleting record', function() {
    $model = createModel();

    $cache = TestModel::retrieveCache('latest');
    expect($cache->name)->toBe('test-name');

    $model->delete();

    $cache = TestModel::retrieveCache('latest');
    expect($cache)->toBeNull();
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

    $cache = TestModel::retrieveCache($name);
    expect($cache->name)->toBe('test-name');

    $model->name = 'new-test-name';
    $model->save();

    $cache = TestModel::retrieveCache($name);
    expect($cache->name)->toBe('test-name');
});

it('will not delete cache after deleting record if refresh after delete flag is false', function() {
    $name = 'latest.no-delete';
    $model = createModel();

    $cache = TestModel::retrieveCache($name);
    expect($cache->name)->toBe('test-name');

    $model->delete();

    $cache = TestModel::retrieveCache($name);
    expect($cache->name)->toBe('test-name');
});

it('will store cache entity forever', function() {
    $name = 'list.forever';
    createModel();

    $cache = TestModel::retrieveCache($name);
    expect($cache)->toHaveCount(1);

    testTime()->freeze('2048-05-17 12:43:34');

    $cache = TestModel::retrieveCache($name);
    expect($cache)->toHaveCount(1);
});

it('will store cache till end of day', function() {
    testTime()->freeze('2022-05-17 12:43:34');

    createModel();

    $cache = DB::table('cache')
        ->where('key', 'list.day')
        ->first();

    expect($cache)->toBeTruthy();

    $expiration = (int)$cache->expiration;

    expect($expiration)->toBe(1652831999);
});

it('will store cache till end of week', function() {
    testTime()->freeze('2022-05-17 12:43:34');

    createModel();

    $cache = DB::table('cache')
        ->where('key', 'list.week')
        ->first();

    expect($cache)->toBeTruthy();

    $expiration = (int)$cache->expiration;

    expect($expiration)->toBe(1653177599);
});

it('will store cache with ttl', function() {
    testTime()->freeze('2022-05-17 12:43:34');

    createModel();

    $cache = DB::table('cache')
        ->where('key', 'list.ttl')
        ->first();

    expect($cache)->toBeTruthy();

    $expiration = (int)$cache->expiration;

    expect($expiration)->toBe(1652791534);
});

it('will update cache manually', function() {
    $model = createModel();

    $cache = TestModel::retrieveCache('latest');
    expect($cache->name)->toBe('test-name');

    DB::table('test_models')->where('id', $model->id)->update([
        'name' => 'new-test-name'
    ]);

    $cache = TestModel::retrieveCache('latest');
    expect($cache->name)->toBe('test-name');

    TestModel::updateCache('latest');

    $cache = TestModel::retrieveCache('latest');
    expect($cache->name)->toBe('new-test-name');
});

it('will update all cache entities manually', function() {
    createModel();

    $cache = TestModel::retrieveCache('latest');
    expect($cache->name)->toBe('test-name');

    $cache = TestModel::retrieveCache('list.forever');
    expect($cache)->toHaveCount(1);

    DB::table('test_models')->insert([
        'name'       => 'new-test-name',
        'content'    => 'content',
        'created_at' => now()->addSecond()
    ]);

    $cache = TestModel::retrieveCache('latest');
    expect($cache->name)->toBe('test-name');

    $cache = TestModel::retrieveCache('list.forever');
    expect($cache)->toHaveCount(1);

    TestModel::updateAllCacheEntities();

    $cache = TestModel::retrieveCache('latest');
    expect($cache->name)->toBe('new-test-name');

    $cache = TestModel::retrieveCache('list.forever');
    expect($cache)->toHaveCount(2);
});
