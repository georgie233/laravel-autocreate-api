<?php

namespace Georgie\AutoAPi;

use Georgie\AutoAPi\Commands\AuthCommand;
use Georgie\AutoAPi\Commands\AutoAPiCommand;
use Georgie\AutoAPi\Commands\AutoNameCreateCommand;
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
                AutoCreateCommand::class,
                AutoNameCreateCommand::class,
                AuthCommand::class,
                InitCommand::class,

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
        $this->app->singleton('GAutoCreate', function () {
            return new Provider();
        });
    }
}
