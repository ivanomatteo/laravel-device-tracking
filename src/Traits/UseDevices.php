<?php

namespace IvanoMatteo\LaravelDeviceTracking\Traits;

use IvanoMatteo\LaravelDeviceTracking\Models\Device;

trait UseDevices
{
    public function device()
    {
        return $this->belongsToMany(Device::class, 'device_user')->withPivot('verified_at')->withTimestamps();
    }
}
