<?php

namespace App\Http\Controllers\Api\V1\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\TechnicianLoginRequest;
use App\Http\Resources\TechnicianResource;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TechnicianAuthController extends Controller
{
    /**
     * تسجيل دخول الفني الميداني (برقم الهاتف وكلمة المرور)
     */
    public function login(TechnicianLoginRequest $request)
    {
        $technician = Technician::where('phone', $request->phone)->first();

        if (!$technician || !Hash::check($request->password, $technician->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'بيانات الدخول غير صحيحة، يرجى التأكد من رقم الهاتف وكلمة المرور',
            ], 401);
        }

        if ($technician->status !== 'active') {
            return response()->json([
                'status'  => false,
                'message' => 'حسابك موقوف حالياً من قِبل المشرف، يرجى التواصل مع الإدارة',
            ], 403);
        }

        // إبطال التوكنات السابقة إن رغبت أو إصدار توكن جديد للموبايل
        $token = $technician->createToken('technician-mobile-token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'تم تسجيل دخول الفني بنجاح',
            'data'    => [
                'token'      => $token,
                'technician' => new TechnicianResource($technician),
            ],
        ]);
    }

    /**
     * الملف الشخصي للفني والإحصائيات اليومية الحية
     */
    public function me(Request $request)
    {
        /** @var \App\Models\Technician $technician */
        $technician = $request->user();

        // إحصائيات سريعة للزيارات المسندة إليه اليوم وفي الانتظار
        $ordersCount = $technician->orders()->count();
        $inProgressToday = $technician->orders()
            ->whereIn('status', ['technician_assigned', 'on_the_way', 'in_progress'])
            ->count();
        $completedToday = $technician->orders()
            ->where('status', 'sample_collected')
            ->whereDate('updated_at', now()->toDateString())
            ->count();

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب بيانات الفني بنجاح',
            'data'    => [
                'technician' => new TechnicianResource($technician),
                'summary'    => [
                    'total_orders'     => $ordersCount,
                    'in_progress'      => $inProgressToday,
                    'completed_today'  => $completedToday,
                ],
            ],
        ]);
    }

    /**
     * تسجيل خروج الفني وإبطال التوكن الحالي
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }
}
