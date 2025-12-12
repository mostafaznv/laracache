<?php

namespace Mostafaznv\LaraCache;

use Closure;


class CacheEntity
{
    public string $name;
    public string $driver;

    public bool   $isQueueable;
    public string $queueName;
    public string $queueConnection;

    public bool   $debounce         = false;
    public int    $debounceWaitTime = 0; // when set to 0, debouncing is disabled
    public string $debounceQueueName;
    public string $debounceQueueConnection;

    public bool $forever            = true;
    public bool $validForRestOfDay  = false;
    public bool $validForRestOfWeek = false;

    public int  $ttl                 = 0; // in seconds. a value of 0 denotes no expiration (cache persists indefinitely)
    public bool $refreshAfterCreate  = true;
    public bool $refreshAfterUpdate  = true;
    public bool $refreshAfterDelete  = true;
    public bool $refreshAfterRestore = true;

    public mixed    $default      = null;
    public ?Closure $cacheClosure = null;


    public function __construct(string $name)
    {
        $config = config('laracache');
        $defaultQueue = config('queue.default');

        $this->name = $name;
        $this->driver = $config['driver'] ?? config('cache.default');

        $queue = $config['queue'];
        $debounce = $config['debounce'];

        if (is_array($queue)) {
            $this->isQueueable = $queue['status'] ?? false;
            $this->queueName = $queue['name'] ?? 'default';
            $this->queueConnection = $queue['connection'] ?? $defaultQueue;
        }
        else {
            $this->isQueueable = (bool)$queue;
            $this->queueName = 'default';
            $this->queueConnection = $defaultQueue;
        }

        $debounceWaitTime = $debounce['wait'] ?? 5;

        $this->debounce = $debounceWaitTime > 0 ? ($debounce['status'] ?? false) : false;
        $this->debounceWaitTime = $debounceWaitTime;
        $this->debounceQueueName = $debounce['queue']['name'] ?? 'default';
        $this->debounceQueueConnection = $debounce['queue']['connection'] ?? $defaultQueue;
    }


    public static function make(string $name): self
    {
        return new static($name);
    }


    public function setDefault(mixed $defaultValue): self
    {
        $this->default = $defaultValue;

        return $this;
    }

    public function cache(Closure $closure): self
    {
        $this->cacheClosure = $closure;

        return $this;
    }

    public function setDriver(string $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function isQueueable(bool $status = true, string $onConnection = '', string $onQueue = ''): self
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

    public function debounce(bool $status = true, int $waitTime = 5, string $onConnection = '', string $onQueue = ''): self
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


    # observers
    public function refreshAfterCreate(bool $status = true): self
    {
        $this->refreshAfterCreate = $status;

        return $this;
    }

    public function refreshAfterUpdate(bool $status = true): self
    {
        $this->refreshAfterUpdate = $status;

        return $this;
    }

    public function refreshAfterDelete(bool $status = true): self
    {
        $this->refreshAfterDelete = $status;

        return $this;
    }

    public function refreshAfterRestore(bool $status = true): self
    {
        $this->refreshAfterRestore = $status;

        return $this;
    }


    # expiration
    public function ttl(int $seconds): self
    {
        $this->ttl = max($seconds, 0);
        $this->forever = $this->ttl === 0;
        $this->validForRestOfDay = false;
        $this->validForRestOfWeek = false;

        return $this;
    }

    public function forever(): self
    {
        $this->forever = true;
        $this->validForRestOfDay = false;
        $this->validForRestOfWeek = false;
        $this->ttl = 0;

        return $this;
    }

    public function validForRestOfDay(): self
    {
        $this->validForRestOfDay = true;
        $this->validForRestOfWeek = false;
        $this->forever = false;
        $this->ttl = 0;

        return $this;
    }

    public function validForRestOfWeek(): self
    {
        $this->validForRestOfDay = false;
        $this->validForRestOfWeek = true;
        $this->forever = false;
        $this->ttl = 0;

        return $this;
    }
}
