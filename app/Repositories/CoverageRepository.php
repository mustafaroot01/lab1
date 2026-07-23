<?php
namespace App\Repositories;

use App\Models\CoverageZone;

class CoverageRepository
{
    /**
     * Find the highest priority active zone that covers the given coordinates
     */
    public function findCoveringZone(float $lat, float $lng): ?CoverageZone
    {
        $latRounded = round($lat, 4); // ~11 meters precision
        $lngRounded = round($lng, 4);
        
        $version = \Illuminate\Support\Facades\Cache::get('coverage_zones_version', 1);
        $cacheKey = "coverage_zone_id_{$version}_{$latRounded}_{$lngRounded}";

        $zoneId = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($lat, $lng) {
            $isSqlite = \Illuminate\Support\Facades\DB::getDriverName() === 'sqlite';

            $zoneQuery = CoverageZone::where('status', 'ACTIVE');
            
            if (!$isSqlite) {
                $point = "POINT({$lng} {$lat})"; // MySQL expects Longitude first in POINT
                $zoneQuery->where(function ($query) use ($point, $lat, $lng) {
                    $query->where('coverage_type', 'POLYGON')
                          ->whereRaw("ST_Contains(geometry, ST_GeomFromText(?))", [$point])
                          ->orWhere(function($q) use ($lat, $lng) {
                              $q->where('coverage_type', 'RADIUS')
                                ->whereRaw("ST_Distance_Sphere(POINT(center_lng, center_lat), POINT(?, ?)) <= radius_meters", [$lng, $lat]);
                          });
                });
            }

            $zones = $zoneQuery->where(function ($query) {
                    $query->whereNull('effective_from')->orWhere('effective_from', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('effective_to')->orWhere('effective_to', '>=', now());
                })
                ->where(function ($query) {
                    $time = now()->format('H:i:s');
                    $query->whereNull('starts_at')->orWhere('starts_at', '<=', $time);
                })
                ->where(function ($query) {
                    $time = now()->format('H:i:s');
                    $query->whereNull('ends_at')->orWhere('ends_at', '>=', $time);
                })
                ->orderByDesc('priority')
                ->get();

            if ($isSqlite) {
                // PHP fallback for SQLite
                foreach ($zones as $z) {
                    if ($z->coverage_type === 'POLYGON') {
                        $geojson = is_array($z->geometry) ? $z->geometry : json_decode($z->geometry, true);
                        if ($geojson && isset($geojson['coordinates'][0])) {
                            $polygon = $geojson['coordinates'][0];
                            if (\App\Helpers\SpatialHelper::pointInPolygon([$lng, $lat], $polygon)) {
                                return $z->id;
                            }
                        }
                    } elseif ($z->coverage_type === 'RADIUS') {
                        $dist = \App\Helpers\SpatialHelper::haversineDistance($lat, $lng, current(explode(',', $z->geometry))[0] ?? 0, current(explode(',', $z->geometry))[1] ?? 0); // Need proper radius extraction
                        // Wait, radius uses center_lat and center_lng
                        if (\App\Helpers\SpatialHelper::haversineDistance($lat, $lng, (float)$z->center_lat, (float)$z->center_lng) <= (float)$z->radius_meters) {
                            return $z->id;
                        }
                    }
                }
                return null;
            }

            return $zones->first()?->id;
        });

        if (!$zoneId) {
            return null;
        }

        // Cache the model itself
        return \Illuminate\Support\Facades\Cache::remember("zone_model_{$version}_{$zoneId}", 3600, function() use ($zoneId) {
            return CoverageZone::find($zoneId);
        });
    }
}
