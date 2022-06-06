<?php

namespace Mostafaznv\LaraCache\Observers;

use Mostafaznv\LaraCache\Cache;

final class LaraCacheObserver
{
    public function created(mixed $model): void
    {
        $model->cache()->refresh($model, Cache::$created);
    }

    public function updated(mixed $model): void
    {
        if (!$this->isRestored($model)) {
            $model->cache()->refresh($model, Cache::$updated);
        }
    }

    public function deleted(mixed $model): void
    {
        $model->cache()->refresh($model, Cache::$deleted);
    }

    public function restored(mixed $model): void
    {
        $model->cache()->refresh($model, Cache::$restored);
    }

    private function isRestored(mixed $model): bool
    {
        return $model->wasChanged('deleted_at')
            and is_null($model->deleted_at)
            and !$model->originalIsEquivalent('deleted_at');
    }
}
