<?php

use Illuminate\Support\Facades\Bus;
use Mostafaznv\LaraCache\Jobs\DebounceRefresh;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\TestModel;
use Mostafaznv\LaraCache\Utils\RefreshDebouncer;
use Illuminate\Support\Str;
use function Spatie\PestPluginTestTime\testTime;


beforeEach(function () {
    Bus::fake();

    $this->model = TestModel::class;
    $this->name = 'list.forever';
    $this->queueName = 'default';
    $this->queueConnection = config('queue.default');

    testTime()->freeze('2025-08-14 12:00:00');
});


it('dispatches a job and sets cache keys correctly', function () {
    RefreshDebouncer::dispatch($this->model, $this->name, $this->queueConnection, $this->queueName);

    Bus::assertDispatched(DebounceRefresh::class, function ($job) {
        return $job->model === $this->model
            and $job->name === $this->name
            and $job->delay === 5
            and Str::isUuid($job->token);
    });
});

it('will delay running job for specific amount of seconds', function () {
    RefreshDebouncer::dispatch($this->model, $this->name, $this->queueConnection, $this->queueName, 13);

    Bus::assertDispatched(DebounceRefresh::class, function ($job) {
        return $job->delay === 13;
    });
});

it('will dispatch queue on customized queue connection', function () {
    RefreshDebouncer::dispatch($this->model, $this->name, 'test-connection', 'test-queue');

    Bus::assertDispatched(DebounceRefresh::class, function ($job) {
        return $job->connection === 'test-connection'
            and $job->queue === 'test-queue';
    });
});

it('will dispatch RefreshDebouncer multiple times', function () {
    RefreshDebouncer::dispatch($this->model, $this->name, $this->queueConnection, $this->queueName);
    RefreshDebouncer::dispatch($this->model, $this->name, $this->queueConnection, $this->queueName);
    RefreshDebouncer::dispatch($this->model, $this->name, $this->queueConnection, $this->queueName);
    RefreshDebouncer::dispatch($this->model, $this->name, $this->queueConnection, $this->queueName);


    Bus::assertDispatchedTimes(DebounceRefresh::class, 4);
});
