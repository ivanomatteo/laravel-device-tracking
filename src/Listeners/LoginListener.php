<?php

namespace IvanoMatteo\LaravelDeviceTracking\Listeners;


use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use IvanoMatteo\LaravelDeviceTracking\Facades\DeviceTracker;

class LoginListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if (Auth::guard('web')->check()) {
            $user = $event->user;
            if (!$user->deviceShouldBeDetected()) {
                return;
            }
            DeviceTracker::detectFindAndUpdate();
        }
    }
}
