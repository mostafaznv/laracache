<?php

namespace Mostafaznv\LaraCache;

use Illuminate\Support\ServiceProvider;
use Mostafaznv\LaraCache\Commands\DeleteCacheCommand;
use Mostafaznv\LaraCache\Commands\UpdateCacheCommand;

class LaraCacheServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/config.php' => config_path('laracache.php')], 'config');

            $this->commands([
                UpdateCacheCommand::class,
                DeleteCacheCommand::class
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laracache');

        $this->app->bind('laracache', function () {
            return new LaraCache;
        });
    }
}
