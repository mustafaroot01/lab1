<?php
namespace App\Services\Coverage;

use App\Models\CoverageZone;

class ZonePricingService
{
    public function calculate(CoverageZone $zone): float
    {
        if ($zone->pricing_type === 'FIXED') {
            return (float) $zone->service_fee;
        }

        // For RULE_BASED, logic can be added here (e.g., peak hours, rush fees)
        return (float) $zone->service_fee;
    }
}
