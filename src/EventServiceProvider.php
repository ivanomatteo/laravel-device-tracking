<?php
namespace IvanoMatteo\LaravelDeviceTracking;

use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use IvanoMatteo\LaravelDeviceTracking\Listeners\LoginListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LoginListener::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
