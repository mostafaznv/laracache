<?php

use Illuminate\Support\Facades\Bus;
use Mostafaznv\LaraCache\Jobs\DebounceRefresh;
use Mostafaznv\LaraCache\Jobs\UpdateLaraCacheModelsList;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    Bus::fake();

    $this->key = 'cache-key';
    $this->model = TestModel::class;
    $this->name = 'list.forever';

    testTime()->freeze('2025-08-14 12:00:00');
});


it('wont crash when cache is empty', function () {
    $debounce = new DebounceRefresh($this->key, 'token', $this->model, $this->name);
    $debounce->handle();

    Bus::assertNotDispatched(UpdateLaraCacheModelsList::class);
});

it('wont call UpdateLaraCacheModelsList when the given token is different from the cached one', function () {
    $debounce = new DebounceRefresh($this->key, 'token', $this->model, $this->name);
    $debounce->handle();

    Bus::assertNotDispatched(UpdateLaraCacheModelsList::class);
});

it('will call UpdateLaraCacheModelsList when the given token and the latest one are matched', function () {
    Cache::put($this->key, 'token', 5000);

    $debounce = new DebounceRefresh($this->key, 'token', $this->model, $this->name);
    $debounce->handle();

    Bus::assertDispatched(UpdateLaraCacheModelsList::class);
});

it('will call UpdateLaraCacheModelsList once', function () {
    Cache::put($this->key, 'real-token', 5000);

    # 1
    $debounce = new DebounceRefresh($this->key, 'token-1', $this->model, $this->name);
    $debounce->handle();

    # 2
    $debounce = new DebounceRefresh($this->key, 'token-2', $this->model, $this->name);
    $debounce->handle();

    # 3
    $debounce = new DebounceRefresh($this->key, 'real-token', $this->model, $this->name);
    $debounce->handle();

    Bus::assertDispatchedTimes(UpdateLaraCacheModelsList::class, 1);
});

