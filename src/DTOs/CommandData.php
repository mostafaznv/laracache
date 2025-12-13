<?php

namespace Mostafaznv\LaraCache\DTOs;

use Mostafaznv\LaraCache\Exceptions\EntityIsNotAllowed;
use Mostafaznv\LaraCache\Exceptions\ModelDoesntUseLaraCacheTrait;
use Mostafaznv\LaraCache\Exceptions\ModelDoestNotExist;
use Mostafaznv\LaraCache\Exceptions\ModelOptionIsRequired;
use Mostafaznv\LaraCache\Traits\LaraCache;


class CommandData
{
    public function __construct(
        /**
         * @var LaraCache[] $models
         */
        public array $models,
        public array $entities
    ) {}


    public static function make(array $models, array $entities = []): self
    {
        $modelsCount = count($models);

        if ($modelsCount === 0) {
            throw ModelOptionIsRequired::make();
        }


        if ($modelsCount > 1 and count($entities)) {
            throw EntityIsNotAllowed::make();
        }

        $m = [];

        foreach ($models as $model) {
            $m[] = self::model($model);
        }

        return new static($m, $entities);
    }

    private static function model(?string $model): string
    {
        if ($model) {
            if (self::modelExists($model)) {
                return $model;
            }

            // @codeCoverageIgnoreStart
            $defaultPath = "App\\Models\\$model";

            if (self::modelExists($defaultPath)) {
                return $defaultPath;
            }
            // @codeCoverageIgnoreEnd
        }

        throw ModelDoestNotExist::make($model);
    }

    private static function modelExists(string $model): bool
    {
        if (class_exists($model)) {
            if (method_exists($model, 'cache')) {
                return true;
            }

            throw ModelDoesntUseLaraCacheTrait::make($model);
        }

        return false;
    }
}
