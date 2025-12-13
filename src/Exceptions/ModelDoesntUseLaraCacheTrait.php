<?php

namespace Mostafaznv\LaraCache\Exceptions;

use InvalidArgumentException;


class ModelDoesntUseLaraCacheTrait extends InvalidArgumentException
{
    public static function make(string $model): static
    {
        return new static("The specified model [$model] does not use the `LaraCache` trait.");
    }
}
