<?php

/*
 * You can place your custom package configuration in here.
 */

use IvanoMatteo\LaravelDeviceTracking\DeviceHijackingDetectorDefault;

return [
    'user_model' => 'App\User',
    'device_cookie' => 'device_uuid',
    'hijacking_detector' => DeviceHijackingDetectorDefault::class,
];
