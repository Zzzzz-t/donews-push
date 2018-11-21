<?php

namespace tlsss\DoNewsPush;

use Illuminate\Support\ServiceProvider;
use tlsss\DoNewsPush\Contracts\DoNewsPusher;
use tlsss\DoNewsPush\Push;

class DoNewsPushServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/push.php' => config_path('push.php'),
        ]);
        // $this->loadRoutesFrom(__DIR__ . '/Routes.php');
    }

    public function register()
    {
        $this->app->singleton(DoNewsPusher::class, function ($app) {
            return new Push();
        });
    }

    public function provides()
    {
        return [DoNewsPusher::class];
    }
}