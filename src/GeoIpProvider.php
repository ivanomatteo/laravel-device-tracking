<?php
namespace IvanoMatteo\LaravelDeviceTracking;

use Illuminate\Database\Eloquent\Model;
use IvanoMatteo\LaravelDeviceTracking\Models\Device;

interface GeoIpProvider
{
    public function getCountry($ip);
    public function getCountryIsoCode($ip);
    public function getCity($ip);
    public function getState($ip);
    public function getCoord($ip);
}
