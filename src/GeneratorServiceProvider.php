<?php

namespace ChuJC\LaravelGenerator;

use ChuJC\AMapDistrict\Console\DistrictCommand;
use ChuJC\AMapDistrict\Console\DistrictTableCommand;
use Illuminate\Support\ServiceProvider;

class GeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/config/generator.php';

        $this->mergeConfigFrom($configPath, 'generator');

        if (function_exists('config_path')) {
            $publishPath = config_path('generator.php');
        } else {
            $publishPath = base_path('config/generator.php');
        }

        $this->publishes([$configPath => $publishPath], 'config');

        $this->app->singleton('command.chujc.models', function ($app) {
            return $app['ChuJC\LaravelGenerator\Commands\MakeModelsCommand'];
        });

        $this->app->singleton('command.chujc.api', function ($app) {
            return $app['ChuJC\LaravelGenerator\Commands\MakeAPICommand'];
        });

        $this->commands(['command.chujc.models', 'command.chujc.api']);

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
