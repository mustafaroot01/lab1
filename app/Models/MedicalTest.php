<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_group_id',
        'sample_type_id',
        'tube_type_id',
        'name_ar',
        'name_en',
        'key',
        'sample_type',
        'tube_type',
        'fasting_required',
        'result_time',
        'price',
        'platform_price',
        'total_price',
        'is_active',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'fasting_required' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'float',
        'platform_price' => 'float',
        'total_price' => 'float',
    ];

    public function group()
    {
        return $this->belongsTo(TestGroup::class, 'test_group_id');
    }

    public function sampleTypeObj()
    {
        return $this->belongsTo(SampleType::class, 'sample_type_id');
    }

    public function tubeTypeObj()
    {
        return $this->belongsTo(TubeType::class, 'tube_type_id');
    }

    public function packageOffers()
    {
        return $this->belongsToMany(PackageOffer::class, 'package_offer_tests', 'medical_test_id', 'package_offer_id')
                    ->withTimestamps();
    }
}
