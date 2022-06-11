<?php

use Mostafaznv\LaraCache\DTOs\CacheData;
use Mostafaznv\LaraCache\Exceptions\CacheEntityDoesNotExist;
use Mostafaznv\LaraCache\Facades\LaraCache;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;

it('will throw exception if entity name is not defined during retrieving cache', function() {
    $this->expectException(CacheEntityDoesNotExist::class);

    TestModel::cache()->get('unknown-name');
});

it('will retrieve content of each entity correctly', function() {
    $numberCache = TestModel::cache()->get('static.number');
    $arrayCache = TestModel::cache()->get('static.array');
    $boolCache = TestModel::cache()->get('static.bool');

    expect($numberCache)->toBe(1)
        ->and($arrayCache)->toBe([1, 2])
        ->and($boolCache)->toBeTrue();
});

it('will retrieve content of each entity correctly using facade', function() {
    $cache = LaraCache::retrieve(TestModel::class, 'static.array');

    expect($cache)->toBe([1, 2]);
});

it('will retrieve cache using laraCache static method', function() {
    $numberCache = TestModel::laraCache()->get('static.number');
    $arrayCache = TestModel::laraCache()->get('static.array');
    $boolCache = TestModel::laraCache()->get('static.bool');

    expect($numberCache)->toBe(1)
        ->and($arrayCache)->toBe([1, 2])
        ->and($boolCache)->toBeTrue();
});

it('will return cache data if with cache data flag is true', function() {
    createModel();

    $cache = TestModel::cache()->get('latest', true);

    expect($cache)->toBeInstanceOf(CacheData::class);
});
