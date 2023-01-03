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
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $reported_as_rogue_at
 * @property string|null $note
 * @property string|null $admin_note
 * @property-read \IvanoMatteo\LaravelDeviceTracking\Models\Device $device
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereAdminNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereReportedAsRogueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeviceUser whereVerifiedAt($value)
 * 
 * @mixin \Eloquent
 */
class DeviceUser extends Pivot
{

    protected $casts = [
        'verified_at' => 'datetime',
        'reported_as_rogue_at' => 'datetime',
    ];
    protected $guarded = [];
    protected $hidden = [
        'note','admin_note'
    ];


    public function device()
    {
        return $this->belongsTo(Device::class);
    }
    public function user()
    {
        return $this->belongsTo(Device::getUserClass());
    }
}
