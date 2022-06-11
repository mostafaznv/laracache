<?php

use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function() {
    testTime()->freeze('2022-05-17 12:43:34');
    createModel();
});

it('will store cache entity forever', function() {
    $cache = TestModel::cache()->get('list.forever', true);

    expect($cache->value)->toHaveCount(1)
        ->and($cache->expiration)->toBeNull();
});

it('will store cache till end of day', function() {
    $cache = TestModel::cache()->get('list.day', true);

    expect($cache->value)->toHaveCount(1)
        ->and($cache->expiration)->toBe(1652831999);
});

it('will store cache till end of week', function() {
    $cache = TestModel::cache()->get('list.week', true);

    expect($cache->value)->toHaveCount(1)
        ->and($cache->expiration)->toBe(1653177599);
});

it('will store cache with ttl', function() {
    $cache = TestModel::cache()->get('list.ttl', true);

    expect($cache->value)->toHaveCount(1)
        ->and($cache->expiration)->toBe(1652791534);
});
