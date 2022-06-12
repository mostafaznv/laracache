<?php

namespace Mostafaznv\LaraCache\Utils;

abstract class CacheEnum
{
    public function __construct(protected int|string $value) {}

    public static function __callStatic(string $name, array $arguments): self
    {
        return new static($name);
    }

    public function equals(self $other): bool
    {
        return get_class($this) === get_class($other) and $this->value === $other->value;
    }
}
