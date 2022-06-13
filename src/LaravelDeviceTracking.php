<?php

namespace IvanoMatteo\LaravelDeviceTracking;

use hisorange\BrowserDetect\Contracts\ResultInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use IvanoMatteo\LaravelDeviceTracking\Events\DeviceCreated;
use IvanoMatteo\LaravelDeviceTracking\Events\DeviceHijacked;
use IvanoMatteo\LaravelDeviceTracking\Events\DeviceUpdated;
use IvanoMatteo\LaravelDeviceTracking\Events\UserSeenFromNewDevice;
use IvanoMatteo\LaravelDeviceTracking\Events\UserSeenFromUnverifiedDevice;
use IvanoMatteo\LaravelDeviceTracking\Models\Device;
use IvanoMatteo\LaravelDeviceTracking\Models\DeviceUser;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
            $browser = App::make('browser-detect')->detect();
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
                'is_bot' => $isBot,
                'version' => $browser->browserVersion(),
                'engine' => $browser->browserEngine(),
                'platform_family' => $browser->platformFamily(),
                'platform_name' => $browser->platformName(),
                'platform_version' => $browser->platformVersion(),
                'device_model' => $browser->deviceModel(),
                'ip_addresses' => Request::ips(),
                'user_agent' => Str::limit(Request::header('user-agent'), 512),
            ];

            $device_uuid = $this->getCookieID();

            $this->detectData = compact('device_type', 'data', 'device_uuid');
        }

        return $this->detectData;
    }


    public function getRequestHash()
    {
        return md5(Request::ip() . Request::userAgent() . $this->getCookieID());
    }

    /**
     * return true if match
     * return false if not match
     * return null if web guard is not logged in
     *
     * @return bool|null
     */
    public function checkSessionDeviceHash()
    {
        if (Auth::guard('web')->check()) {

            $sessionMd5 = session(config('laravel-device-tracking.session_key'));
            $currentMd5 = $this->getRequestHash();

            if (!$sessionMd5 || $currentMd5 !== $sessionMd5) {
                return false;
            } else {
                return true;
            }
        }

        return null;
    }

    /**
     * id web guard is logged in, this function will store
     * the device hash in the session
     */
    public function setSessionDeviceHash()
    {
        if (Auth::guard('web')->check()) {

            $currentMd5 = $this->getRequestHash();
            session([config('laravel-device-tracking.session_key') => $currentMd5]);
        }
    }

    /**
     * retrieve the device identifier from cookie
     * @return string
     * */
    public function getCookieID()
    {

        return Str::limit(Request::cookie(config('laravel-device-tracking.device_cookie')), 255, '');
    }

    /**
     * set the device identifier cookie
     */
    public function setCookieID($id)
    {
        Cookie::queue(Cookie::forever(
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
    public function findCurrentDevice($orNew = false, $update = false)
    {
        if (isset($this->currentDevice)) {
            return $this->currentDevice;
        }

        $this->currentDevice = Device::where('device_uuid', '=', $this->getCookieID())->first();

        if (!$this->currentDevice && $orNew) {
            $this->currentDevice = $this->newDeviceFromDetection();
        }

        if ($this->currentDevice && $update) {
            $this->detect();
            $this->currentDevice->ip = Request::ip();
            $this->currentDevice->device_type = $this->detectData['device_type'];
            $this->currentDevice->data = array_merge($this->currentDevice->data ?? [], $this->detectData['data']);
        }

        return $this->currentDevice;
    }


    public function flagAsVerified(Device $device, $user_id)
    {
        $device->pivot()
            ->where('user_id', '=', $user_id)
            ->update(['verified_at' => now()]);
    }
    public function flagAsVerifiedByUuid($device_uuid, $user)
    {
        DeviceUser::where('user_id', '=', $user)
            ->whereHas('device', function ($q) use ($device_uuid) {
                $q->where('device_uuid', '=', $device_uuid);
            })
            ->update(['verified_at' => now()]);
    }

    public function flagCurrentAsVerified()
    {
        if (Auth::check()) {
            $this->detectFindAndUpdate()->currentUserStatus
                ->fill(['verified_at' => now()])
                ->save();
        } else {
            throw new HttpException(500, 'an unser must be logged in to verify the current device');
        }
    }


    /**
     * create a new Device instance using detected data
     * @return Device
     */
    public function newDeviceFromDetection()
    {
        $this->detect();

        $device_uuid =  Str::uuid()->toString() . ':' . Str::random(16);
        $data = $this->detectData['data'];
        $device_type = $this->detectData['device_type'];
        $ip = Request::ip();

        return new Device(compact('device_uuid', 'data', 'device_type', 'ip'));
    }


    /**
     * provide an DeviceHijackingDetector the class from config
     * @return DeviceHijackingDetector
     */
    public function getHijackingDetector()
    {
        if (!isset($this->hijackingDetector)) {
            $tmp = App::make(config('laravel-device-tracking.hijacking_detector'));
            if (!is_object($tmp) || !is_subclass_of($tmp, DeviceHijackingDetector::class)) {
                throw new HttpException(500, get_class($tmp) . ' do not implements DeviceHijackingDetector');
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
        if ($reDetectDevice) {
            $this->currentDevice = null;
        }

        /** @var Model */
        $user = Auth::user();

        $isDeviceJustCreated = false;


        if (!isset($this->currentDevice)) {
            $this->findCurrentDevice(true, true);

            if ($hijackMessage = $this->getHijackingDetector()->detect($this->currentDevice, $user)) {
                $this->currentDevice->device_hijacked_at = now();
                DeviceHijacked::dispatch($hijackMessage, $this->currentDevice, $user);
            }

            $isDeviceDirty = $this->currentDevice->isDirty();
            $isDeviceJustCreated = !$this->currentDevice->exists;

            $this->currentDevice->touch();
            
            if ($isDeviceDirty) {
                if ($isDeviceJustCreated) {
                    DeviceCreated::dispatch($this->currentDevice, $user);
                } else {
                    DeviceUpdated::dispatch($this->currentDevice, $user);
                }
            }

            $this->setSessionDeviceHash();
            $this->setCookieID($this->currentDevice->device_uuid);
        }


        $newUserId = optional($user)->getKey();

        if ($newUserId && $this->userId !== $newUserId) {

            $shouldAttack = $user && ($isDeviceJustCreated || !$this->currentDevice->isUsedBy($newUserId));

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
