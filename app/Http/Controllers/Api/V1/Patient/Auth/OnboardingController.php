<?php

namespace App\Http\Controllers\Api\V1\Patient\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\DistrictResource;
use App\Models\District;
use App\Models\LegalPage;

class OnboardingController extends Controller
{
    /**
     * جلب البيانات اللازمة للتهيئة وقوائم الأقضية والمناطق وشروط الخدمة
     */
    public function getOnboardingData()
    {
        $districts = District::with(['branch'])
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

        $termsPage = LegalPage::where('slug', 'terms')->first();
        $privacyPage = LegalPage::where('slug', 'privacy')->first();

        return response()->json([
            'status'     => true,
            'message'    => 'تم جلب بيانات التسجيل بنجاح',
            'districts'  => DistrictResource::collection($districts),
            'terms'      => $termsPage ? [
                'title'   => $termsPage->title,
                'content' => $termsPage->content,
            ] : [
                'title'   => 'شروط الخدمة',
                'content' => 'باستخدامك للتطبيق، فإنك توافق على شروط وأحكام طلب التحاليل الطبية والخدمات المنزلية.',
            ],
            'privacy'    => $privacyPage ? [
                'title'   => $privacyPage->title,
                'content' => $privacyPage->content,
            ] : [
                'title'   => 'سياسة الخصوصية',
                'content' => 'نحن نحافظ على سرية بياناتك الطبية والشخصية بأعلى معايير الأمان وخصوصية المرضى.',
            ],
        ]);
    }
}
