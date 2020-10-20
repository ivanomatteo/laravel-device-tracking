# A library that allow to track different devices used

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ivanomatteo/laravel-device-tracking.svg?style=flat-square)](https://packagist.org/packages/ivanomatteo/laravel-device-tracking)
[![Build Status](https://img.shields.io/travis/ivanomatteo/laravel-device-tracking/master.svg?style=flat-square)](https://travis-ci.org/ivanomatteo/laravel-device-tracking)
[![Quality Score](https://img.shields.io/scrutinizer/g/ivanomatteo/laravel-device-tracking.svg?style=flat-square)](https://scrutinizer-ci.com/g/ivanomatteo/laravel-device-tracking)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanomatteo/laravel-device-tracking.svg?style=flat-square)](https://packagist.org/packages/ivanomatteo/laravel-device-tracking)


This package implement a "google like" device detection.

you can detect when an user is using a new device, and manage the verified status between user and device.

you can also detect a possible device hijacking.



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
$device = \DeviceTracker::findDetectAndUpdate();

//

// flag as verfified for the current user
$device->currentUserStatus->verified_at = now();
$device->currentUserStatus->save();

// flag as verfified for a specific user
$status = $device->pivot()->where('user_id','=',$specific_user_id)->first();
$status->verified_at = now();
$status->save();


// if you are using laravel/ui (classic scaffolding)
// a good place where to trigger the detection is inside 
// App\Http\Controllers\Auth\LoginController
// by adding this method:
protected function authenticated(Request $request, $user)
{
    $device = \DeviceTracker::findDetectAndUpdate();

    //
}

/*
    It's also possible to call findDetectAndUpdate() on every request, but 
    in order to reduce the overhead (that anyway is not much) I suggest to call it
    just on log in, and before important actions. 
    In these situations you could also log the current device information.
*/


```

Following events could be emitted:

* IvanoMatteo\LaravelDeviceTracking\Events\DeviceCreated

When a new device is detected and stored

* IvanoMatteo\LaravelDeviceTracking\Events\DeviceUpdated

When some information of a device is changed

* IvanoMatteo\LaravelDeviceTracking\Events\DeviceHijacked

When critical device information are change basing on the logic of
the configured IvanoMatteo\LaravelDeviceTracking\DeviceHijackingDetector
after this event, the device will be updated, and the next time 

* IvanoMatteo\LaravelDeviceTracking\Events\UserSeenFromNewDevice

when an user is detected on a device for the first time DeviceHijacked
will not be triggered, but the device will have the device_hijacked_at with 
the last DeviceHijacked event timestamp

* IvanoMatteo\LaravelDeviceTracking\Events\UserSeenFromUnverifiedDevice

When an user is detected on a device not for the first time and the device is not flagged as verified

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
