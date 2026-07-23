<?php
namespace App\Services\Coverage\Policies;

use App\DTOs\CoverageResultDTO;
use App\Repositories\CoverageRepository;
use App\Services\Coverage\Contracts\CoveragePolicyInterface;
use App\Services\Coverage\ZonePricingService;

class ZoneCoveragePolicy implements CoveragePolicyInterface
{
    public function __construct(
        private CoverageRepository $repository,
        private ZonePricingService $pricingService
    ) {}

    public function check(float $lat, float $lng): CoverageResultDTO
    {
        $zone = $this->repository->findCoveringZone($lat, $lng);

        if (!$zone) {
            return new CoverageResultDTO(
                isCovered: false,
                message: 'عذراً، الخدمة غير متوفرة في موقعك الحالي.'
            );
        }

        $fee = $this->pricingService->calculate($zone);

        return new CoverageResultDTO(
            isCovered: true,
            zoneId: $zone->id,
            zoneName: $zone->name,
            fee: $fee,
            policy: 'ZONE_BASED',
            priority: $zone->priority,
            coverageType: $zone->coverage_type,
            message: 'مشمول ضمن مناطق التغطية',
            snapshot: $zone->toArray()
        );
    }
}
