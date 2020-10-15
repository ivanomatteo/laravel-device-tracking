<?php

namespace IvanoMatteo\LaravelDeviceTracking;

use IvanoMatteo\LaravelDeviceTracking\Events\DeviceHijacked;
use IvanoMatteo\LaravelDeviceTracking\Events\UserSeenFromNewDevice;
use IvanoMatteo\LaravelDeviceTracking\Events\UserSeenFromUnverifiedDevice;
use IvanoMatteo\LaravelDeviceTracking\Models\Device;

class LaravelDeviceTracking
{
    private $detectData;
    private $currentDevice;
    private $hijackingDetector;

    /** @return Device */
    public function detect()
    {
        if (!isset($this->detectData)) {

            $browser = \Browser::detect();
            $isBot = $browser->isBot();
            $family = $browser->browserFamily();
            $platform = $browser->platformName();
            $platformVersion = $browser->platformVersion();
            $deviceModel = $browser->deviceModel();

            $arr = [];

            if ($isBot) {
                $arr[] = 'BOT';
            }
            if ($platformVersion) {
                $arr[] = $platformVersion;
            }
            if ($deviceModel) {
                $arr[] = $deviceModel;
            }
            if ($platform) {
                $arr[] = $platform;
            }
            if ($family) {
                $arr[] = $family;
            }

            $device_type = implode("|", $arr);


            $data = [
                'version' => $browser->browserVersion(),
                'engine' => $browser->browserEngine(),
                'bot' => $isBot,
                'ips' => request()->ips(),
                'user_agent' => \Str::limit(request()->header('user-agent'), 512, ''),
            ];

            $device_uuid = \Str::limit(request()->cookie(config('laravel-device-tracking.device_cookie')), 255, '');

            $this->detectData = compact('device_type', 'data', 'device_uuid');
        }
        return $this->detectData;
    }

    /** @return Device */
    public function newDevice()
    {
        $this->detect();

        $device_uuid =  \Str::uuid()->toString();
        $data = $this->detectData['data'];
        $device_type = $this->detectData['device_type'];
        $ip = request()->ip();

        return new Device(compact('device_uuid', 'data', 'device_type','ip'));
    }



    public function getHijackingDetector()
    {
        if (!isset($this->hijackingDetector)) {
            $this->hijackingDetector = resolve(config('laravel-device-tracking.hijacking_detector'));
        }
        return $this->hijackingDetector;
    }


    public function findDetectAndUpdate()
    {
        if (!isset($this->currentDevice)) {

            $this->detect();

            $device = Device::where('device_uuid', '=', $this->detectData['device_uuid'])->first();
            if (!$device) {
                $device = $this->newDevice();
            }

            $user = request()->user();
            
            $device->ip = request()->ip();
            $device->device_type = $this->detectData['device_type'];
            $device->data = array_merge($device->data ?? [], $this->detectData['data']);

            if ($hijacked = $this->getHijackingDetector()->detect($device, $user)) {
                $device->device_hijacked_at = now();

                DeviceHijacked::dispatch($hijacked, $device, $user);
            }

            $should_attach = $user && (!$device->exists ||  $device->whereHas('user', function ($q) use ($user) {
                $q->where('device_user.user_id', '=', $user->id);
            })->count() === 0);

            $device->touch();
            $device->save();

            if ($should_attach) {
                $device->user()->attach($user);
                UserSeenFromNewDevice::dispatch($device, $user);
            } else {
                if ($device->currentUserStatus && !$device->currentUserStatus->verified_at) {
                    UserSeenFromUnverifiedDevice::dispatch($device, $user);
                }
            }


            \Cookie::queue(\Cookie::forever(config('laravel-device-tracking.device_cookie'), $device->device_uuid));

            $this->currentDevice = $device;
        }

        return $this->currentDevice;
    }
}
