<?php

namespace App\Observers;

use App\Models\CoverageZone;
use Illuminate\Support\Facades\Cache;

class CoverageZoneObserver
{
    /**
     * Handle the CoverageZone "saved" event.
     */
    public function saved(CoverageZone $coverageZone): void
    {
        Cache::increment('coverage_zones_version');
    }

    /**
     * Handle the CoverageZone "deleted" event.
     */
    public function deleted(CoverageZone $coverageZone): void
    {
        Cache::increment('coverage_zones_version');
    }
}
