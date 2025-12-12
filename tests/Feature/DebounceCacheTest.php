<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Mostafaznv\LaraCache\Enums\CacheStatus;
use Mostafaznv\LaraCache\Jobs\DebounceRefresh;
use Mostafaznv\LaraCache\Jobs\UpdateLaraCacheModelsList;
use Mostafaznv\LaraCache\Tests\TestSupport\TestModels\DebounceTestModel;
use Illuminate\Support\Facades\DB;
use function Spatie\PestPluginTestTime\testTime;


beforeEach(function () {
    Bus::fake([
        UpdateLaraCacheModelsList::class
    ]);

    testTime()->freeze('2025-08-14 12:00:00');

    $this->waitTime = config('laracache.debounce.wait', 5);
});

it('will initiate cache object with CREATING status for the debounced entity', function () {
    createDebounceModel();

    $cache = DebounceTestModel::cache()->get('latest', true);
    expect($cache->status)->toBe(CacheStatus::CREATED);

    $cache = DebounceTestModel::cache()->get('latest.debounce', true);
    expect($cache->status)->toBe(CacheStatus::CREATING);
});

it('will initiate cache object with entity default value', function () {
    createDebounceModel();

    $cache = DebounceTestModel::cache()->get('latest.debounce', true);

    expect($cache->value)->toBe(-1);
});

it('will initiate cache object with proper expiration ttl', function () {
    createDebounceModel();

    $cache = DebounceTestModel::cache()->get('latest.debounce', true);

    expect($cache->expiration)
        ->toBeInt()
        ->toBeGreaterThan(0);
});

it('will dispatch debounce-refresh job', function () {
    Bus::fake();

    createDebounceModel();

    Bus::assertDispatched(DebounceRefresh::class);
});

it('will create cache after debounce wait time is completed', function () {
    createDebounceModel();

    # before running queue
    $cache = DebounceTestModel::cache()->get('latest.debounce', true);

    expect($cache->status)
        ->toBe(CacheStatus::CREATING)
        ->and($cache->value)
        ->toBe(-1);


    # waiting for debouncing
    testTime()->freeze(
        now()->addSeconds($this->waitTime - 1)
    );

    Artisan::call('queue:work --once --sleep=0');


    $cache = DebounceTestModel::cache()->get('latest.debounce', true);

    expect($cache->status)
        ->toBe(CacheStatus::CREATING)
        ->and($cache->value)
        ->toBe(-1);


    # debounced
    testTime()->freeze(
        now()->addSeconds($this->waitTime + 1)
    );

    Artisan::call('queue:work --once --sleep=0');

    $cache = DebounceTestModel::cache()->get('latest.debounce', true);

    expect($cache->status)
        ->toBe(CacheStatus::CREATED)
        ->and($cache->value)
        ->tobeInstanceOf(DebounceTestModel::class);
});

it('will execute sql query just once', function () {
    createDebounceModel();
    createDebounceModel();
    createDebounceModel();
    createDebounceModel();


    testTime()->freeze(
        now()->addSeconds($this->waitTime + 1)
    );

    Artisan::call('queue:work --once --sleep=0');
    Artisan::call('queue:work --once --sleep=0');
    Artisan::call('queue:work --once --sleep=0');

    $cache = DebounceTestModel::cache()->get('latest.debounce');
    expect($cache)->toBe(-1);


    DB::enableQueryLog();

    Artisan::call('queue:work --once --sleep=0');


    $cache = DebounceTestModel::cache()->get('latest.debounce');
    expect($cache)->toBeInstanceOf(DebounceTestModel::class);


    $queries = 0;

    foreach (DB::getQueryLog() as $query) {
        $q = $query['query'];

        if (str_starts_with($q, 'select *') and str_contains($q, 'test_models')) {
            $queries++;
        }
    }


    expect($queries)->toBe(1);
});

it('will return old cache until debounce process is done', function () {
    $model = createDebounceModel('old-name');

    # old cache
    testTime()->freeze(
        now()->addSeconds($this->waitTime + 1)
    );

    Artisan::call('queue:work --once --sleep=0');

    $cache = DebounceTestModel::cache()->get('latest.debounce');
    expect($cache->name)->toBe('old-name');


    # updating model
    $model->name = 'new-name';
    $model->save();

    testTime()->freeze(
        now()->addSeconds($this->waitTime - 1)
    );

    Artisan::call('queue:work --once --sleep=0');

    $cache = DebounceTestModel::cache()->get('latest.debounce', true);

    expect($cache->status)
        ->toBe(CacheStatus::CREATING)
        ->and($cache->value->name)
        ->toBe('old-name');


    # debounced
    testTime()->freeze(
        now()->addSeconds($this->waitTime + 1)
    );

    Artisan::call('queue:work --once --sleep=0');

    $cache = DebounceTestModel::cache()->get('latest.debounce');
    expect($cache->name)->toBe('new-name');
});

it('will return cached result on retrieving normally', function () {
    Queue::fake();
    DB::table('test_models')
        ->insert([
            'name'       => 'queue-test-name',
            'content'    => 'content',
            'created_at' => now()
        ]);

    $cache = DebounceTestModel::cache()->get('latest.debounce', true);

    expect($cache->status)
        ->toBe(CacheStatus::CREATED)
        ->and($cache->value)
        ->toBeInstanceOf(DebounceTestModel::class);
});
