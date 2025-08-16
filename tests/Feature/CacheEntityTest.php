<?php

use Mostafaznv\LaraCache\CacheEntity;

beforeEach(function() {
    $this->entity = CacheEntity::make('test-name');
});


it('will make cache entity using factory', function() {
    expect($this->entity)->toBeInstanceOf(CacheEntity::class)
        ->name->toBe('test-name');
});

it('will set driver if to default cache driver if laracache.driver is null', function() {
    config()->set('cache.default', 'fake-driver');

    $entity = CacheEntity::make('test-name');

    expect($entity->driver)->toBe('fake-driver');
});

it('will set driver if laracache.driver is set', function() {
    config()->set('laracache.driver', 'fake-driver');

    $entity = CacheEntity::make('test-name');

    expect($entity->driver)->toBe('fake-driver');
});

it('will set is-queueable by default if laracache.queue is boolean', function() {
    expect($this->entity->isQueueable)->toBeFalse();

    config()->set('laracache.queue', true);

    $entity = CacheEntity::make('test-name');

    expect($entity->isQueueable)->toBeTrue();
});

it('will set is-queueable by default', function() {
    expect($this->entity->isQueueable)->toBeFalse();

    config()->set('laracache.queue.status', true);

    $entity = CacheEntity::make('test-name');

    expect($entity->isQueueable)->toBeTrue();
});

it('will set custom queue status', function() {
    expect($this->entity->isQueueable)->toBeFalse();

    $this->entity->isQueueable();
    expect($this->entity->isQueueable)->toBeTrue();

    $this->entity->isQueueable(false);
    expect($this->entity->isQueueable)->toBeFalse();
});

it('will set queue name and connection by default', function() {
    expect($this->entity->queueName)->toBe('default')
        ->and($this->entity->queueConnection)->toBe('database');
});

it('will set queue name and connection by default with deprecated config file', function() {
    config()->set('laracache.queue', true);

    $entity = CacheEntity::make('test-name');

    expect($entity->queueName)->toBe('default')
        ->and($entity->queueConnection)->toBe('database');
});

it('will set custom queue name and connection using config file', function() {
    expect($this->entity->queueName)->toBe('default')
        ->and($this->entity->queueConnection)->toBe('database');

    config()->set('laracache.queue.name', 'test-queue');
    config()->set('laracache.queue.connection', 'test-connection');

    $entity = CacheEntity::make('test-name');

    expect($entity->queueName)->toBe('test-queue')
        ->and($entity->queueConnection)->toBe('test-connection');
});

it('will set custom queue name and connection using entity method', function() {
    expect($this->entity->queueName)->toBe('default')
        ->and($this->entity->queueConnection)->toBe('database');

    $entity = CacheEntity::make('test-name')
    ->isQueueable(true, 'test-connection', 'test-queue');

    expect($entity->queueName)->toBe('test-queue')
        ->and($entity->queueConnection)->toBe('test-connection');
});

it('will set debounce status property false by default', function() {
    expect($this->entity->debounce)->toBeFalse();
});

it('will set custom value for debounce status property using config file', function() {
    # true
    config()->set('laracache.debounce.status', true);

    $entity = CacheEntity::make('test-name');
    expect($entity->debounce)->toBeTrue();


    # false
    config()->set('laracache.debounce.status', false);

    $entity = CacheEntity::make('test-name');
    expect($entity->debounce)->toBeFalse();
});

it('will set custom value for debounce status property using entity methods', function() {
    # true
    $this->entity->debounce();
    expect($this->entity->debounce)->toBeTrue();

    # false
    $this->entity->debounce(false);
    expect($this->entity->debounce)->toBeFalse();
});

it('will set debounce wait-time property false by default', function() {
    expect($this->entity->debounceWaitTime)->toBe(5);
});

it('will set custom value for debounce wait-time property using config file', function() {
    config()->set('laracache.debounce.wait', 13);

    $entity = CacheEntity::make('test-name');
    expect($entity->debounceWaitTime)->toBe(13);
});

it('will set custom value for debounce wait-time property using entity methods', function() {
    $this->entity->debounce(true, 13);
    expect($this->entity->debounceWaitTime)->toBe(13);
});

it('will disable debounce status when wait-time property in config-file is negative or zero', function() {
    config()->set('laracache.debounce.status', true);
    config()->set('laracache.debounce.wait', 0);

    $status = config('laracache.debounce.status');
    expect($status)->toBeTrue();
});

it('will disable debounce status when wait-time property of cache entity is negative or zero', function() {
    $this->entity->debounce(true, 0);

    expect($this->entity->debounce)->toBeFalse();
});

it('will set custom debounce queue name and connection using config file', function() {
    expect($this->entity->debounceQueueName)->toBe('default')
        ->and($this->entity->debounceQueueConnection)->toBe('database');

    config()->set('laracache.debounce.queue.name', 'test-queue');
    config()->set('laracache.debounce.queue.connection', 'test-connection');

    $entity = CacheEntity::make('test-name');

    expect($entity->debounceQueueName)->toBe('test-queue')
        ->and($entity->debounceQueueConnection)->toBe('test-connection');
});

