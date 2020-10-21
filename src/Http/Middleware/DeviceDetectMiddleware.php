<?php

namespace IvanoMatteo\LaravelDeviceTracking\Http\Middleware;

use Closure;

class DeviceDetectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (\Auth::guard('web')->check()) {

            /** @var LaravelDeviceTracking */
            $ldt = resolve('laravel-device-tracking');

            $sessionMd5 = session('laravel-device-tracking');
            $currentMd5 = md5($request->userAgent() . $ldt->getCookieID());

            if (!$sessionMd5 || $currentMd5 !== $sessionMd5) {
                $ldt->detectFindAndUpdate();
                session(['laravel-device-tracking' => $currentMd5]);
            }
            
        }

        return $next($request);
    }
}
