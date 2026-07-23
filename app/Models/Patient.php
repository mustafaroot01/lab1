<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Traits\HasPushNotifications;

class Patient extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasPushNotifications;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'birth_date',
        'gender',
        'address',
        'is_profile_completed',
        'is_active',
        'agreed_to_terms',
        'otp_code',
        'otp_expires_at',
        'notes',
        'registration_status',
    ];

    protected $hidden = [
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'birth_date'           => 'date',
            'is_profile_completed' => 'boolean',
            'is_active'            => 'boolean',
            'agreed_to_terms'      => 'boolean',
            'otp_expires_at'       => 'datetime',
        ];
    }

    public function chronicDiseases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PatientChronicDisease::class, 'patient_id')->latest();
    }

    public function medications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PatientMedication::class, 'patient_id')->latest();
    }

    public function allergies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PatientAllergy::class, 'patient_id')->latest();
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class, 'patient_id')->latest();
    }

    public function conversations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Chat\Conversation::class, 'patient_id');
    }

    public function isAdmin(): bool
    {
        return false;
    }
}
