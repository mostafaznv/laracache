<?php

namespace Mostafaznv\LaraCache\Actions;

use Mostafaznv\LaraCache\Actions\Support\UpdateDeleteCache;
use Mostafaznv\LaraCache\DTOs\CommandData;

class UpdateCacheAction extends UpdateDeleteCache
{
    public function run(CommandData $data): void
    {
        if (count($data->models) > 1) {
            foreach ($data->models as $model) {
                $this->updateAll($model);
            }
        }
        else {
            $model = $data->models[0];

            empty($data->entities)
                ? $this->updateAll($model)
                : $this->update($model, $data->entities);
        }
    }

    /**
     * @param \Mostafaznv\LaraCache\Traits\LaraCache $model
     * @return void
     */
    private function updateAll(string $model): void
    {
        $entities = [];

        foreach ($model::cacheEntities() as $entity) {
            $entities[] = $entity->name;
        }

        $this->update($model, $entities);
    }

    /**
     * @param \Mostafaznv\LaraCache\Traits\LaraCache $model
     * @param array $entities
     * @return void
     */
    private function update(string $model, array $entities): void
    {
        $this->console?->warn(
            sprintf('>> Updating cache entities in [%s] model', class_basename($model))
        );

        foreach ($entities as $entity) {
            $this->console?->line('— ' . $this->title($entity));

            $model::cache()->update($entity);

            $this->console?->info('Updated');
        }
    }
}
