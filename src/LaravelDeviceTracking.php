<?php

namespace IvanoMatteo\LaravelDeviceTracking;

use hisorange\BrowserDetect\Contracts\ResultInterface;
use Illuminate\Database\Eloquent\Model;
use IvanoMatteo\LaravelDeviceTracking\Events\DeviceCreated;
use IvanoMatteo\LaravelDeviceTracking\Events\DeviceHijacked;
use IvanoMatteo\LaravelDeviceTracking\Events\DeviceUpdated;
use IvanoMatteo\LaravelDeviceTracking\Events\UserSeenFromNewDevice;
use IvanoMatteo\LaravelDeviceTracking\Events\UserSeenFromUnverifiedDevice;
use IvanoMatteo\LaravelDeviceTracking\Models\Device;

class LaravelDeviceTracking
{
    private $userId;
    private $detectData;
    private $currentDevice;
    private $hijackingDetector;


    /** 
     * retrieve device informations from the user-agent string
     * @return array 
     * */
    public function detect()
    {
        if (!isset($this->detectData)) {

            /** @var ResultInterface */
            $browser = \App::make('browser-detect')->detect();
            $isBot = $browser->isBot();
            $family = $browser->browserFamily();
            $platform = $browser->platformFamily();
            $deviceModel = $browser->deviceModel();

            $features = [];

            if ($isBot) {
                $features[] = 'BOT';
            }
            if ($deviceModel) {
                $features[] = $deviceModel;
            }
            if ($platform) {
                $features[] = $platform;
            }
            if ($family) {
                $features[] = $family;
            }

            // device type is a generated identifier 
            // that normally should not change
            $device_type = implode("|", $features);

            // other metadata
            $data = [
                'version' => $browser->browserVersion(),
                'engine' => $browser->browserEngine(),
                'bot' => $isBot,
                'ips' => request()->ips(),
                'user_agent' => \Str::limit(request()->header('user-agent'), 512),
            ];

            $device_uuid = \Str::limit(request()->cookie(config('laravel-device-tracking.device_cookie')), 255, '');

            $this->detectData = compact('device_type', 'data', 'device_uuid');
        }

        return $this->detectData;
    }


    /** 
     * create a new Device instance using detected data
     * @return Device 
     */
    public function newDeviceFromDetection()
    {
        $this->detect();

        $device_uuid =  \Str::uuid()->toString() . ':' . \Str::random(16);
        $data = $this->detectData['data'];
        $device_type = $this->detectData['device_type'];
        $ip = request()->ip();

        return new Device(compact('device_uuid', 'data', 'device_type', 'ip'));
    }


    /**
     * provide an DeviceHijackingDetector the class from config
     * @return DeviceHijackingDetector 
     */
    public function getHijackingDetector()
    {
        if (!isset($this->hijackingDetector)) {
            $tmp = resolve(config('laravel-device-tracking.hijacking_detector'));
            if (!is_subclass_of($tmp, DeviceHijackingDetector::class)) {
                abort(500, get_class($tmp) . 'do not implements DeviceHijackingDetector');
            }
            $this->hijackingDetector = $tmp;
        }
        return $this->hijackingDetector;
    }

    /**
     * create and store an instance id Device from browser metadata,
     * if an user is logged in, then bind the user with the device,
     * trigger the events,
     * store the cookie with the device identifier
     * 
     * @param bool $reDetectDevice force device detection to run again
     * 
     * @return Device the detected device
     */
    public function findDetectAndUpdate(bool $reDetectDevice = false)
    {
        if($reDetectDevice){
            $this->currentDevice = null;
        }

        /** @var Model */
        $user = \Auth::user();

        $is_device_just_created = false;
     
        
        if (!isset($this->currentDevice)) {

            $this->detect();

            /** @var Device */
            $device = Device::where('device_uuid', '=', $this->detectData['device_uuid'])->first();
            if (!$device) {
                $device = $this->newDeviceFromDetection();
            }

            $device->ip = request()->ip();
            $device->device_type = $this->detectData['device_type'];
            $device->data = array_merge($device->data ?? [], $this->detectData['data']);


            if ($hijack_message = $this->getHijackingDetector()->detect($device, $user)) {
                $device->device_hijacked_at = now();
                DeviceHijacked::dispatch($hijack_message, $device, $user);
            }

            $is_device_dirty = $device->isDirty();
            $is_device_just_created = !$device->exists;
            
            $device->touch();
            $device->save();

            if ($is_device_dirty) {
                if($is_device_just_created){
                    DeviceCreated::dispatch($device, $user);
                }else{
                    DeviceUpdated::dispatch($device, $user);
                }
            }

            \Cookie::queue(\Cookie::forever(
                config('laravel-device-tracking.device_cookie'),
                $device->device_uuid,
                null,
                null,
                null,
                true, // http only
                false,
                null // same site
            ));

            $this->currentDevice = $device;
        }


        $newUserId = optional($user)->getKey();

        if ($newUserId && $this->userId !== $newUserId) {

            $should_attach = $user && ($is_device_just_created || $this->currentDevice->whereHas('user', function ($q) use ($newUserId) {
                $q->where('device_user.user_id', '=', $newUserId);
            })->count() === 0);

            if ($should_attach) {
                $this->currentDevice->user()->attach($user);
                UserSeenFromNewDevice::dispatch($this->currentDevice, $user);
            } else {
                if (!optional($this->currentDevice->currentUserStatus)->verified_at) {
                    UserSeenFromUnverifiedDevice::dispatch($this->currentDevice, $user);
                }
            }
        }

        $this->userId = $newUserId;


        return $this->currentDevice;
    }
}
