<?php

namespace App\Services\Coverage\Strategies;

use Illuminate\Database\Eloquent\Collection;
use App\Services\Coverage\GeometryService;

class GraceDistanceStrategy implements CoverageStrategyInterface
{
    private GeometryService $geometryService;

    public function __construct(GeometryService $geometryService)
    {
        $this->geometryService = $geometryService;
    }

    public function process(Collection $zones, float $lat, float $lng): Collection
    {
        // This strategy operates on ALL zones initially fetched, not just the ones filtered by PolygonStrategy.
        // Therefore, we evaluate grace distance if a zone didn't match via polygon.
        
        return $zones->filter(function ($zone) use ($lat, $lng) {
            // If it already matched via polygon, we keep it.
            if (isset($zone->_match_type) && $zone->_match_type === 'polygon_exact') {
                return true;
            }

            // Otherwise, check Grace Distance.
            $polygon = [];
            if ($zone->coverage_type === 'POLYGON' && !empty($zone->geometry)) {
                $geoJson = is_string($zone->geometry) ? json_decode($zone->geometry, true) : $zone->geometry;
                if (isset($geoJson['coordinates'])) {
                    $polygon = $geoJson['coordinates'];
                }
            } elseif ($zone->coverage_type === 'RADIUS') {
                $dist = $this->geometryService->haversineDistance($lat, $lng, $zone->center_lat, $zone->center_lng);
                $allowedGrace = $zone->grace_distance ?? config('coverage.default_grace_distance', 50);
                $distanceFromEdge = $dist - $zone->radius_meters;
                
                if ($distanceFromEdge > 0 && $distanceFromEdge <= $allowedGrace) {
                    $zone->_match_type = 'grace_distance';
                    $zone->_distance_from_border = $distanceFromEdge;
                    return true;
                }
                return false;
            }

            $distance = $this->geometryService->distanceToPolygonEdge($lat, $lng, $polygon);

            $allowedGrace = $zone->grace_distance ?? config('coverage.default_grace_distance', 50);

            if ($distance <= $allowedGrace) {
                $zone->_match_type = 'grace_distance';
                $zone->_distance_from_border = $distance;
                return true;
            }

            return false;
        });
    }
}
