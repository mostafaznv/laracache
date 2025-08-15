<?php

namespace Mostafaznv\LaraCache;

use Closure;

class CacheEntity
{
    /**
     * Cache name
     *
     * @var string
     */
    public string $name;

    /**
     * Cache Driver (store)
     *
     * @var string
     */
    public string $driver;

    /**
     * Indicate if cache should exist forever
     *
     * @var bool
     */
    public bool $forever = true;

    /**
     * Indicates if caching operation should perform in the background or not
     *
     * @var bool
     */
    public bool $isQueueable;

    /**
     * Queue name
     *
     * @var string
     */
    public string $queueName;

    /**
     * Queue connection
     *
     * @var string
     */
    public string $queueConnection;


    /**
     * Indicate if cache should debounce
     *
     * @var bool
     */
    public bool $debounce = false;

    /**
     * Debounce time in seconds
     * debounce = 0 means the debouncing is disabled
     *
     * @var int
     */
    public int $debounceWaitTime = 0;

    /**
     * Debounce queue name
     *
     * @var string
     */
    public string $debounceQueueName;

    /**
     * Debounce queue connection
     *
     * @var string
     */
    public string $debounceQueueConnection;


    /**
     * Indicate if cache should exist till end of day
     *
     * @var bool
     */
    public bool $validForRestOfDay = false;

    /**
     * Indicate if cache should exist till end of week
     *
     * @var bool
     */
    public bool $validForRestOfWeek = false;

    /**
     * Cache TTL in seconds
     * ttl = 0 means we want cache queries forever
     *
     * @var int
     */
    public int $ttl = 0;

    /**
     * Indicate if queries should refresh after create
     *
     * @var bool
     */
    public bool $refreshAfterCreate = true;

    /**
     * Indicate if queries should refresh after update
     *
     * @var bool
     */
    public bool $refreshAfterUpdate = true;

    /**
     * Indicate if queries should refresh after delete
     *
     * @var bool
     */
    public bool $refreshAfterDelete = true;

    /**
     * Indicate if queries should refresh after restore
     *
     * @var bool
     */
    public bool $refreshAfterRestore = true;

    /**
     * Specify default value of cache entity
     *
     * @var mixed
     */
    public mixed $default = null;

    /**
     * The anonymous function that should be executed to store cache values to cache store.
     *
     * @var Closure|null
     */
    public ?Closure $cacheClosure = null;


    public function __construct(string $name)
    {
        $this->name = $name;
        $this->driver = config('laracache.driver') ?? config('cache.default');

        $queue = config('laracache.queue');
        $debounce = config('laracache.debounce');

        if (is_array($queue)) {
            $this->isQueueable = $queue['status'] ?? false;
            $this->queueName = $queue['name'] ?? 'default';
            $this->queueConnection = $queue['connection'] ?? config('queue.default');
        }
        else {
            $this->isQueueable = (bool)$queue;
            $this->queueName = 'default';
            $this->queueConnection = config('queue.default');
        }

        $debounceWaitTime = $debounce['wait'] ?? 5;

        $this->debounce = $debounceWaitTime > 0 ? ($debounce['status'] ?? false) : false;
        $this->debounceWaitTime = $debounceWaitTime;
        $this->debounceQueueName = $debounce['queue']['name'] ?? 'default';
        $this->debounceQueueConnection = $debounce['queue']['connection'] ?? config('queue.default');
    }

    /**
     * Create a new cache entity.
     *
     * @param string $name
     * @return CacheEntity
     */
    public static function make(string $name): CacheEntity
    {
        return new static($name);
    }

    /**
     * Specify custom driver for cache entity
     *
     * @param string $driver
     * @return $this
     */
    public function setDriver(string $driver): CacheEntity
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Specify if cache operation should perform in background or not
     *
     * @param bool $status
     * @param string $onConnection
     * @param string $onQueue
     * @return $this
     */
    public function isQueueable(bool $status = true, string $onConnection = '', string $onQueue = ''): CacheEntity
    {
        $this->isQueueable = $status;

        if ($onConnection) {
            $this->queueConnection = $onConnection;
        }

        if ($onQueue) {
            $this->queueName = $onQueue;
        }

        return $this;
    }


