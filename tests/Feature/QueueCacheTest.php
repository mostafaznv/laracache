<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Mostafaznv\LaraCache\DTOs\CacheData;
use Mostafaznv\LaraCache\Enums\CacheStatus;
use Mostafaznv\LaraCache\Jobs\RefreshCache;
use Mostafaznv\LaraCache\Jobs\UpdateLaraCacheModelsList;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\QueueTestModel;
use Illuminate\Support\Facades\DB;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\QueueTestModel2;


beforeEach(function () {
    Bus::fake([
        UpdateLaraCacheModelsList::class
    ]);
});


it('will initiate cache object with CREATING status', function () {
    createQueueModel();

    $cache = QueueTestModel::cache()->get('latest', true);
    expect($cache->status)->toBe(CacheStatus::CREATING);
});

it('will initiate cache object with entity default value', function () {
    createQueueModel();

    $cache = QueueTestModel::cache()->get('latest', true);
    expect($cache->value)->toBe(-1);
});

it('will initiate cache object with properly expiration ttl', function () {
    createQueueModel();

    $cache = QueueTestModel::cache()->get('latest', true);
    expect(is_null($cache->expiration))->toBeFalse();
});

it('will dispatch refresh-cache', function () {
    Queue::fake();
    createQueueModel();

    $onQueue = config('laracache.queue.name');
    Queue::assertPushedOn($onQueue, RefreshCache::class);
});

it('will create cache after processing queue', function () {
    createQueueModel();
    $before = now();

    $model = createQueueModel();
    Artisan::call('queue:work --once --sleep=0');

    $cache = QueueTestModel::cache()->get('latest', true);
    $after = now();

    expect($before->diffInSeconds($after))
        ->toBeGreaterThanOrEqual(1)
        ->and($cache->value)
        ->toBeInstanceOf(QueueTestModel::class)
        ->and($cache->value->name)
        ->toBe($model->name)
        ->and($cache->status)
        ->toBe(CacheStatus::CREATED);
});

it('will return default value and dispatch cache creation job on retrieving entity', function () {
    Queue::fake();
    DB::table('test_models')
        ->insert([
            'name'       => 'queue-test-name',
            'content'    => 'content',
            'created_at' => now()
        ]);

    $cache = QueueTestModel::cache()->get('latest', true);
    $onQueue = config('laracache.queue.name');


    expect($cache->status)
        ->toBe(CacheStatus::CREATING)
        ->and($cache->value)
        ->toBe(-1);

    Queue::assertPushedOn($onQueue, RefreshCache::class);
});

it('will create cache in background on retrieving entity', function () {
    $name = 'queue-test-name';
    $before = now();

    DB::table('test_models')
        ->insert([
            'name'       => $name,
            'content'    => 'content',
            'created_at' => now()
        ]);

    $cache = QueueTestModel::cache()->get('latest', true);

    expect($cache->status)
        ->toBe(CacheStatus::CREATING)
        ->and($cache->value)
        ->toBe(-1);

    Artisan::call('queue:work --once --sleep=0');

    $cache = QueueTestModel::cache()->get('latest', true);
    $after = now();

    expect($before->diffInSeconds($after))
        ->toBeGreaterThanOrEqual(1)
        ->and($cache->value)
        ->toBeInstanceOf(QueueTestModel::class)
        ->and($cache->value->name)
        ->toBe($name)
        ->and($cache->status)
        ->toBe(CacheStatus::CREATED);
});

it('will change cache status to creating on model update', function () {
    $model = createQueueModel();

    $cache = QueueTestModel::cache()->get('latest', true);
    expect($cache->status)->toBe(CacheStatus::CREATING);

    Artisan::call('queue:work --once --sleep=0');

    $cache = QueueTestModel::cache()->get('latest', true);
    expect($cache->status)->toBe(CacheStatus::CREATED);

    $model->name = 'new name';
    $model->save();

    $cache = QueueTestModel::cache()->get('latest', true);
    expect($cache->status)->toBe(CacheStatus::CREATING);

    Artisan::call('queue:work --once --sleep=0');

    $cache = QueueTestModel::cache()->get('latest', true);
    expect($cache->status)
        ->toBe(CacheStatus::CREATED)
        ->and($cache->value->name)
        ->toBe('new name');
});

it('will return old cache until queue process of updating model is done', function () {
    $model = createQueueModel('old-name');

    $cache = QueueTestModel::cache()->get('latest');
    expect($cache)->toBe(-1);

    Artisan::call('queue:work --once --sleep=0');

    $cache = QueueTestModel::cache()->get('latest');
    expect($cache->name)->toBe('old-name');

    $model->name = 'new-name';
    $model->save();

    $cache = QueueTestModel::cache()->get('latest');
    expect($cache->name)->toBe('old-name');

    Artisan::call('queue:work --once --sleep=0');

    $cache = QueueTestModel::cache()->get('latest');
    expect($cache->name)->toBe('new-name');
});

it('will respect cache creation rules during queue tasks', function () {
    # prepare
    $worksOnCreation = 'queue-test-model.latest';
    $doesNotWorkOnCreation = 'queue-test-model2.latest';


    # test 1 - will work on creation
    createQueueModel();
    Artisan::call('queue:work --sleep=0 --once');

    $latest = Cache::get($worksOnCreation);
    expect($latest)
        ->toBeInstanceOf(CacheData::class)
        ->and($latest->status)
        ->toBe(CacheStatus::CREATED);


    # test 2 - will not work on creation
    createQueueModel2();
    Artisan::call('queue:work --sleep=0 --once');

    $latest = Cache::get($doesNotWorkOnCreation);
    expect($latest)->toBeNull();


    # test 3 - will work on retrieve
    $cache = QueueTestModel2::cache()->get('latest', true);

    expect($cache)
        ->toBeInstanceOf(CacheData::class)
        ->and($cache->status)
        ->toBe(CacheStatus::CREATING)
        ->and($cache->value)
        ->toBe(-1);

    Artisan::call('queue:work --sleep=0 --once');

    $cache = QueueTestModel2::cache()->get('latest', true);

    expect($cache)
        ->toBeInstanceOf(CacheData::class)
        ->and($cache->status)
        ->toBe(CacheStatus::CREATED)
        ->and($cache->value)
        ->toBeInstanceOf(QueueTestModel2::class);
});
