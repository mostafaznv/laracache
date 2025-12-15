<?php

namespace Mostafaznv\LaraCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class RefreshCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $model,
        private readonly string $name,
    ) {}


    public function handle(): void
    {
        $model = app($this->model);

        $model->cache()->update($this->name);
    }
}
