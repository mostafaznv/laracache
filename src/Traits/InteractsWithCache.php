<?php

namespace Mostafaznv\LaraCache\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\DTOs\CacheData;
use Mostafaznv\LaraCache\DTOs\CacheEvent;
use Mostafaznv\LaraCache\DTOs\CacheStatus;
use Mostafaznv\LaraCache\Exceptions\CacheEntityDoesNotExist;

trait InteractsWithCache
{
    private string $prefix;
    private mixed  $model;
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

    private function storeCache(CacheData $cache, CacheEntity $entity, int $ttl): void
    {
        $name = $this->getEntityFullName($entity);

        is_null($cache->expiration)
            ? Cache::store($entity->driver)->forever($name, $cache)
            : Cache::store($entity->driver)->put($name, $cache, $ttl);

        $this->updateCacheEntitiesList($entity);
    }

    private function updateCacheEntity(string $name, ?CacheEvent $event = null, CacheEntity $entity = null): CacheData
    {
        $entity = $this->findCacheEntity($name, $entity);

        if ($this->entityIsCallable($entity, $event)) {
            $ttl = $entity->getTtl();
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
            return $this->updateCacheEntity($name, null, $entity);
        }

        return $cache;
    }
}
