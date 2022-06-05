<?php

namespace Mostafaznv\LaraCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Model $model;
    protected string $event;

    public function __construct(Model $model, string $event)
    {
        $this->model = $model;
        $this->event = $event;
    }

    public function handle()
    {
        foreach ($this->model->cacheEntities() as $entity) {
            $this->model->updateCache($entity->name, $this->event, $entity);
        }
    }
}
