<?php

namespace Mostafaznv\LaraCache\DTOs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Enums\CacheStatus;


class CacheData
{
    public function __construct(
        public CacheStatus $status,
        public ?int        $expiration,
        public mixed       $value,
    ) {}


    public static function make(CacheStatus $status, int $ttl, mixed $value): self
    {
        $expiration = $ttl > 0 ? Carbon::now()->addSeconds($ttl)->unix() : null;

        return new static($status, $expiration, $value);
    }

    public static function fromCache(CacheEntity $entity, string $prefix, int $ttl = 0): self
    {
        $name = $prefix . '.' . $entity->name;
        $value = Cache::store($entity->driver)->get($name, $entity->default);

        if ($value === $entity->default) {
            return self::make(CacheStatus::NOT_CREATED, $ttl, $entity->default);
        }

        return $value;
    }
}
