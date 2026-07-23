<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeviceToken extends Model
{
    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'onesignal_player_id',
        'platform',
        'app_version',
        'device_name',
        'last_used_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'is_active'    => 'boolean',
        ];
    }

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }
}
