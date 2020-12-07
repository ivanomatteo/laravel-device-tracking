<?php

namespace IvanoMatteo\LaravelDeviceTracking;

use Illuminate\Support\ServiceProvider;

class LaravelDeviceTrackingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('laravel-device-tracking.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laravel-device-tracking');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-device-tracking', function () {
            return new LaravelDeviceTracking;
        });

        if (config('laravel-device-tracking.detect_on_login')) {
            $this->app->register(EventServiceProvider::class);
        }
    }
}
