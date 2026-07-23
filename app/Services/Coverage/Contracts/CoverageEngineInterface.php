<?php

namespace App\Services\Coverage\Contracts;

use App\DTOs\CoverageResultDTO;

interface CoverageEngineInterface
{
    /**
     * Verify coverage for a given coordinate.
     */
    public function verifyCoverage(float $lat, float $lng, ?int $patientId = null): CoverageResultDTO;
}
