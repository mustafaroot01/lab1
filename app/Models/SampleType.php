<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'code',
        'icon',
        'color',
        'description',
        'sort_order',
    ];

    public function tests()
    {
        return $this->hasMany(MedicalTest::class, 'sample_type_id');
    }

    public function getTestsCountAttribute()
    {
        $count = $this->tests()->count();
        return $count > 0 ? $count : MedicalTest::where('sample_type', $this->name_ar)->count();
    }
}
