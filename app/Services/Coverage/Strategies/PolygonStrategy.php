<?php

namespace App\Services\Coverage\Strategies;

use Illuminate\Database\Eloquent\Collection;
use App\Services\Coverage\GeometryService;

class PolygonStrategy implements CoverageStrategyInterface
{
    public function __construct(private GeometryService $geometryService) {}

    public function process(Collection $zones, float $lat, float $lng): Collection
    {
        return $zones->filter(function ($zone) use ($lat, $lng) {
            $polygon = [];
            if ($zone->coverage_type === 'POLYGON' && !empty($zone->geometry)) {
                $geoJson = is_string($zone->geometry) ? json_decode($zone->geometry, true) : $zone->geometry;
                if (isset($geoJson['coordinates'])) {
                    $polygon = $geoJson['coordinates'];
                }
            } elseif ($zone->coverage_type === 'RADIUS') {
                // If it's a radius, we check haversine distance
                $dist = $this->geometryService->haversineDistance($lat, $lng, $zone->center_lat, $zone->center_lng);
                if ($dist <= $zone->radius_meters) {
                    $zone->_match_type = 'polygon_exact';
                    $zone->_distance_from_border = 0.0;
                    return true;
                }
                return false;
            }

            // Keep zones that match exactly inside the polygon.
            $isInside = $this->geometryService->isPointInPolygon($lat, $lng, $polygon);
            
            if ($isInside) {
                $zone->_match_type = 'polygon_exact';
                $zone->_distance_from_border = 0.0;
                return true;
            }

            return false;
        });
    }
}
