<?php

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Mostafaznv\LaraCache\Jobs\RefreshCache;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;

it('will process cache through queue', function() {
    Bus::fake();

    config()->set('laracache.queue', true);

    createModel();

    Bus::assertDispatched(RefreshCache::class);
});

it('will update cache through queue', function() {
    config()->set('laracache.queue', true);

    $hasCache = Cache::has('latest');
    expect($hasCache)->toBeFalse();

    createModel();

    $hasLatestCache = Cache::has('latest');
    $latestCache = TestModel::cache()->get('latest');

    expect($hasLatestCache)->toBeTrue()
        ->and($latestCache)->toBeTruthy()
        ->name->toBe('test-name');
});
