<?php

namespace IvanoMatteo\LaravelDeviceTracking\Models;


use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * IvanoMatteo\LaravelDeviceTracking\Models\Device
 *
 * @property int $id
 * @property string $device_uuid
 * @property string $device_type
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \IvanoMatteo\LaravelDeviceTracking\Models\DeviceUser|null $currentUserStatus
 * @property-read \Illuminate\Database\Eloquent\Collection|\IvanoMatteo\LaravelDeviceTracking\Models\DeviceUser[] $pivot
 * @property-read int|null $pivot_count
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $user
 * @property-read int|null $user_count
 * @method static \Illuminate\Database\Eloquent\Builder|Device newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Device newQuery()
 * @method static \Illuminate\Database\Query\Builder|Device onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Device query()
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereDeviceUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Device withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Device withoutTrashed()
 * @mixin \Eloquent
 */
class Device extends Model
{
    use SoftDeletes;


    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
    ];


    function touch()
    {
        $this->{static::UPDATED_AT} = now();
    }

    function user()
    {
        return $this->belongsToMany(config('laravel-device-tracking.user_model'), 'device_user')
            ->using(DeviceUser::class)
            ->withPivot('verified_at')->withTimestamps();
    }
    function pivot()
    {
        return $this->hasMany(DeviceUser::class);
    }
    function currentUserStatus()
    {
        return $this->hasOne(DeviceUser::class)
            ->where('user_id', '=', optional(request()->user())->id);
    }
}
