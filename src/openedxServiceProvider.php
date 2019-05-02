<?php

namespace ngunyimacharia\openedx;

use Event;
use App\User;
use ngunyimacharia\openedx\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class openedxServiceProvider extends ServiceProvider
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
        //Register database
        \Config::set(
            'database.connections.edx_mysql',
            \Config::get('openedx.db.edx_mysql')
        );
        //Register observer
        User::observe(UserObserver::class);

        //Listen for login and logout
        Event::listen('Illuminate\Auth\Events\Login', 'ngunyimacharia\openedx\Listeners\SuccessfulLogin');
        Event::listen('Illuminate\Auth\Events\Verified', 'ngunyimacharia\openedx\Listeners\SuccessfulVerified');
        Event::listen('Illuminate\Auth\Events\Logout', 'ngunyimacharia\openedx\Listeners\SuccessfulLogout');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/openedx.php', 'openedx');

        // Register the service the package provides.
        $this->app->singleton('openedx', function ($app) {
            return new openedx;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['openedx'];
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
            __DIR__ . '/../config/openedx.php' => config_path('openedx.php'),
        ], 'openedx.config');

        // Publishing assets.
        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('vendor/ngunyimacharia'),
        ], 'openedx.views');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/ngunyimacharia'),
        ], 'openedx.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/ngunyimacharia'),
        ], 'openedx.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
