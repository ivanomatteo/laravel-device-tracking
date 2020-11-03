<?php

namespace IvanoMatteo\LaravelDeviceTracking\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class DeviceTrackerMiddleware
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
        if (Auth::guard('web')->check()) {

            /** @var LaravelDeviceTracking */
            $ldt = App::make('laravel-device-tracking');

            if ($ldt->checkSessionDeviceHash() === false) {
                $ldt->detectFindAndUpdate();
            }
        }

        return $next($request);
    }
}
