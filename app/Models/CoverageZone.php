<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoverageZone extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'coverage_type',
        'geometry',
        'radius_meters',
        'pricing_type',
        'service_fee',
        'free_visit_threshold',
        'priority',
        'status',
        'effective_from',
        'effective_to',
        'starts_at',
        'ends_at',
        'min_lat',
        'max_lat',
        'min_lng',
        'max_lng',
        'grace_distance',
    ];

    protected $casts = [
        'geometry' => 'json',
        'is_active' => 'boolean',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
        'starts_at' => 'string',
        'ends_at' => 'string',
        'service_fee' => 'float',
        'free_visit_threshold' => 'float',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function ($zone) {
            $zone->ensurePolygonIsClosed();
            $zone->calculateBoundingBox();
        });
    }

    /**
     * Ensure the first and last points of the polygon are identical.
     */
    public function ensurePolygonIsClosed(): void
    {
        if ($this->coverage_type !== 'POLYGON' || empty($this->geometry)) {
            return;
        }

        $geometry = is_array($this->geometry) ? $this->geometry : json_decode($this->geometry, true);

        // A valid GeoJSON polygon is an array of linear rings. 
        // For simplicity, we assume the outer array is the list of coordinates.
        if (is_array($geometry) && count($geometry) > 0) {
            // Support both standard Array and GeoJSON Polygon structure.
            // Typically our structure was [ [lat, lng], [lat, lng] ]
            if (isset($geometry[0]) && is_array($geometry[0])) {
                // If it's a deep GeoJSON (e.g. coordinates array inside)
                if (isset($geometry[0][0]) && is_array($geometry[0][0])) {
                   $ring = &$geometry[0];
                } else {
                   $ring = &$geometry;
                }

                $first = $ring[0];
                $last = $ring[count($ring) - 1];

                if ($first[0] !== $last[0] || $first[1] !== $last[1]) {
                    $ring[] = $first;
                }
            }
            $this->geometry = $geometry;
        }
    }

    /**
     * Calculate and set min/max bounding box fields.
     */
    public function calculateBoundingBox(): void
    {
        if ($this->coverage_type !== 'POLYGON' || empty($this->geometry)) {
            return;
        }

        $geometry = is_array($this->geometry) ? $this->geometry : json_decode($this->geometry, true);

        // Support both GeoJSON format { type, coordinates: [[[lng,lat],...]] }
        // and plain coordinates array [[[lng,lat],...]] or [[lng,lat],...]
        if (isset($geometry['coordinates'])) {
            $coords = $geometry['coordinates'];
        } else {
            $coords = $geometry;
        }

        // Extract ring (outer boundary)
        $ring = isset($coords[0][0]) && is_array($coords[0][0]) ? $coords[0] : $coords;

        if (empty($ring)) {
            return;
        }

        $minLat = 90.0;
        $maxLat = -90.0;
        $minLng = 180.0;
        $maxLng = -180.0;

        foreach ($ring as $point) {
            if (!isset($point[0]) || !isset($point[1])) continue;
            // GeoJSON format: [longitude, latitude]
            $pLng = (float) $point[0];
            $pLat = (float) $point[1];

            if ($pLat < $minLat) $minLat = $pLat;
            if ($pLat > $maxLat) $maxLat = $pLat;
            if ($pLng < $minLng) $minLng = $pLng;
            if ($pLng > $maxLng) $maxLng = $pLng;
        }

        if ($minLat !== 90.0) {
            $this->min_lat = $minLat;
            $this->max_lat = $maxLat;
            $this->min_lng = $minLng;
            $this->max_lng = $maxLng;
        }
    }
}
