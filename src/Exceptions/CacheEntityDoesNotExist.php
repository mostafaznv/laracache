<?php

namespace Mostafaznv\LaraCache\Exceptions;

use InvalidArgumentException;


class CacheEntityDoesNotExist extends InvalidArgumentException
{
    public static function make(string $name, string $model): static
    {
        return new static("Cache entity [$name] was not found. Please verify that it is defined on model [$model].");
    }
}
