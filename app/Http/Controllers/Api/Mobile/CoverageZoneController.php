<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CoverageZone;
use Illuminate\Http\Request;

class CoverageZoneController extends Controller
{
    /**
     * استرجاع جميع مناطق التغطية النشطة لغرض رسمها على خريطة تطبيق المريض
     */
    public function index(Request $request)
    {
        // جلب المناطق الفعالة أو التي تحت الصيانة فقط (التي تظهر للمستخدم)
        $zones = CoverageZone::whereIn('status', ['ACTIVE', 'MAINTENANCE'])->get();

        return response()->json([
            'status' => true,
            'message' => 'تم جلب مناطق التغطية بنجاح',
            'data' => $zones->map(function ($zone) {
                $geometry = is_string($zone->geometry) ? json_decode($zone->geometry, true) : $zone->geometry;

                return [
                    'id'                   => $zone->id,
                    'name'                 => $zone->name,
                    'status'               => $zone->status,
                    'coverage_type'        => $zone->coverage_type,
                    'pricing_type'         => $zone->pricing_type,
                    'service_fee'          => (float) $zone->service_fee,
                    'free_visit_threshold' => $zone->free_visit_threshold ? (float) $zone->free_visit_threshold : null,
                    'radius_meters'        => $zone->radius_meters,
                    'geojson'              => $geometry,
                ];
            })
        ]);
    }
}
