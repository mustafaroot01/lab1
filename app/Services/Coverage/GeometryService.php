<?php

namespace App\Services\Coverage;

class GeometryService
{
    const EARTH_RADIUS_METERS = 6371000;

    /**
     * Calculate the shortest distance from a point to a polygon edge in meters.
     * Uses the Haversine formula to find distances to line segments.
     */
    public function distanceToPolygonEdge(float $lat, float $lng, array $polygon): float
    {
        if (empty($polygon)) {
            return PHP_FLOAT_MAX;
        }

        $minDistance = PHP_FLOAT_MAX;

        // Extract outer ring: GeoJSON coordinates = [ [[lng,lat],...] ]
        $ring = isset($polygon[0][0]) && is_array($polygon[0][0]) ? $polygon[0] : $polygon;
        $count = count($ring);

        if ($count < 3) {
            return PHP_FLOAT_MAX;
        }

        for ($i = 0; $i < $count - 1; $i++) {
            $p1 = $ring[$i];
            $p2 = $ring[$i + 1];

            // GeoJSON format: [longitude, latitude] — so [0]=lng, [1]=lat
            $dist = $this->distanceToSegment(
                $lat, $lng,
                (float)$p1[1], (float)$p1[0],  // p1: lat=index1, lng=index0
                (float)$p2[1], (float)$p2[0]   // p2: lat=index1, lng=index0
            );

            if ($dist < $minDistance) {
                $minDistance = $dist;
            }
        }

        return $minDistance;
    }

    /**
     * Signature: distanceToSegment(point_lat, point_lng, seg_a_lat, seg_a_lng, seg_b_lat, seg_b_lng)
     * Returns distance in meters from point P to line segment A→B.
     * Uses equirectangular projection for speed (accurate for small regions like Iraq).
     */
    private function distanceToSegment(float $pLat, float $pLng, float $aLat, float $aLng, float $bLat, float $bLng): float
    {
        $dx = $bLng - $aLng;
        $dy = $bLat - $aLat;

        if ($dx == 0 && $dy == 0) {
            // Segment is a point
            return $this->haversineDistance($pLat, $pLng, $aLat, $aLng);
        }

        $t = (($pLng - $aLng) * $dx + ($pLat - $aLat) * $dy) / ($dx * $dx + $dy * $dy);

        if ($t < 0) {
            return $this->haversineDistance($pLat, $pLng, $aLat, $aLng);
        } elseif ($t > 1) {
            return $this->haversineDistance($pLat, $pLng, $bLat, $bLng);
        }

        $closestLng = $aLng + $t * $dx;
        $closestLat = $aLat + $t * $dy;

        return $this->haversineDistance($pLat, $pLng, $closestLat, $closestLng);
    }

    /**
     * Calculate distance between two points in meters using Haversine.
     */
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;

        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Check if a point is inside a polygon using ray casting algorithm.
     */
    public function isPointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        if (empty($polygon)) {
            return false;
        }

        // GeoJSON Polygon coordinates: [ ring0, ring1, ... ] where ring0 = outer ring
        // Each ring is: [ [lng, lat], [lng, lat], ... ]
        // After extracting coordinates from GeoJSON: $polygon = geoJson['coordinates'] = [[ [lng,lat], ...] ]
        // So we need to get the outer ring (polygon[0]) if it's nested.
        $ring = isset($polygon[0][0]) && is_array($polygon[0][0]) ? $polygon[0] : $polygon;
        $count = count($ring);

        if ($count < 3) {
            return false;
        }

        $inside = false;
        $x = $lng;
        $y = $lat;

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = (float)$ring[$i][0]; $yi = (float)$ring[$i][1];
            $xj = (float)$ring[$j][0]; $yj = (float)$ring[$j][1];

            $intersect = (($yi > $y) != ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}
