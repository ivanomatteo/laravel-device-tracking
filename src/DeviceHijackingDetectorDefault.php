<?php

namespace IvanoMatteo\LaravelDeviceTracking;

use Illuminate\Contracts\Auth\Authenticatable;
use IvanoMatteo\LaravelDeviceTracking\Events\DeviceHijacked;
use IvanoMatteo\LaravelDeviceTracking\Models\Device;

class DeviceHijackingDetectorDefault implements DeviceHijackingDetector
{

    function detect(Device $device, Authenticatable $user = null)
    {
        if($device->exists){ //exists in db
            if ($device->isDirty('device_type')) {
                return 'device_type missmatch';
            }
    
            /* 
            if (\Str::startsWith($device->ip, '10.')) {
                if ($device->isDirty('ip')) {
                    return 'intranet device changed ip';
                }
            }
    
            if (\Str::startsWith($device->ip, '10.') && !\Str::startsWith($device->ip, '10.')) {
                return 'intranet device changed network';
            }  
            */
    
            /*
                //extract json field to avoid multiple automatic decoding
                $newdata = $device->data;
                $olddata = $device->getOriginal('data');
                
                //... 
            */
        }
        
    
        return null;
    }
}
