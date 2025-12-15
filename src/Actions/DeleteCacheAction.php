<?php

namespace Mostafaznv\LaraCache\Actions;

use Mostafaznv\LaraCache\Actions\Support\UpdateDeleteCache;
use Mostafaznv\LaraCache\DTOs\CommandData;
use Mostafaznv\LaraCache\Traits\LaraCache;


class DeleteCacheAction extends UpdateDeleteCache
{
    public function run(CommandData $data): void
    {
        if (count($data->models) > 1) {
            foreach ($data->models as $model) {
                $this->deleteAll($model);
            }
        }
        else {
            $model = $data->models[0];

            empty($data->entities)
                ? $this->deleteAll($model)
                : $this->delete($model, $data->entities);
        }
    }

    /**
     * @param LaraCache $model
     * @return void
     */
    private function deleteAll(string $model): void
    {
        $entities = [];

        foreach ($model::cacheEntities() as $entity) {
            $entities[] = $entity->name;
        }

        $this->delete($model, $entities);
    }

    /**
     * @param LaraCache $model
     * @param array $entities
     * @return void
     */
    private function delete(string $model, array $entities): void
    {
        $this->console?->warn(
            sprintf('>> Deleting cache entities in [%s] model', class_basename($model))
        );

        foreach ($entities as $entity) {
            $this->console?->line('— ' . $this->title($entity));

            $model::cache()->delete($entity);

            $this->console?->info('Deleted');
        }
    }
}
