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

            $device_uuid = $this->getCookieID();

            $this->detectData = compact('device_type', 'data', 'device_uuid');
        }

        return $this->detectData;
    }


    function getCookieID(){
        return \Str::limit(request()->cookie(config('laravel-device-tracking.device_cookie')), 255, '');
    }
    function setCookieID($id){
        \Cookie::queue(\Cookie::forever(
            config('laravel-device-tracking.device_cookie'),
            $id,
            null,
            null,
            null,
            true, // http only
            false,
            null // same site
        ));
    }

    /**
     * @return Device|null
     */
    function findCurrentDevice($orNew = false){
         /** @var Device */
         $device = Device::where('device_uuid', '=', $this->getCookieID())->first();

         if (!$device && $orNew) {
            $device = $this->newDeviceFromDetection();
        }

        return $device;
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
    public function detectFindAndUpdate(bool $reDetectDevice = false)
    {
        if($reDetectDevice){
            $this->currentDevice = null;
        }

        /** @var Model */
        $user = \Auth::user();

        $isDeviceJustCreated = false;
     
        
        if (!isset($this->currentDevice)) {

            $this->detect();

            $device = $this->findCurrentDevice(true);

            $device->ip = request()->ip();
            $device->device_type = $this->detectData['device_type'];
            $device->data = array_merge($device->data ?? [], $this->detectData['data']);


            if ($hijackMessage = $this->getHijackingDetector()->detect($device, $user)) {
                $device->device_hijacked_at = now();
                DeviceHijacked::dispatch($hijackMessage, $device, $user);
            }

            $isDeviceDirty = $device->isDirty();
            $isDeviceJustCreated = !$device->exists;
            
            $device->touch();
            $device->save();

            if ($isDeviceDirty) {
                if($isDeviceJustCreated){
                    DeviceCreated::dispatch($device, $user);
                }else{
                    DeviceUpdated::dispatch($device, $user);
                }
            }

            $this->setCookieID($device->device_uuid);

            $this->currentDevice = $device;
        }


        $newUserId = optional($user)->getKey();

        if ($newUserId && $this->userId !== $newUserId) {

            $shouldAttack = $user && ($isDeviceJustCreated || $this->currentDevice->whereHas('user', function ($q) use ($newUserId) {
                $q->where('device_user.user_id', '=', $newUserId);
            })->count() === 0);

            if ($shouldAttack) {
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
