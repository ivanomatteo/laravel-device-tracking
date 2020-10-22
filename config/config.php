<?php


use IvanoMatteo\LaravelDeviceTracking\DeviceHijackingDetectorDefault;

return [
    // if user_model is null, will be probed: App\Model\User and then App\User
    'user_model' => null, 

    // the device identifier cookie
    'device_cookie' => 'device_uuid',

    'session_key' => 'laravel-device-tracking',

    // must implement: IvanoMatteo\LaravelDeviceTracking\DeviceHijackingDetector
    'hijacking_detector' => DeviceHijackingDetectorDefault::class,
];
