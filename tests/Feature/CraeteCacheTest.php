<?php

use Illuminate\Support\Facades\Cache;
use Mostafaznv\LaraCache\Facades\LaraCache;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;

it('will create cache after creating record', function() {
    $facadeCache = Cache::get('latest');
    $cache = TestModel::cache()->get('latest');

    expect($facadeCache)->toBeNull()
        ->and($cache)->toBeNull();

    createModel();

    $facadeCache = Cache::get('latest');
    $cache = TestModel::cache()->get('latest');

    expect($facadeCache->value->name)->toBe('test-name')
        ->and($cache)->name->toBe('test-name');
});

it('will not create cache after creating record if refresh-after-create flag is false', function() {
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

it('will not create cache if cache is disabled', function() {
    TestModel::cache()->disable();

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    createModel();

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    TestModel::cache()->enable();
});

it('will not create cache if cache is disabled - facade', function() {
    LaraCache::disable(TestModel::class);

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    createModel();

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    LaraCache::enable(TestModel::class);
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
