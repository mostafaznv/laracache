<?php

namespace Mostafaznv\LaraCache\Observers;

use Mostafaznv\LaraCache\DTOs\CacheEvent;

final class LaraCacheObserver
{
    public function created(mixed $model): void
    {
        $model->cache()->refresh(CacheEvent::CREATED());
    }

    public function updated(mixed $model): void
    {
        if (!$this->isRestored($model)) {
            $model->cache()->refresh(CacheEvent::UPDATED());
        }
    }

    public function deleted(mixed $model): void
    {
        $model->cache()->refresh(CacheEvent::DELETED());
    }

    public function restored(mixed $model): void
    {
        $model->cache()->refresh(CacheEvent::RESTORED());
    }

    private function isRestored(mixed $model): bool
    {
        return $model->wasChanged('deleted_at')
            and is_null($model->deleted_at)
            and !$model->originalIsEquivalent('deleted_at');
    }
}
