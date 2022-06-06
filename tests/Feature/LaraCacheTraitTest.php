<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use function Spatie\PestPluginTestTime\testTime;

it('will throw exception if entity name is not defined during retrieving cache', function() {
    $this->expectException(Exception::class);

    TestModel::cache()->get('unknown-name');
});

it('will throw exception if entity name is not defined during updating cache', function() {
    $this->expectException(Exception::class);

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

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    createModel();

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeTrue();

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
    $model = createModel();

    $cache = TestModel::cache()->get('latest');
    expect($cache->name)->toBe('test-name');

    $model->delete();

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    $cache = TestModel::cache()->get('latest');
    expect($cache)->toBeNull();
});

it('will update cache after restoring record', function() {
    $model = createModel();

    $cache = TestModel::cache()->get('latest');
    expect($cache->name)->toBe('test-name');

    $model->delete();

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    $cache = TestModel::cache()->get('latest');
    expect($cache)->toBeNull();

    $model->restore();

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeTrue();

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

    $hasCache = Cache::has($name);
    expect($hasCache)->toBeTrue();

    $model->delete();

    $hasCache = Cache::has($name);
    expect($hasCache)->toBeFalse();

    $model->restore();

    $hasCache = Cache::has($name);
    expect($hasCache)->toBeFalse();
});

it('will store cache entity forever', function() {
    $now = testTime()->freeze();
    $name = 'list.forever';
    createModel();

    $cache = TestModel::cache()->get($name);
    expect($cache)->toHaveCount(1);

    $cache = DB::table('cache')->where('key', $name)->first();
    $expiration = (int)$cache->expiration;

    expect($expiration > $now->addYears(3)->unix())->toBeTrue();
});

it('will store cache till end of day', function() {
    testTime()->freeze('2022-05-17 12:43:34');

    createModel();

    $cache = DB::table('cache')->where('key', 'list.day')->first();

    expect($cache)->toBeTruthy()
        ->and((int)$cache->expiration)->toBe(1652831999);
});

it('will store cache till end of week', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

    $cache = DB::table('cache')->where('key', 'list.week')->first();

    expect($cache)->toBeTruthy()
        ->and((int)$cache->expiration)->toBe(1653177599);
});

it('will store cache with ttl', function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();

    $cache = DB::table('cache')->where('key', 'list.ttl')->first();

    expect($cache)->toBeTruthy()
        ->and((int)$cache->expiration)->toBe(1652791534);
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
