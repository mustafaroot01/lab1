<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name_ar',
        'address',
        'phone',
        'lat',
        'lng',
        'radius_km',
        'service_fee',
        'urgent_fee',
        'free_threshold',
        'fee_notes',
        'coverage_type',
        'coverage_polygon',
        'is_active',
        'opens_at',
        'closes_at',
        'working_hours',
        'notes',
    ];

    protected $casts = [
        'lat'              => 'float',
        'lng'              => 'float',
        'radius_km'        => 'float',
        'service_fee'      => 'float',
        'urgent_fee'       => 'float',
        'free_threshold'   => 'float',
        'is_active'        => 'boolean',
        'coverage_polygon' => 'array',
        'working_hours'    => 'array',
    ];



    public function districts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(District::class);
    }

    public function services(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BranchService::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }
}

