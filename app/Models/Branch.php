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

    /**
     * حساب المسافة بالكيلومتر بين الفرع ونقطة معينة (معادلة Haversine)
     */
    public function haversineDistance(float $lat, float $lng): float
    {
        if (!$this->lat || !$this->lng) return 0;
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat - $this->lat);
        $dLng = deg2rad($lng - $this->lng);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($this->lat)) * cos(deg2rad($lat))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * التحقق مما إذا كانت الإحداثيات داخل المضلع المرسوم (Ray Casting Algorithm)
     */
    public function isPointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $inside = false;
        $n = count($polygon);
        if ($n < 3) return false;

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $pointI = $polygon[$i];
            $pointJ = $polygon[$j];
            if (!is_array($pointI) || !is_array($pointJ) || count($pointI) < 2 || count($pointJ) < 2) {
                continue;
            }

            $xi = (float) $pointI[0];
            $yi = (float) $pointI[1];
            $xj = (float) $pointJ[0];
            $yj = (float) $pointJ[1];

            $intersect = (($yi > $lng) != ($yj > $lng))
                && ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi + 0.000000001) + $xi);
            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * هل الإحداثيات داخل نطاق خدمة الفرع؟ (يدعم رسم النقاط والدائرة)
     */
    public function coversLocation(float $lat, float $lng): bool
    {
        // إذا كان نوع التغطية مضلع بالنقاط ويحتوي على 3 نقاط على الأقل
        if ($this->coverage_type === 'polygon' && is_array($this->coverage_polygon) && count($this->coverage_polygon) >= 3) {
            return $this->isPointInPolygon($lat, $lng, $this->coverage_polygon);
        }

        // افتراضياً أو إذا كان دائرة
        if (!$this->lat || !$this->lng) return false;
        return $this->haversineDistance($lat, $lng) <= $this->radius_km;
    }

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

