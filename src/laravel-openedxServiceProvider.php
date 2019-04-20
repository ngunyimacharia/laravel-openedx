<?php

namespace ngunyimacharia\laravel-openedx;

use Illuminate\Support\ServiceProvider;

class laravel-openedxServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'ngunyimacharia');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'ngunyimacharia');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-openedx.php', 'laravel-openedx');

        // Register the service the package provides.
        $this->app->singleton('laravel-openedx', function ($app) {
            return new laravel-openedx;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravel-openedx'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laravel-openedx.php' => config_path('laravel-openedx.php'),
        ], 'laravel-openedx.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/ngunyimacharia'),
        ], 'laravel-openedx.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/ngunyimacharia'),
        ], 'laravel-openedx.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/ngunyimacharia'),
        ], 'laravel-openedx.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
