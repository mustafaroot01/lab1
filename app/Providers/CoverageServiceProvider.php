<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Coverage\Contracts\CoverageEngineInterface;
use App\Services\Coverage\CoverageEngine;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;

class CoverageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(CoverageEngineInterface::class, CoverageEngine::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('system_settings')) {
            config([
                'coverage.default_grace_distance' => (int) SystemSetting::getValue('coverage_default_grace_distance', config('coverage.default_grace_distance', 50)),
                'coverage.cache_ttl' => (int) SystemSetting::getValue('coverage_cache_ttl', config('coverage.cache_ttl', 3600)),
                'coverage.log.enabled' => SystemSetting::getBoolean('coverage_log_enabled', config('coverage.log.enabled', true)),
                'coverage.log.slow_request_ms' => (int) SystemSetting::getValue('coverage_slow_request_ms', config('coverage.log.slow_request_ms', 30)),
                'coverage.log.log_grace_matches' => SystemSetting::getBoolean('coverage_log_grace_matches', config('coverage.log.log_grace_matches', true)),
                'coverage.log.log_no_matches' => SystemSetting::getBoolean('coverage_log_no_matches', config('coverage.log.log_no_matches', true)),
                'coverage.log.log_all' => SystemSetting::getBoolean('coverage_log_all', config('coverage.log.log_all', false)),
                'coverage.simulator_enabled' => SystemSetting::getBoolean('coverage_simulator_enabled', true),
            ]);
        }
    }
}
