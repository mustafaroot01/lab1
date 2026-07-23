<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Coverage\Contracts\CoverageEngineInterface;
use Illuminate\Http\Request;

class CoverageCheckController extends Controller
{
    public function __construct(private CoverageEngineInterface $engine) {}

    /**
     * فحص تغطية موقع المريض قبل الدخول في السلة
     *
     * Request:
     *   lat  float (required)
     *   lng  float (required)
     *
     * Response (مغطى):
     * {
     *   "status": true,
     *   "covered": true,
     *   "zone_name": "بعقوبة المركز ١",
     *   "service_fee": 5000,
     *   "match_type": "polygon_exact"  // أو "grace_distance"
     * }
     *
     * Response (غير مغطى):
     * {
     *   "status": true,
     *   "covered": false,
     *   "message": "عذراً، موقعك خارج نطاق التغطية."
     * }
     */
    public function check(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ], [
            'lat.required' => 'تحديد خط العرض مطلوب',
            'lng.required' => 'تحديد خط الطول مطلوب',
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;

        $result = $this->engine->verifyCoverage($lat, $lng);

        if ($result->isCovered) {
            return response()->json([
                'status'      => true,
                'covered'     => true,
                'zone_name'   => $result->zone?->name ?? 'منطقة مغطاة',
                'service_fee' => $result->fee,
                'match_type'  => $result->matchType, // polygon_exact | grace_distance
                'message'     => $result->message ?? 'موقعك ضمن نطاق التغطية',
            ]);
        }

        return response()->json([
            'status'  => true,
            'covered' => false,
            'message' => $result->message ?? 'عذراً، الخدمة غير متوفرة في موقعك الحالي.',
        ]);
    }
}
