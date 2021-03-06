<?php

namespace Georgie\AutoAPi;

use Georgie\AutoAPi\Commands\AuthCommand;
use Georgie\AutoAPi\Commands\AutoApiCommand;
use Georgie\AutoAPi\Commands\InitCommand;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public $singletons = [];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InitCommand::class,
                AuthCommand::class,
                AutoApiCommand::class
            ]);
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('GAutoApi', function () {
            return new Provider();
        });
    }
}
