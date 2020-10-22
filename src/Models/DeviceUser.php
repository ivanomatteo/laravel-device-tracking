<?php

namespace IvanoMatteo\LaravelDeviceTracking\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * IvanoMatteo\LaravelDeviceTracking\Models\DeviceUser
 *
 * @property int $id
 * @property int $user_id
 * @property int $device_id
 * @property string|null $verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereVerifiedAt($value)
 * @mixin \Eloquent
 */
class DeviceUser extends Pivot
{
    function device(){
        return $this->belongsTo(Device::class);
    }
    function user(){
        return $this->belongsTo(Device::getUserClass());
    }
}
