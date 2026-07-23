<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CoverageSettingsController extends Controller
{
    public function index()
    {
        return response()->json([
            'default_grace_distance' => (int) SystemSetting::getValue('coverage_default_grace_distance', 50),
            'cache_ttl' => (int) SystemSetting::getValue('coverage_cache_ttl', 3600),
            'log_enabled' => SystemSetting::getBoolean('coverage_log_enabled', true),
            'slow_request_ms' => (int) SystemSetting::getValue('coverage_slow_request_ms', 30),
            'log_grace_matches' => SystemSetting::getBoolean('coverage_log_grace_matches', true),
            'log_no_matches' => SystemSetting::getBoolean('coverage_log_no_matches', true),
            'log_all' => SystemSetting::getBoolean('coverage_log_all', false),
            'simulator_enabled' => SystemSetting::getBoolean('coverage_simulator_enabled', true),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'default_grace_distance' => 'required|integer|min:0',
            'cache_ttl' => 'required|integer|min:0',
            'log_enabled' => 'required|boolean',
            'slow_request_ms' => 'required|integer|min:0',
            'log_grace_matches' => 'required|boolean',
            'log_no_matches' => 'required|boolean',
            'log_all' => 'required|boolean',
            'simulator_enabled' => 'required|boolean',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::setValue("coverage_{$key}", $value);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم حفظ إعدادات محرك التغطية بنجاح'
        ]);
    }

    public function clearCache()
    {
        $version = Cache::get('coverage_zones_version', 1);
        Cache::forget("coverage_zones:v{$version}");
        
        return response()->json([
            'status' => true,
            'message' => 'تم تفريغ الكاش الخاص بالمناطق بنجاح'
        ]);
    }
}
