<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends Model
{
    protected $fillable = [
        'name',
        'governorate',
        'branch_id',
        'service_fee',
        'free_threshold',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'branch_id'      => 'integer',
        'service_fee'    => 'float',
        'free_threshold' => 'float',
        'sort_order'     => 'integer',
        'is_active'      => 'boolean',
    ];

    public function getNameArAttribute(): ?string
    {
        return $this->name;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class)->orderBy('sort_order');
    }
}
