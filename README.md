# A library that allows you to track different devices used per user

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ivanomatteo/laravel-device-tracking.svg?style=flat-square)](https://packagist.org/packages/ivanomatteo/laravel-device-tracking)

<!-- [![Build Status](https://img.shields.io/travis/ivanomatteo/laravel-device-tracking/master.svg?style=flat-square)](https://travis-ci.org/ivanomatteo/laravel-device-tracking)
[![Quality Score](https://img.shields.io/scrutinizer/g/ivanomatteo/laravel-device-tracking.svg?style=flat-square)](https://scrutinizer-ci.com/g/ivanomatteo/laravel-device-tracking)
 -->
[![Total Downloads](https://img.shields.io/packagist/dt/ivanomatteo/laravel-device-tracking.svg?style=flat-square)](https://packagist.org/packages/ivanomatteo/laravel-device-tracking)


This package implements a "google like" device detection.

You can detect when a user is using a new device and manage the verified status between user and device.

You can also detect a possible device hijacking.



## Installation

You can install the package via composer:

```bash

composer require ivanomatteo/laravel-device-tracking

```

Publish migrations:

```bash
php artisan vendor:publish --provider "IvanoMatteo\LaravelDeviceTracking\LaravelDeviceTrackingServiceProvider" --tag migrations
```

Run migrations:
```bash
php artisan migrate
```

Publish config file:

```bash
php artisan vendor:publish --provider "IvanoMatteo\LaravelDeviceTracking\LaravelDeviceTrackingServiceProvider" --tag config
```

## Usage

```php

use IvanoMatteo\LaravelDeviceTracking\Facades\DeviceTracker;
use IvanoMatteo\LaravelDeviceTracking\Traits\UseDevices;

// add the trait to your user model
class User {
    use UseDevices;
}


// call on login or when you want update and check the device informations
// by default this function is called when the Login event is fired 
// only with the "web" auth guard
// if you want you can disable the detect_on_login option in the config file
$device = DeviceTracker::detectFindAndUpdate();


// flag as verified for the current user
DeviceTracker::flagCurrentAsVerified();

// flag as verified for a specific user
DeviceTracker::flagAsVerified($device, $user_id);

// flag as verified for a specific user by device uuid
DeviceTracker::flagAsVerifiedByUuid($device_uuid, $user_id);


```

If you are using Session Authentication it's possible to add the middleware
**IvanoMatteo\LaravelDeviceTracking\Http\Middleware\DeviceTrackerMiddleware** in app/Http/Kernel.php, at the end of **web** group.

This way, the device will also be checked for **subsequents** requests to the login request.
**DeviceTrackerMiddleware** will store the md5(request()->ip() . $device_uuid . $user_agent ) inside the session
so the detection will be executed again only if the hash does not match.  



Following events can be emitted:

* **DeviceCreated**

    when a new device is detected and stored

* **DeviceUpdated**

    when some information of a device is changed

* **DeviceHijacked**

    when critical device information is changed.
    You can also define a custom **DeviceHijackingDetector**.
    After this event, the device will be updated, and the next time, DeviceHijacked
    will not be emitted, but the device will have the field **device_hijacked_at**
    with the last DeviceHijacked event timestamp.

* **UserSeenFromNewDevice**

    when a user is detected on a device for the first time 

* **UserSeenFromUnverifiedDevice**

    when a user is detected on a device not for the first time and the device is not flagged as verified

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email ivanomatteo@gmail.com instead of using the issue tracker.

## Credits

-   [Ivano Matteo](https://github.com/ivanomatteo)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
