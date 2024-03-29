<?php

namespace Mostafaznv\LaraCache;

use Illuminate\Support\ServiceProvider;
use Mostafaznv\LaraCache\Commands\DeleteCacheCommand;
use Mostafaznv\LaraCache\Commands\DeleteGroupCacheCommand;
use Mostafaznv\LaraCache\Commands\UpdateCacheCommand;
use Mostafaznv\LaraCache\Commands\UpdateGroupCacheCommand;

class LaraCacheServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/config.php' => config_path('laracache.php')], 'config');
        }

        $this->commands([
            UpdateCacheCommand::class,
            DeleteCacheCommand::class,
            UpdateGroupCacheCommand::class,
            DeleteGroupCacheCommand::class
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laracache');

        $this->app->bind('laracache', function () {
            return new LaraCache;
        });
    }
}
