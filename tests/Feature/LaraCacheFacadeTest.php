<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mostafaznv\LaraCache\Facades\LaraCache;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;

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
