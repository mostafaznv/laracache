<?php

namespace Mostafaznv\LaraCache\Exceptions;

use InvalidArgumentException;


class EntityIsNotAllowed extends InvalidArgumentException
{
    public static function make(): static
    {
        return new static('The specified entity is not permitted to perform this operation.');
    }
}
