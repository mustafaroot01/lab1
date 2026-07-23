<?php

namespace App\Services\Coverage\Strategies;

use Illuminate\Database\Eloquent\Collection;

class BoundingBoxStrategy implements CoverageStrategyInterface
{
    public function process(Collection $zones, float $lat, float $lng): Collection
    {
        return $zones->filter(function ($zone) use ($lat, $lng) {
            // If min/max are not set or not calculated (null or all zeros), we skip the bbox check
            if ($zone->min_lat === null || $zone->max_lat === null || $zone->min_lng === null || $zone->max_lng === null
                || ($zone->min_lat == 0 && $zone->max_lat == 0 && $zone->min_lng == 0 && $zone->max_lng == 0)) {
                return true;
            }

            // A point is inside the bounding box if it lies between the min and max coordinates.
            // NOTE: Longitude wrap-around at 180/-180 is not fully handled here for simplicity,
            // but for a local region (e.g. Iraq), this simple check is perfectly sufficient.
            return $lat >= $zone->min_lat && $lat <= $zone->max_lat &&
                   $lng >= $zone->min_lng && $lng <= $zone->max_lng;
        });
    }
}
