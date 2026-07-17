<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'original_price',
        'discount_price',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'original_price' => 'float',
        'discount_price' => 'float',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tests()
    {
        return $this->belongsToMany(MedicalTest::class, 'package_offer_tests', 'package_offer_id', 'medical_test_id')
                    ->withTimestamps();
    }
}
