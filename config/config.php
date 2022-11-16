<?php


use IvanoMatteo\LaravelDeviceTracking\DeviceHijackingDetectorDefault;

return [
    // if user_model is null, will be probed: App\Model\User and then App\User
    'user_model' => null,

    'detect_on_login' => true,

    // the device identifier cookie
    'device_cookie' => 'device_uuid',

    'session_key' => 'laravel-device-tracking',
    'session_cache_minutes' => 5,

    // must implement: IvanoMatteo\LaravelDeviceTracking\DeviceHijackingDetector
    'hijacking_detector' => DeviceHijackingDetectorDefault::class,
];
