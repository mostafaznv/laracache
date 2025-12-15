<?php

namespace Mostafaznv\LaraCache\Exceptions;

use InvalidArgumentException;


class ModelOptionIsRequired extends InvalidArgumentException
{
    public static function make(): static
    {
        return new static('The --model option is required.');
    }
}
