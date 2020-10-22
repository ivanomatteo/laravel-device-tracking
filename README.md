# A library that allow to track different devices used

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ivanomatteo/laravel-device-tracking.svg?style=flat-square)](https://packagist.org/packages/ivanomatteo/laravel-device-tracking)

<!-- [![Build Status](https://img.shields.io/travis/ivanomatteo/laravel-device-tracking/master.svg?style=flat-square)](https://travis-ci.org/ivanomatteo/laravel-device-tracking)
[![Quality Score](https://img.shields.io/scrutinizer/g/ivanomatteo/laravel-device-tracking.svg?style=flat-square)](https://scrutinizer-ci.com/g/ivanomatteo/laravel-device-tracking)
 -->
[![Total Downloads](https://img.shields.io/packagist/dt/ivanomatteo/laravel-device-tracking.svg?style=flat-square)](https://packagist.org/packages/ivanomatteo/laravel-device-tracking)


This package implement a "google like" device detection.

You can detect when an user is using a new device and manage the verified status between user and device.

You can also detect a possible device hijacking.



## Installation

You can install the package via composer:

```bash

composer require ivanomatteo/laravel-device-tracking

php artisan migrate

```

Publish config file:

```bash

php artisan vendor:publish --provider "IvanoMatteo\LaravelDeviceTracking\LaravelDeviceTrackingServiceProvider" --tag config

```

## Usage

```php

//call on login or when you want update and check the device informations
$device = \DeviceTracker::detectFindAndUpdate();


// flag as verfified for the current user
\DeviceTracker::flagCurrentAsVerified();

// flag as verfified for a specific user
\DeviceTracker::flagAsVerified($device, $user_id);

// flag as verfified for a specific user by device uuid
\DeviceTracker::flagAsVerifiedByUuid($device_uuid, $user_id);



// if you are using laravel/ui (classic scaffolding)
// a good place where detectFindAndUpdate() is in the login controller
// App\Http\Controllers\Auth\LoginController
// by adding this method:
protected function authenticated(Request $request, $user)
{
    $device = \DeviceTracker::detectFindAndUpdate();

    //
}

```

If you are using Session Autentication it's possible to add the middleware
**IvanoMatteo\LaravelDeviceTracking\Http\Middleware\DeviceDetectMiddleware** in app/Http/Kernel.php, at the end of **web** group.

In this way, the device will be checked also for requests **subsequents** to the login request.
**DeviceDetectMiddleware** will store the md5( $device_uuid . $user_agent ) inside the session
so the detection will be executed again only if the hash do not match  





Following events could be emitted:

* **DeviceCreated**

    when a new device is detected and stored

* **DeviceUpdated**

    when some information of a device is changed

* **DeviceHijacked**

    when critical device information are changed.
    You ca also define a custom **DeviceHijackingDetector**.
    After this event, the device will be updated, and the next time, DeviceHijacked
    will not be emitted, but the device will have the field **device_hijacked_at**
    with the last DeviceHijacked event timestamp.

* **UserSeenFromNewDevice**

    when an user is detected on a device for the first time 

* **UserSeenFromUnverifiedDevice**

    when an user is detected on a device not for the first time and the device is not flagged as verified

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
