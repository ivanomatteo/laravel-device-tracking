<?php
namespace IvanoMatteo\LaravelDeviceTracking;

use Illuminate\Database\Eloquent\Model;
use IvanoMatteo\LaravelDeviceTracking\Models\Device;

interface DeviceHijackingDetector
{
    /**
     * when this function is invoked, the method save() is not called yet
     * so is possible to use isDirty() method
     *
     * @return string|null a message if an hijacking is detected, null otherwise
     */
    public function detect(Device $device, ?Model $user);
}
