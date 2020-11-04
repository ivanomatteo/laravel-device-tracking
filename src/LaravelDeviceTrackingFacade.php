<?php

namespace IvanoMatteo\LaravelDeviceTracking;

use Illuminate\Support\Facades\Facade;

/**
 */
class LaravelDeviceTrackingFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-device-tracking';
    }
}
