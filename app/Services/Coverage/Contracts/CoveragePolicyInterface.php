<?php
namespace App\Services\Coverage\Contracts;

use App\DTOs\CoverageResultDTO;

interface CoveragePolicyInterface
{
    public function check(float $lat, float $lng): CoverageResultDTO;
}