    /**
     * Specify if cache should debounce
     *
     * @param bool $status
     * @param int $waitTime
     * @param string $onConnection
     * @param string $onQueue
     * @return $this
     */
    public function shouldDebounce(bool $status = true, int $waitTime = 5, string $onConnection = '', string $onQueue = ''): CacheEntity
    {
        $this->debounceWaitTime = max($waitTime, 0);
        $this->debounce = $waitTime > 0 ? $status : false;

        if ($onConnection) {
            $this->debounceQueueConnection = $onConnection;
        }

        if ($onQueue) {
            $this->debounceQueueName = $onQueue;
        }


        return $this;
    }

    /**
     * Specify that the cache should refresh after create a model instance
     *
     * @param bool $status
     * @return $this
     */
    public function refreshAfterCreate(bool $status = true): CacheEntity
    {
        $this->refreshAfterCreate = $status;

        return $this;
    }

    /**
     * Specify that the cache should refresh after update a model instance
     *
     * @param bool $status
     * @return $this
     */
    public function refreshAfterUpdate(bool $status = true): CacheEntity
    {
        $this->refreshAfterUpdate = $status;

        return $this;
    }

    /**
     * Specify that the cache should refresh after delete a model instance
     *
     * @param bool $status
     * @return $this
     */
    public function refreshAfterDelete(bool $status = true): CacheEntity
    {
        $this->refreshAfterDelete = $status;

        return $this;
    }

    /**
     * Specify that the cache should refresh after restore a model instance
     *
     * @param bool $status
     * @return $this
     */
    public function refreshAfterRestore(bool $status = true): CacheEntity
    {
        $this->refreshAfterRestore = $status;

        return $this;
    }

    /**
     * Specify that cache entity should exist there forever
     *
     * @return $this
     */
    public function forever(): CacheEntity
    {
        $this->forever = true;
        $this->validForRestOfDay = false;
        $this->validForRestOfWeek = false;
        $this->ttl = 0;

        return $this;
    }

    /**
     * Specify that cache entity should exist there till end of day
     *
     * @return $this
     */
    public function validForRestOfDay(): CacheEntity
    {
        $this->validForRestOfDay = true;
        $this->validForRestOfWeek = false;
        $this->forever = false;
        $this->ttl = 0;

        return $this;
    }

    /**
     * Specify that cache entity should exist there till end of week
     *
     * @return $this
     */
    public function validForRestOfWeek(): CacheEntity
    {
        $this->validForRestOfDay = false;
        $this->validForRestOfWeek = true;
        $this->forever = false;
        $this->ttl = 0;

        return $this;
    }

    /**
     * Specify cache time to live in second
     *
     * @param int $seconds
     * @return $this
     */
    public function ttl(int $seconds): CacheEntity
    {
        $this->ttl = max($seconds, 0);
        $this->forever = $this->ttl === 0;
        $this->validForRestOfDay = false;
        $this->validForRestOfWeek = false;

        return $this;
    }

    /**
     * Set default value of cache entity
     *
     * @param mixed $defaultValue
     * @return $this
     */
    public function setDefault(mixed $defaultValue): CacheEntity
    {
        $this->default = $defaultValue;

        return $this;
    }

    /**
     * Get TTL
     *
     * @return int
     * @internal
     */
    public function getTtl(): int
    {
        if ($this->forever) {
            return 0;
        }

        if ($this->validForRestOfDay) {
            return day_ending_seconds();
        }

        if ($this->validForRestOfWeek) {
            return week_ending_seconds();
        }

        return $this->ttl;
    }

    /**
     * Specify cache closure
     *
     * @param Closure $closure
     * @return $this
     */
    public function cache(Closure $closure): CacheEntity
    {
        $this->cacheClosure = $closure;

        return $this;
    }
}
