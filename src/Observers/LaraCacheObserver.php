<?php

namespace Mostafaznv\LaraCache\Observers;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\LaraCache\Enums\CacheEvent;
use Mostafaznv\LaraCache\Traits\LaraCache;


final class LaraCacheObserver
{
    /**
     * @param Model&LaraCache $model
     */
    public function created(Model $model): void
    {
        $model->cache()->refresh(CacheEvent::CREATED);
    }

    /**
     * @param Model&LaraCache $model
     */
    public function updated(Model $model): void
    {
        if (!$this->isRestored($model)) {
            $model->cache()->refresh(CacheEvent::UPDATED);
        }
    }

    /**
     * @param Model&LaraCache $model
     */
    public function deleted(Model $model): void
    {
        $model->cache()->refresh(CacheEvent::DELETED);
    }

    /**
     * @param Model&LaraCache $model
     */
    public function restored(Model $model): void
    {
        $model->cache()->refresh(CacheEvent::RESTORED);
    }


    /**
     * @param Model&LaraCache $model
     */
    private function isRestored(Model $model): bool
    {
        return $model->wasChanged('deleted_at')
            and is_null($model->deleted_at)
            and !$model->originalIsEquivalent('deleted_at');
    }
}
