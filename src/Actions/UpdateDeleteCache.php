<?php

namespace Mostafaznv\LaraCache\Actions;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

abstract class UpdateDeleteCache
{
    public function __construct(protected ?Command $console) {}

    public static function make(?Command $console = null): self
    {
        return new static($console);
    }


    protected function title(string $string): string
    {
        return Str::title(
            Str::slug(
                title: Str::replace(['.', '-', '_'], ' ', $string),
                separator: ' '
            )
        );
    }
}
