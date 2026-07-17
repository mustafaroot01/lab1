<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Technician extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $fillable = [
        'name',
        'phone',
        'password',
        'address',
        'specialty',
        'has_transport',
        'has_equipment',
        'id_front_image',
        'id_back_image',
        'district_id_image',
        'notes',
        'status',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password'      => 'hashed',
            'has_transport' => 'boolean',
            'has_equipment' => 'boolean',
        ];
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin(): bool
    {
        return false;
    }
}
