<?php

namespace Swiftmade\Idempotent\Tests\Support;

use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $this->app['config']->set('logging.channels.single', [
            'driver' => 'single',
            'path' => __DIR__ . '/../../laravel.log',
            'level' => 'debug',
        ]);

        $this->app['config']->set('cache.stores.recorded_responses', [
            'driver' => 'redis',
            'connection' => 'cache',
        ]);
    }
}
