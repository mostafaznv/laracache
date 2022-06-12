<?php

use Illuminate\Support\Facades\Cache;
use Mostafaznv\LaraCache\Facades\LaraCache;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;

it('will create cache after creating record', function() {
    $name = 'latest';
    $fullName = 'test-model.latest';

    $facadeCache = Cache::get($fullName);
    $cache = TestModel::cache()->get($name);

    expect($facadeCache)->toBeNull()
        ->and($cache)->toBeNull();

    createModel();

    $facadeCache = Cache::get($fullName);
    $cache = TestModel::cache()->get($name);

    expect($facadeCache->value->name)->toBe('test-name')
        ->and($cache)->name->toBe('test-name');
});

it('will not create cache after creating record if refresh-after-create flag is false', function() {
    $fullName = 'test-model.latest.no-create';

    $hasCache = Cache::has($fullName);
    expect($hasCache)->toBeFalse();

    $model = createModel();

    $hasCache = Cache::has($fullName);
    expect($hasCache)->toBeFalse();


    $model->name = 'new-test-name';
    $model->save();

    $hasCache = Cache::has($fullName);
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
        ->and($list[TestModel::class])->toContain(
            'test-model.list.ttl', 'test-model.empty.number', 'test-model.latest.no-update'
        );
});
