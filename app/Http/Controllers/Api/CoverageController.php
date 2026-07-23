<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Coverage\Contracts\CoverageEngineInterface;
use App\Services\Coverage\CoverageDebugDecorator;
use App\Models\SystemSetting;

class CoverageController extends Controller
{
    public function __construct(
        private CoverageEngineInterface $engine,
        private CoverageDebugDecorator $debugger
    ) {}

    public function check(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'debug' => 'nullable|boolean',
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;
        $isDebug = filter_var($request->debug, FILTER_VALIDATE_BOOLEAN);

        // If debug mode requested, check if simulator is enabled
        if ($isDebug) {
            $simulatorEnabled = SystemSetting::getBoolean('coverage_simulator_enabled', true);
            if (!$simulatorEnabled) {
                return response()->json([
                    'status' => false,
                    'message' => 'أداة المحاكاة معطلة حالياً من الإعدادات'
                ], 403);
            }

            return response()->json($this->debugger->simulateWithDebug($lat, $lng));
        }

        // Standard production run
        $result = $this->engine->verifyCoverage($lat, $lng);

        return response()->json([
            'status' => true,
            'message' => 'تم فحص التغطية',
            'data' => [
                'result' => $result->toArray()
            ]
        ]);
    }
}
