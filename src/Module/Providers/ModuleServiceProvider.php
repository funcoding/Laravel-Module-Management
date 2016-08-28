<?php

namespace Sarav\Providers;

use Illuminate\Support\ServiceProvider;
use Sarav\Console\ModuleMakeCommand;

class ModuleServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerModuleCommand();
    }

    private function setupConfig()
    {
        $source = realpath(__DIR__.'/../config/module.php');

        $this->publishes([$source => config_path('module.php')]);
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerModuleCommand()
    {
        $this->app->singleton('command.module.make', function ($app) {
            return new ModuleMakeCommand($app['files'], $app['composer']);
        });

        $this->commands(['command.module.make']);
    }
}
