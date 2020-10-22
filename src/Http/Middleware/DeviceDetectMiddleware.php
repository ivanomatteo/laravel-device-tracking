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

            if ($ldt->checkSessionDeviceHash() === false) {
                $ldt->detectFindAndUpdate();
            }
            
        }

        return $next($request);
    }
}
