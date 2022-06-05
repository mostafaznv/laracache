<?php

use Illuminate\Support\Facades\Bus;
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

    createModel();

    $cache = TestModel::retrieveCache('latest');
    expect($cache)->toBeTruthy()
        ->name->toBe('test-name');
});
