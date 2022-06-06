<?php

namespace Mostafaznv\LaraCache;

class LaraCache
{
    /**
     * Update Cache
     *
     * @param mixed $model
     * @param string $name
     * @param string $event
     * @param CacheEntity|null $entity
     *
     * @return mixed
     */
    public function update(mixed $model, string $name, string $event = '', CacheEntity $entity = null): mixed
    {
        return $model::cache()->update($name, $event, $entity);
    }

    /**
     * Update All Cache Entities
     *
     * @param mixed $model
     */
    public function updateAll(mixed $model): void
    {
        $model::cache()->updateAll();
    }

    /**
     * Retrieve Cache
     *
     * @param mixed $model
     * @param string $name
     *
     * @return mixed
     */
    public function retrieve(mixed $model, string $name): mixed
    {
        return $model::cache()->get($name);
    }

    /**
     * Disable refresh cache on all events
     *
     * @param $model
     */
    public function disable($model): void
    {
        $model::cache()->disable();
    }

    /**
     * Enable refresh cache on all events
     *
     * @param $model
     */
    public function enable($model): void
    {
        $model::cache()->enable();
    }
}
