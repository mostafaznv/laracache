<?php

namespace Mostafaznv\LaraCache;

use Illuminate\Support\ServiceProvider;

class LaraCacheServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/config.php' => config_path('laracache.php')], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laracache');

        $this->app->bind('laracache', function () {
            return new LaraCache;
        });
    }
}
