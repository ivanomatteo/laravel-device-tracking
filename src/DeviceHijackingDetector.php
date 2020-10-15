<?php
namespace IvanoMatteo\LaravelDeviceTracking;
use Illuminate\Contracts\Auth\Authenticatable;
use IvanoMatteo\LaravelDeviceTracking\Models\Device;

interface DeviceHijackingDetector
{
    /**
     * @return string|null a message if an hijacking is detected, null otherwise
     */
    function detect(Device $device,Authenticatable $user = null);
}
