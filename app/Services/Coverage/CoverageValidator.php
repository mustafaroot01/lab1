<?php
namespace App\Services\Coverage;

use App\Models\Setting;

use App\Models\SystemSetting;

class CoverageValidator
{
    public function isCoverageEnabled(): bool
    {
        try {
            return SystemSetting::getBoolean('coverage.enabled', true);
        } catch (\Exception $e) {
            return true;
        }
    }

    public function getCoverageMode(): string
    {
        try {
            return Setting::get('coverage.mode', 'ZONE_BASED');
        } catch (\Exception $e) {
            return 'ZONE_BASED';
        }
    }
}
