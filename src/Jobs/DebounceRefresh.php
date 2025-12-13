<?php

namespace Mostafaznv\LaraCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;


class DebounceRefresh implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $key,
        public string $token,
        public string $model,
        public string $name,
    ) {}


    public function handle(): void
    {
        $latestToken = Cache::get($this->key);

        if ($latestToken !== $this->token) {
            return;
        }

        app($this->model)->cache()->update($this->name);
    }
}
