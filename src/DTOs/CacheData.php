<?php

namespace Mostafaznv\LaraCache\DTOs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Mostafaznv\LaraCache\CacheEntity;

class CacheData
{
    public function __construct(
        public CacheStatus $status,
        public ?int        $expiration,
        public mixed       $value
    ) {}

    public static function make(CacheStatus $status, int $ttl, mixed $value): self
    {
        $expiration = $ttl ? Carbon::now()->addSeconds($ttl)->unix() : null;

        return new static($status, $expiration, $value);
    }

    public static function fromCache(CacheEntity $entity, string $driver): self
    {
        $value = Cache::store($driver)->get($entity->name, $entity->default);

        if ($value === $entity->default) {
            return self::make(CacheStatus::NOT_CREATED(), 0, $entity->default);
        }

        return $value;
    }
}
