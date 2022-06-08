<?php

namespace Mostafaznv\LaraCache\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Mostafaznv\LaraCache\LaraCacheServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    /**
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaraCacheServiceProvider::class,
        ];
    }

    /**
     * @param Application $app
     */
    public function getEnvironmentSetUp($app)
    {
        config()->set('cache.default', 'array');

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * @param Application $app
     */
    protected function setUpDatabase(Application $app)
    {
        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('test_models', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('content', 500)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
    }
}
