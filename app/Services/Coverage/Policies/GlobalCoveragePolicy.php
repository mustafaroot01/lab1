<?php
namespace App\Services\Coverage\Policies;

use App\DTOs\CoverageResultDTO;
use App\Services\Coverage\Contracts\CoveragePolicyInterface;
use App\Models\Setting;

class GlobalCoveragePolicy implements CoveragePolicyInterface
{
    public function check(float $lat, float $lng): CoverageResultDTO
    {
        // For global coverage, allow everywhere with a default fee
        $defaultFee = (float) Setting::get('coverage.default_fee', 5000);

        return new CoverageResultDTO(
            isCovered: true,
            zoneId: null,
            zoneName: 'التغطية الشاملة',
            fee: $defaultFee,
            policy: 'GLOBAL',
            priority: null,
            coverageType: 'GLOBAL',
            message: 'مشمول بالتغطية الشاملة',
            snapshot: [
                'type' => 'GLOBAL',
                'fee' => $defaultFee
            ]
        );
    }
}
