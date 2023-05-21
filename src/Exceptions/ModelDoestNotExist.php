<?php

namespace Mostafaznv\LaraCache\Exceptions;

use InvalidArgumentException;

class ModelDoestNotExist extends InvalidArgumentException
{
    public static function make(string $model): static
    {
        return new static("Target model [$model] does not exist.");
    }
}
