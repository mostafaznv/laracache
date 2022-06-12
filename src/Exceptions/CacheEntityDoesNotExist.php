<?php

namespace Mostafaznv\LaraCache\Exceptions;

use InvalidArgumentException;

class CacheEntityDoesNotExist extends InvalidArgumentException
{
    public static function make(string $name, string $model): static
    {
        return new static("Cache entity `{$name}` not found. please check if `{$name}` exists in `{$model}`");
    }
}
