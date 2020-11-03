<?php

namespace IvanoMatteo\LaravelDeviceTracking\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use IvanoMatteo\LaravelDeviceTracking\Models\Device;

class DeviceHijacked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $device;
    public $user;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $message, Device $device, ?Model $user)
    {
        $this->message = $message;
        $this->device = $device;
        $this->user = $user;
    }
}