it('will set custom debounce queue name and connection using entity method', function() {
    expect($this->entity->debounceQueueName)->toBe('default')
        ->and($this->entity->debounceQueueConnection)->toBe('database');

    $entity = CacheEntity::make('test-name')
        ->debounce(true, 12, 'test-connection', 'test-queue');

    expect($entity->debounceQueueName)->toBe('test-queue')
        ->and($entity->debounceQueueConnection)->toBe('test-connection');
});

it('will set specific driver for entity', function() {
    expect($this->entity->driver)->toBe('array');

    $this->entity->setDriver('fake-driver');

    expect($this->entity->driver)->toBe('fake-driver');
});

it('will set refresh after create correctly', function() {
    expect($this->entity->refreshAfterCreate)->toBeTrue();

    $this->entity->refreshAfterCreate(false);
    expect($this->entity->refreshAfterCreate)->toBeFalse();

    $this->entity->refreshAfterCreate(true);
    expect($this->entity->refreshAfterCreate)->toBeTrue();
});

it('will set refresh after update correctly', function() {
    expect($this->entity->refreshAfterUpdate)->toBeTrue();

    $this->entity->refreshAfterUpdate(false);
    expect($this->entity->refreshAfterUpdate)->toBeFalse();

    $this->entity->refreshAfterUpdate(true);
    expect($this->entity->refreshAfterUpdate)->toBeTrue();
});

it('will set refresh after delete correctly', function() {
    expect($this->entity->refreshAfterDelete)->toBeTrue();

    $this->entity->refreshAfterDelete(false);
    expect($this->entity->refreshAfterDelete)->toBeFalse();

    $this->entity->refreshAfterDelete(true);
    expect($this->entity->refreshAfterDelete)->toBeTrue();
});

it('will set refresh after restore correctly', function() {
    expect($this->entity->refreshAfterRestore)->toBeTrue();

    $this->entity->refreshAfterRestore(false);
    expect($this->entity->refreshAfterRestore)->toBeFalse();

    $this->entity->refreshAfterRestore(true);
    expect($this->entity->refreshAfterRestore)->toBeTrue();
});

it('will set forever property correctly', function() {
    $this->entity->validForRestOfDay();

    expect($this->entity)
        ->forever->toBeFalse()
        ->validForRestOfDay->toBeTrue()
        ->validForRestOfWeek->toBeFalse()
        ->ttl->toBe(0);

    $this->entity->forever();

    expect($this->entity)
        ->forever->toBeTrue()
        ->validForRestOfDay->toBeFalse()
        ->validForRestOfWeek->toBeFalse()
        ->ttl->toBe(0);
});

it('will set cache expire to till the end of day', function() {
    expect($this->entity)
        ->forever->toBeTrue()
        ->validForRestOfDay->toBeFalse();

    $this->entity->validForRestOfDay();

    expect($this->entity)
        ->forever->toBeFalse()
        ->validForRestOfDay->toBeTrue()
        ->validForRestOfWeek->toBeFalse()
        ->ttl->toBe(0);
});

it('will set cache expire to till the week of day', function() {
    expect($this->entity)
        ->forever->toBeTrue()
        ->validForRestOfWeek->toBeFalse();

    $this->entity->validForRestOfWeek();

    expect($this->entity)
        ->forever->toBeFalse()
        ->validForRestOfDay->toBeFalse()
        ->validForRestOfWeek->toBeTrue()
        ->ttl->toBe(0);
});

it('will set ttl correctly', function() {
    expect($this->entity->forever)->toBeTrue();

    $this->entity->ttl(120);

    expect($this->entity)
        ->forever->toBeFalse()
        ->validForRestOfDay->toBeFalse()
        ->validForRestOfWeek->toBeFalse()
        ->ttl->toBe(120);
});

it('will enable forever flag if ttl is zero', function() {
    $this->entity->validForRestOfDay();

    expect($this->entity)
        ->forever->toBeFalse()
        ->validForRestOfDay->toBeTrue();

    $this->entity->ttl(0);

    expect($this->entity)
        ->forever->toBeTrue()
        ->validForRestOfDay->toBeFalse()
        ->ttl->toBe(0);
});

it('will set cache default value', function() {
    expect($this->entity->default)->toBeNull();

    $this->entity->setDefault([]);
    expect($this->entity->default)->tobe([]);

    $this->entity->setDefault(0);
    expect($this->entity->default)->tobe(0);

    $object = new stdClass;
    $this->entity->setDefault($object);
    expect($this->entity->default)->tobe($object);
});

it('will set cache function correctly', function() {
    expect($this->entity->cacheClosure)->toBeNull();

    $this->entity->cache(function() {
        return 'test-value';
    });

    expect($this->entity->cacheClosure)->toBeInstanceOf(Closure::class);

    $this->entity->cache(fn() => 'return-value');

    expect($this->entity->cacheClosure)->toBeInstanceOf(Closure::class);
});
