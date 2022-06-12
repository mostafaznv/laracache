<?php

use Illuminate\Support\Facades\Cache;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;


it('will restore cache after restoring record', function() {
    $model = createModel();
    $name = 'latest';
    $fullName = 'test-model.latest';

    $cache = TestModel::cache()->get($name);
    expect($cache->name)->toBe('test-name');

    $model->delete();

    $facadeCache = Cache::get($fullName);
    $cache = TestModel::cache()->get($name);

    expect($facadeCache->value)->toBeNull()
        ->and($cache)->toBeNull();

    $model->restore();

    $facadeCache = Cache::get($fullName);
    $cache = TestModel::cache()->get($name);

    expect($facadeCache->value->name)->toBe('test-name')
        ->and($cache->name)->toBe('test-name');
});

it('will not restore cache after restoring record if refresh-after-restore flag is false', function() {
    $model = createModel();
    $name = 'latest.no-restore';
    $fullName = 'test-model.latest.no-restore';

    $cache = Cache::get($fullName);
    expect($cache->value->name)->toBe('test-name');

    $model->delete();

    $cache = Cache::get($fullName);
    expect($cache->value)->toBeNull();

    $model->restore();

    $facadeCache = Cache::get($fullName);
    $cache = TestModel::cache()->get($name);

    expect($facadeCache->value)->toBeNull()
    ->and($cache)->toBeNull();
});
