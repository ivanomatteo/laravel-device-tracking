# A library that allow to track different devices used

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ivanomatteo/laravel-device-tracking.svg?style=flat-square)](https://packagist.org/packages/ivanomatteo/laravel-device-tracking)
[![Build Status](https://img.shields.io/travis/ivanomatteo/laravel-device-tracking/master.svg?style=flat-square)](https://travis-ci.org/ivanomatteo/laravel-device-tracking)
[![Quality Score](https://img.shields.io/scrutinizer/g/ivanomatteo/laravel-device-tracking.svg?style=flat-square)](https://scrutinizer-ci.com/g/ivanomatteo/laravel-device-tracking)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanomatteo/laravel-device-tracking.svg?style=flat-square)](https://packagist.org/packages/ivanomatteo/laravel-device-tracking)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

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

//call on login or when you want update anche check the device inforamtion
\DeviceTracker::findDetectAndUpdate();


/*
Following events could be emitted:


IvanoMatteo\LaravelDeviceTracking\Events\DeviceCreated
    When a new device is detected and stored

IvanoMatteo\LaravelDeviceTracking\Events\DeviceUpdated
    When some information of a device is changed

IvanoMatteo\LaravelDeviceTracking\Events\DeviceHijacked
    When critical device information are change basing on the logic of
    the configured IvanoMatteo\LaravelDeviceTracking\DeviceHijackingDetector

IvanoMatteo\LaravelDeviceTracking\Events\UserSeenFromNewDevice
    When an user is detected on a device for the first time 

IvanoMatteo\LaravelDeviceTracking\Events\UserSeenFromUnverifiedDevice
    When an user is detected on a device not for the first time and the device is not flagged as verified

*/

```


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

