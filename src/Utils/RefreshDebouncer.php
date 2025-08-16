<?php

namespace Mostafaznv\LaraCache\Utils;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mostafaznv\LaraCache\Jobs\DebounceRefresh;


class RefreshDebouncer
{
    public static function dispatch(string $model, string $name, string $queueConnection, string $queueName, int $wait = 5): void
    {
        $key = "laracache.debounce.$model:token";

        $token = Str::uuid()->toString();
        $ttl = $wait + 60;

        Cache::put($key, $token, $ttl);

        DebounceRefresh::dispatch($key, $token, $model, $name)
            ->onConnection($queueConnection)
            ->onQueue($queueName)
            ->delay($wait);
    }
}
