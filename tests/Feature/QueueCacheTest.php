<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Mostafaznv\LaraCache\DTOs\CacheStatus;
use Illuminate\Support\Facades\Queue;
use Mostafaznv\LaraCache\Jobs\RefreshCache;
use Mostafaznv\LaraCache\Jobs\UpdateLaraCacheModelsList;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\QueueTestModel;
use Illuminate\Support\Facades\DB;

beforeEach(function() {
    Bus::fake([
        UpdateLaraCacheModelsList::class
    ]);
});

it('will initiate cache object with CREATING status', function() {
    createQueueModel();

    $cache = QueueTestModel::cache()->get('latest', true);
    $isCreating = $cache->status->equals(CacheStatus::CREATING());

    expect($isCreating)->toBeTrue();
});

it('will initiate cache object with entity default value', function() {
    createQueueModel();

    $cache = QueueTestModel::cache()->get('latest', true);

    expect($cache->value)->toBe(-1);
});

it('will initiate cache object with properly expiration ttl', function() {
    createQueueModel();

    $cache = QueueTestModel::cache()->get('latest', true);

    expect(is_null($cache->expiration))->toBeFalse();
});

it('will dispatch refresh-cache', function() {
    Queue::fake();
    createQueueModel();

    $onQueue = config('laracache.queue.name');

    Queue::assertPushedOn($onQueue, RefreshCache::class);
});

it('will create cache after processing queue', function() {
    createQueueModel();
    $before = now();

    $model = createQueueModel();
    Artisan::call('queue:work --once');

    $cache = QueueTestModel::cache()->get('latest', true);
    $isCreated = $cache->status->equals(CacheStatus::CREATED());
    $after = now();

    expect($before->diffInSeconds($after) >= 1)->toBeTrue()
        ->and($cache->value)->toBeInstanceOf(QueueTestModel::class)
        ->and($cache->value->name)->toBe($model->name)
        ->and($isCreated)->toBeTrue();
});

it('will return default value and dispatch cache creation job on retrieving entity', function() {
    Queue::fake();
    DB::table('test_models')
        ->insert([
            'name'       => 'queue-test-name',
            'content'    => 'content',
            'created_at' => now()
        ]);

    $cache = QueueTestModel::cache()->get('latest', true);
    $isCreating = $cache->status->equals(CacheStatus::CREATING());

    expect($isCreating)->toBeTrue()
        ->and($cache->value)->toBe(-1);

    $onQueue = config('laracache.queue.name');

    Queue::assertPushedOn($onQueue, RefreshCache::class);
});

it('will create cache in background on retrieving entity', function() {
    $name = 'queue-test-name';
    $before = now();

    DB::table('test_models')
        ->insert([
            'name'       => $name,
            'content'    => 'content',
            'created_at' => now()
        ]);

    $cache = QueueTestModel::cache()->get('latest', true);
    $isCreating = $cache->status->equals(CacheStatus::CREATING());

    expect($isCreating)->toBeTrue()
        ->and($cache->value)->toBe(-1);

    Artisan::call('queue:work --once');

    $cache = QueueTestModel::cache()->get('latest', true);
    $isCreated = $cache->status->equals(CacheStatus::CREATED());
    $after = now();

    expect($before->diffInSeconds($after) >= 1)->toBeTrue()
        ->and($cache->value)->toBeInstanceOf(QueueTestModel::class)
        ->and($cache->value->name)->toBe($name)
        ->and($isCreated)->toBeTrue();
});

it('will change cache status to creating on model update', function() {
    $model = createQueueModel();

    $cache = QueueTestModel::cache()->get('latest', true);
    $isCreating = $cache->status->equals(CacheStatus::CREATING());

    expect($isCreating)->toBeTrue();

    Artisan::call('queue:work --once');

    $cache = QueueTestModel::cache()->get('latest', true);
    $isCreated = $cache->status->equals(CacheStatus::CREATED());

    expect($isCreated)->toBeTrue();

    $model->name = 'new name';
    $model->save();

    $cache = QueueTestModel::cache()->get('latest', true);
    $isCreating = $cache->status->equals(CacheStatus::CREATING());

    expect($isCreating)->toBeTrue();

    Artisan::call('queue:work --once');

    $cache = QueueTestModel::cache()->get('latest', true);
    $isCreated = $cache->status->equals(CacheStatus::CREATED());

    expect($isCreated)->toBeTrue()
        ->and($cache->value->name)->toBe('new name');
});

it('will return old cache until queue process of updating model is done', function() {
    $model = createQueueModel('old-name');

    $cache = QueueTestModel::cache()->get('latest');
    expect($cache)->toBe(-1);

    Artisan::call('queue:work --once');

    $cache = QueueTestModel::cache()->get('latest');
    expect($cache->name)->toBe('old-name');

    $model->name = 'new-name';
    $model->save();

    $cache = QueueTestModel::cache()->get('latest');
    expect($cache->name)->toBe('old-name');

    Artisan::call('queue:work --once');

    $cache = QueueTestModel::cache()->get('latest');
    expect($cache->name)->toBe('new-name');
});
