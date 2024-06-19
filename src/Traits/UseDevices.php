<?php

namespace IvanoMatteo\LaravelDeviceTracking\Traits;

use IvanoMatteo\LaravelDeviceTracking\Models\Device;

trait UseDevices
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function device()
    {
        return $this->belongsToMany(Device::class, 'device_user')
            ->withPivot(['verified_at', 'name', 'reported_as_rogue_at', 'note', 'admin_note', 'data'])
            ->withTimestamps();
    }

    /** 
     * @return bool
     */
    public function deviceShouldBeDetected()
    {
        return true;
    }
}
