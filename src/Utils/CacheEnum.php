<?php

namespace Mostafaznv\LaraCache\Utils;

abstract class CacheEnum
{
    public function __construct(protected int|string $value) {}

    public static function __callStatic(string $name, array $arguments): self
    {
        return new static($name);
    }

    public function getValue(): int|string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return get_class($this) === get_class($other) and $this->getValue() === $other->getValue();
    }
}
