<?php

namespace Mostafaznv\LaraCache\DTOs;

/**
 * @method static self NOT_CREATED()
 * @method static self CREATING()
 * @method static self CREATED()
 * @method static self DELETED()
 */
class CacheStatus
{

    public function __construct(protected int|string $value) {}

    public static function __callStatic(string $name, array $arguments): self
    {
        return new static($name);
    }


    public function equals(CacheStatus $other): bool
    {
        return get_class($this) === get_class($other) and $this->value === $other->value;
    }
}
