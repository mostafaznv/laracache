<?php

namespace Mostafaznv\LaraCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class UpdateLaraCacheModelsList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $driver;
    private string $key;

    public const LARACACHE_MODELS_LIST = 'laracache.models';

    public function __construct(private string $model)
    {
        $this->driver = config('laracache.driver') ?: config('cache.default');
        $this->key = self::LARACACHE_MODELS_LIST;
    }


    public function handle(): void
    {
        $list = Cache::driver($this->driver)->get($this->key, []);
        $list[] = $this->model;

        Cache::driver($this->driver)->forever(
            key: 'laracache.models',
            value: array_unique($list)
        );
    }
}
