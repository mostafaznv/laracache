<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mostafaznv\LaraCache\Exceptions\CacheEntityDoesNotExist;
use Mostafaznv\LaraCache\Facades\LaraCache;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel2;

beforeEach(function() {
    $this->model = createModel();
});

it('will throw exception if entity name is not defined during updating cache', function() {
    $this->expectException(CacheEntityDoesNotExist::class);

    TestModel::cache()->update('unknown-name');
});

it('will update cache after updating record', function() {
    $name = 'latest';

    $facadeCache = Cache::get($name);
    $cache = TestModel::cache()->get($name);

    expect($cache->name)->toBe('test-name')
        ->and($facadeCache->value->name)->toBe('test-name');

    $this->model->name = 'new-test-name';
    $this->model->save();

    $facadeCache = Cache::get($name);
    $cache = TestModel::cache()->get($name);

    expect($cache->name)->toBe('new-test-name')
        ->and($facadeCache->value->name)->toBe('new-test-name');
});

it('will not update cache after updating record if refresh-after-update flag is false', function() {
    $name = 'latest.no-update';

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');

    $this->model->name = 'new-test-name';
    $this->model->save();

    $cacheFacade = Cache::get($name);
    $cache = TestModel::cache()->get($name);

    expect($cache->name)->toBe('test-name')
        ->and($cacheFacade->value->name)->toBe('test-name');
});

it('will update cache manually', function() {
    $name = 'latest';

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');

    DB::table('test_models')
        ->where('id', $this->model->id)
        ->update([
            'name' => 'new-test-name'
        ]);

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');

    TestModel::cache()->update($name);

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('new-test-name');
});

it('will update cache manually using facade', function() {
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
    $latestCache = TestModel::cache()->get('latest');
    $foreverCache = TestModel::cache()->get('list.forever');

    expect($latestCache->name)->toBe('test-name')
        ->and($foreverCache)->toHaveCount(1);

    DB::table('test_models')->insert([
        'name'       => 'new-test-name',
        'content'    => 'content',
        'created_at' => now()->addSecond()
    ]);

    $latestCache = TestModel::cache()->get('latest');
    $foreverCache = TestModel::cache()->get('list.forever');

    expect($latestCache->name)->toBe('test-name')
        ->and($foreverCache)->toHaveCount(1);

    TestModel::cache()->updateAll();

    $latestCache = TestModel::cache()->get('latest');
    $foreverCache = TestModel::cache()->get('list.forever');

    expect($latestCache->name)->toBe('new-test-name')
        ->and($foreverCache)->toHaveCount(2);
});

it('will update all cache entities manually using facade', function() {
    $latestCache = LaraCache::retrieve(TestModel::class, 'latest');
    $foreverCache = LaraCache::retrieve(TestModel::class, 'list.forever');

    expect($latestCache->name)->toBe('test-name')
        ->and($foreverCache)->toHaveCount(1);

    DB::table('test_models')->insert([
        'name'       => 'new-test-name',
        'content'    => 'content',
        'created_at' => now()->addSecond()
    ]);

    $latestCache = LaraCache::retrieve(TestModel::class, 'latest');
    $foreverCache = LaraCache::retrieve(TestModel::class, 'list.forever');

    expect($latestCache->name)->toBe('test-name')
        ->and($foreverCache)->toHaveCount(1);

    LaraCache::updateAll(TestModel::class);

    $latestCache = LaraCache::retrieve(TestModel::class, 'latest');
    $foreverCache = LaraCache::retrieve(TestModel::class, 'list.forever');

    expect($latestCache->name)->toBe('new-test-name')
        ->and($foreverCache)->toHaveCount(2);
});

it('will update all cache entities that stored with laracache', function() {
    $model2 = createModel2();

    $latestCache1 = TestModel::cache()->get('latest');
    $dayCache1 = TestModel::cache()->get('list.day');
    $latestCache2 = TestModel2::cache()->get('latest-2');
    $dayCache2 = TestModel2::cache()->get('list-2.day');

    expect($latestCache1->name)->toBe('test-name')
        ->and($dayCache1)->toHaveCount(1)
        ->and($latestCache2->name)->toBe('test-name')
        ->and($dayCache2)->toHaveCount(1);

    DB::table('test_models')
        ->where('id', $this->model->id)
        ->update([
            'name' => 'new-test-name'
        ]);

    DB::table('test_models_2')
        ->where('id', $model2->id)
        ->update([
            'name' => 'new-test-name-2'
        ]);

    $latestCache1 = TestModel::cache()->get('latest');
    $dayCache1 = TestModel::cache()->get('list.day');
    $latestCache2 = TestModel2::cache()->get('latest-2');
    $dayCache2 = TestModel2::cache()->get('list-2.day');

    expect($latestCache1->name)->toBe('test-name')
        ->and($dayCache1)->toHaveCount(1)
        ->and($latestCache2->name)->toBe('test-name')
        ->and($dayCache2)->toHaveCount(1);

    LaraCache::updateAll();

    $latestCache1 = TestModel::cache()->get('latest');
    $dayCache1 = TestModel::cache()->get('list.day');
    $latestCache2 = TestModel2::cache()->get('latest-2');
    $dayCache2 = TestModel2::cache()->get('list-2.day');

    expect($latestCache1->name)->toBe('new-test-name')
        ->and($dayCache1)->toHaveCount(1)
        ->and($latestCache2->name)->toBe('new-test-name-2')
        ->and($dayCache2)->toHaveCount(1);
});
