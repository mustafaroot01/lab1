<?php

namespace App\Repositories\Coverage;

use App\Models\CoverageZone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CoverageRepository
{
    /**
     * Get all active coverage zones, utilizing Cache-Aside pattern.
     * The cache key is versioned to prevent race conditions during updates.
     */
    public function getActiveZones(): Collection
    {
        $version = Cache::get('coverage_zones_version', 1);
        $cacheKey = "coverage_zones:v{$version}";
        $ttl = config('coverage.cache_ttl', 3600);

        return Cache::remember($cacheKey, $ttl, function () {
            // We only need ACTIVE zones for coverage checks.
            return CoverageZone::where('status', 'ACTIVE')->get();
        });
    }

    /**
     * Manually forget the current version of the cache.
     * Typically, updating the version is enough, but this provides a hard reset.
     */
    public function forgetCache(): void
    {
        $version = Cache::get('coverage_zones_version', 1);
        Cache::forget("coverage_zones:v{$version}");
    }

    /**
     * Force a cache refresh by incrementing the version.
     */
    public function refreshCache(): void
    {
        Cache::increment('coverage_zones_version');
    }

    /**
     * Find a zone by ID directly from DB.
     */
    public function findById(int $id): ?CoverageZone
    {
        return CoverageZone::find($id);
    }
}
