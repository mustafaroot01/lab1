<?php
namespace App\Services\Coverage;

use App\DTOs\CoverageResultDTO;
use App\Services\Coverage\Policies\GlobalCoveragePolicy;
use App\Services\Coverage\Policies\ZoneCoveragePolicy;

class CoverageLocator
{
    public function __construct(
        private CoverageValidator $validator,
        private GlobalCoveragePolicy $globalPolicy,
        private ZoneCoveragePolicy $zonePolicy
    ) {}

    public function locate(float $lat, float $lng): CoverageResultDTO
    {
        if (!$this->validator->isCoverageEnabled()) {
            return new CoverageResultDTO(
                isCovered: false,
                message: 'خدمة الزيارات المنزلية متوقفة حالياً من قبل الإدارة.'
            );
        }

        $mode = $this->validator->getCoverageMode();

        if ($mode === 'GLOBAL') {
            return $this->globalPolicy->check($lat, $lng);
        }

        // Default to ZONE_BASED
        return $this->zonePolicy->check($lat, $lng);
    }
}
