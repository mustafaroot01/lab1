<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientChronicDisease extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'user_id',
        'disease_name',
        'severity',
        'diagnosis_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'diagnosis_date' => 'date',
        ];
    }

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

}
