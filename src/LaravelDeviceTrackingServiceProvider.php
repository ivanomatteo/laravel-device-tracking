<?php

namespace IvanoMatteo\LaravelDeviceTracking;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class LaravelDeviceTrackingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        //$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('laravel-device-tracking.php'),
            ], 'config');

            $stubs = collect(glob(__DIR__ . '/../database/migrations/*.stub'))
                ->map(function ($stub) {
                    return basename($stub);
                })
                ->sort();

            $count = 0;
            foreach ($stubs as $stub) {
                $targetMigrationSuffix = substr(basename($stub, '.stub'), 4);
                $targetGlob = database_path("migrations/*_" . $targetMigrationSuffix);

                if (!empty(glob($targetGlob))) {
                    continue;
                }

                $this->publishes([
                    __DIR__ . '/../database/migrations/' . $stub
                    =>
                    database_path('migrations/' . date('Y_m_d_His', (time() + $count)) . '_' . $targetMigrationSuffix),
                ], 'migrations');

                $count++;
            }
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
