<?php

namespace Mostafaznv\LaraCache\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\DTOs\CacheData;
use Mostafaznv\LaraCache\DTOs\CacheEvent;
use Mostafaznv\LaraCache\DTOs\CacheStatus;
use Mostafaznv\LaraCache\Exceptions\CacheEntityDoesNotExist;
use Mostafaznv\LaraCache\Jobs\RefreshCache;
use Mostafaznv\LaraCache\Jobs\UpdateLaraCacheModelsList;

trait InteractsWithCache
{
    private string $prefix;
    private mixed $model;
    private string $laracacheListKey;

    public function __construct(string $model)
    {
        $this->prefix = Str::kebab(class_basename($model));
        $this->model = $model;
        $this->laracacheListKey = config('laracache.laracache-list');
    }


    private function getEntityFullName(CacheEntity $entity): string
    {
        return $this->prefix . '.' . $entity->name;
    }

    private function findCacheEntity(string $name, ?CacheEntity $entity = null): CacheEntity
    {
        if ($entity) {
            return $entity;
        }

        foreach ($this->model::cacheEntities() as $cacheEntity) {
            if ($cacheEntity->name === $name) {
                return $cacheEntity;
            }
        }

        throw CacheEntityDoesNotExist::make($name, $this->model);
    }

    private function entityIsCallable(CacheEntity $entity, ?CacheEvent $event = null): bool
    {
        return is_null($event)
            or ($event->equals(CacheEvent::CREATED()) and $entity->refreshAfterCreate)
            or ($event->equals(CacheEvent::UPDATED()) and $entity->refreshAfterUpdate)
            or ($event->equals(CacheEvent::DELETED()) and $entity->refreshAfterDelete)
            or ($event->equals(CacheEvent::RESTORED()) and $entity->refreshAfterRestore);
    }

    private function callCacheClosure(CacheEntity $entity, int $ttl, bool $delete = false): CacheData
    {
        if ($delete) {
            return CacheData::make(CacheStatus::DELETED(), $ttl, $entity->default);
        }

        $value = $entity->cacheClosure ? call_user_func($entity->cacheClosure) : null;

        return CacheData::make(
            status: CacheStatus::CREATED(),
            ttl: $ttl,
            value: $value ?: $entity->default
        );
    }

    private function updateCacheEntitiesList(CacheEntity $entity): void
    {
        $name = $this->getEntityFullName($entity);
        $list = Cache::store($entity->driver)->get($this->laracacheListKey);

        if (is_array($list)) {
            if (isset($list[$this->model]) and is_array($list[$this->model])) {
                if (!in_array($name, $list[$this->model])) {
                    $list[$this->model][] = $name;
                }
            }
            else {
                $list[$this->model] = [$name];
            }
        }
        else {
            $list = [
                $this->model => [$name]
            ];
        }

        Cache::store($entity->driver)->forever($this->laracacheListKey, $list);
    }

    private function putCacheIntoCacheStorage(CacheData $cache, string $driver, string $name, int $ttl): bool
    {
        if (is_null($cache->expiration)) {
            return Cache::store($driver)->forever($name, $cache);
        }

        return Cache::store($driver)->put($name, $cache, $ttl);
    }

    private function initCache(CacheEntity $entity, int $ttl): void
    {
        $name = $this->getEntityFullName($entity);
        $cache = CacheData::fromCache($entity, $this->prefix, $ttl);

        if ($cache->status->equals(CacheStatus::NOT_CREATED())) {
            $cache->value = $entity->default;
        }

        $cache->status = CacheStatus::CREATING();

        $this->putCacheIntoCacheStorage($cache, $entity->driver, $name, $ttl);
    }

    private function storeCache(CacheData $cache, CacheEntity $entity, int $ttl): void
    {
        $name = $this->getEntityFullName($entity);

        $this->putCacheIntoCacheStorage($cache, $entity->driver, $name, $ttl);
        $this->updateCacheEntitiesList($entity);
    }

    private function updateLaraCacheModelsList(): void
    {
        UpdateLaraCacheModelsList::dispatch($this->model);
    }

    private function updateCacheEntity(string $name, ?CacheEvent $event = null, CacheEntity $entity = null): CacheData
    {
        $entity = $this->findCacheEntity($name, $entity);

        if ($this->entityIsCallable($entity, $event)) {
            $ttl = $entity->getTtl();

            $this->initCache($entity, $ttl);
            $cache = $this->callCacheClosure($entity, $ttl);
            $this->storeCache($cache, $entity, $ttl);

            return $cache;
        }

        return CacheData::fromCache($entity, $this->prefix);
    }

    private function deleteCacheEntity(string $name, bool $deleteForever = false, CacheEntity $entity = null): CacheData
    {
        $entity = $this->findCacheEntity($name, $entity);
        $ttl = !$deleteForever ? $entity->getTtl() : 0;
        $cache = $this->callCacheClosure($entity, $ttl, true);
        $this->storeCache($cache, $entity, $ttl);

        return $cache;
    }

    private function retrieve(string $name): CacheData
    {
        $entity = $this->findCacheEntity($name);
        $cache = CacheData::fromCache($entity, $this->prefix);

        if ($cache->status->equals(CacheStatus::NOT_CREATED())) {
            if ($entity->isQueueable) {
                $this->initCache($entity, $entity->getTtl());
                RefreshCache::dispatch($this->model, $entity->name, CacheEvent::RETRIEVED());

                return CacheData::fromCache($entity, $this->prefix, $entity->ttl);
            }

            return $this->updateCacheEntity($name, null, $entity);
        }

        return $cache;
    }
}
