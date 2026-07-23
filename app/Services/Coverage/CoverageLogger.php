<?php

namespace App\Services\Coverage;

use Illuminate\Support\Facades\DB;

class CoverageLogger
{
    /**
     * Log the coverage verification if it meets the smart logging criteria.
     */
    public function log(
        float $lat,
        float $lng,
        ?int $patientId,
        ?int $matchedZoneId,
        float $executionTimeMs,
        string $algorithmUsed,
        bool $insidePolygon,
        bool $graceMatch,
        ?float $distanceFromBorder
    ): void {
        if (!config('coverage.log.enabled', true)) {
            return;
        }

        $shouldLog = config('coverage.log.log_all', false);

        if (!$shouldLog && $executionTimeMs > config('coverage.log.slow_request_ms', 30)) {
            $shouldLog = true;
        }

        if (!$shouldLog && $graceMatch && config('coverage.log.log_grace_matches', true)) {
            $shouldLog = true;
        }

        if (!$shouldLog && $matchedZoneId === null && config('coverage.log.log_no_matches', true)) {
            $shouldLog = true;
        }

        if ($shouldLog) {
            DB::table('coverage_verification_logs')->insert([
                'patient_id' => $patientId,
                'latitude' => $lat,
                'longitude' => $lng,
                'matched_zone_id' => $matchedZoneId,
                'execution_time_ms' => $executionTimeMs,
                'algorithm_used' => $algorithmUsed,
                'inside_polygon' => $insidePolygon,
                'grace_match' => $graceMatch,
                'distance_from_border' => $distanceFromBorder,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
