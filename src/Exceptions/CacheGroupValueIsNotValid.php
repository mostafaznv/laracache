<?php

namespace Mostafaznv\LaraCache\Exceptions;

use InvalidArgumentException;


class CacheGroupValueIsNotValid extends InvalidArgumentException
{
    public static function make(string $group): static
    {
        return new static("The provided group [$group] is invalid.");
    }
}
