<?php

namespace Mostafaznv\LaraCache\Exceptions;

use InvalidArgumentException;


class ModelDoestNotExist extends InvalidArgumentException
{
    public static function make(string $model): static
    {
        return new static("The specified model [$model] was not found.");
    }
}
