<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'birth_date',
        'gender',
        'is_profile_completed',
        'is_active',
        'agreed_to_terms',
        'otp_code',
        'otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'birth_date'           => 'date',
            'is_profile_completed' => 'boolean',
            'is_active'            => 'boolean',
            'agreed_to_terms'      => 'boolean',
            'otp_expires_at'       => 'datetime',
        ];
    }


    public function chronicDiseases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PatientChronicDisease::class)->latest();
    }

    public function medications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PatientMedication::class)->latest();
    }

    public function allergies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PatientAllergy::class)->latest();
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function conversations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Chat\Conversation::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
