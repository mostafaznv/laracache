<?php

use Illuminate\Support\Facades\Cache;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;


it('will restore cache after restoring record', function() {
    $name = 'latest';
    $model = createModel();

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');

    $model->delete();

    $facadeCache = Cache::get($name);
    $cache = TestModel::cache()->get($name);

    expect($facadeCache->value)->toBeNull()
        ->and($cache)->toBeNull();

    $model->restore();

    $facadeCache = Cache::get($name);
    $cache = TestModel::cache()->get($name);

    expect($facadeCache->value->name)->toBe('test-name')
        ->and($cache->name)->toBe('test-name');
});

it('will not restore cache after restoring record if refresh-after-restore flag is false', function() {
    $name = 'latest.no-restore';
    $model = createModel();

    $cache = Cache::get($name);
    expect($cache->value->name)->toBe('test-name');

    $model->delete();

    $cache = Cache::get($name);
    expect($cache->value)->toBeNull();

    $model->restore();

    $facadeCache = Cache::get($name);
    $cache = TestModel::cache()->get($name);

    expect($facadeCache->value)->toBeNull()
    ->and($cache)->toBeNull();
});
