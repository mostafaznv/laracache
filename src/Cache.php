<?php

namespace Mostafaznv\LaraCache;

use Mostafaznv\LaraCache\DTOs\CacheData;
use Mostafaznv\LaraCache\Enums\CacheEvent;
use Mostafaznv\LaraCache\Jobs\RefreshCache;
use Mostafaznv\LaraCache\Traits\InteractsWithCache;
use Mostafaznv\LaraCache\Utils\RefreshDebouncer;

class Cache
{
    use InteractsWithCache;

    public function get(string $name, bool $withCacheData = false): mixed
    {
        $cache = $this->retrieve($name);

        if ($withCacheData) {
            return $cache;
        }

        return $cache->value;
    }

    public function update(string $name): CacheData
    {
        $this->updateLaraCacheModelsList();

        return $this->updateCacheEntity($name);
    }

    public function updateAll(): void
    {
        $this->updateLaraCacheModelsList();

        foreach ($this->model::cacheEntities() as $entity) {
            $this->updateCacheEntity(
                name: $entity->name,
                entity: $entity
            );
        }
    }

    public function delete(string $name, bool $forever = false): CacheData
    {
        return $this->deleteCacheEntity($name, $forever);
    }

    public function deleteAll(bool $forever = false): void
    {
        foreach ($this->model::cacheEntities() as $entity) {
            $this->deleteCacheEntity($entity->name, $forever, $entity);
        }
    }

    public function refresh(CacheEvent $event): void
    {
        if ($this->model::$isEnabled) {
            $this->updateLaraCacheModelsList();

            foreach ($this->model::cacheEntities() as $entity) {
                if ($entity->debounce) {
                    $this->initCache($entity, $entity->getTtl());

                    RefreshDebouncer::dispatch(
                        model: $this->model,
                        name: $entity->name,
                        queueConnection: $entity->queueConnection,
                        queueName: $entity->queueName,
                        wait: $entity->debounceWaitTime
                    );
                }
                else if ($entity->isQueueable) {
                    $this->initCache($entity, $entity->getTtl());

                    RefreshCache::dispatch($this->model, $entity->name, $event)
                        ->onConnection($entity->queueConnection)
                        ->onQueue($entity->queueName);
                }
                else {
                    $this->updateCacheEntity($entity->name, $event, $entity);
                }
            }
        }
    }

    public function disable(): void
    {
        $this->model::$isEnabled = false;
    }

    public function enable(): void
    {
        $this->model::$isEnabled = true;
    }
}
