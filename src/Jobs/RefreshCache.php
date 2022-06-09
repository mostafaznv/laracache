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

    protected Model  $model;
    protected string $name;
    protected string $event;

    public function __construct(Model $model, string $name, string $event)
    {
        $this->model = $model;
        $this->name = $name;
        $this->event = $event;
    }

    public function handle()
    {
        $this->model->cache()->update($this->name, $this->event);
    }
}
