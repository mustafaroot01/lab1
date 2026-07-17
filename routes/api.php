<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// ─── Admin Dashboard Controllers ─────────────────────────────────────────────
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\PackageOfferController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\TechnicianController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\LegalPageController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\ContactInfoController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\OtpSettingController;
use App\Http\Controllers\Api\GeneralSettingController;
use App\Http\Controllers\Api\PatientController;

// ─── راوتات عامة (يستهلكها تطبيق الموبايل بدون تسجيل دخول) ──────────────────────
Route::get('medical-dictionary/patient-catalog', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\MedicalTestController::class, 'patientCatalog']);
Route::post('check-coverage', [BranchController::class, 'checkCoverage']);
Route::apiResource('banners', BannerController::class)->only(['index', 'show']);
Route::apiResource('faqs', FaqController::class)->only(['index', 'show']);
Route::apiResource('legal-pages', LegalPageController::class)->only(['index', 'show']);
Route::apiResource('contact-infos', ContactInfoController::class)->only(['index', 'show']);
Route::apiResource('districts', DistrictController::class)->only(['index', 'show']);
Route::get('settings/general', [GeneralSettingController::class, 'index']);

// Popup Stories Mobile API — إعلانات الستوري الطولية للموبايل
Route::get('popup-stories/active', [\App\Http\Controllers\Api\V1\Mobile\MobilePopupStoryController::class, 'getActiveStories']);
Route::post('popup-stories/{popup_story}/view', [\App\Http\Controllers\Api\V1\Mobile\MobilePopupStoryController::class, 'recordView']);
Route::post('popup-stories/{popup_story}/click', [\App\Http\Controllers\Api\V1\Mobile\MobilePopupStoryController::class, 'recordClick']);

// ─── راوتات لوحة التحكم — محمية بالكامل (أدمن فقط) ────────────────────────
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

Route::prefix('medical-dictionary')->group(function () {
    Route::get('/summary', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\MedicalTestController::class, 'summary']);
    
    Route::get('/groups', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\TestGroupController::class, 'index']);
    Route::post('/groups', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\TestGroupController::class, 'store']);
    Route::put('/groups/{id}', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\TestGroupController::class, 'update']);
    Route::put('/groups/{id}/toggle-status', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\TestGroupController::class, 'toggleStatus']);
    Route::delete('/groups/{id}', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\TestGroupController::class, 'destroy']);

    Route::get('/tests', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\MedicalTestController::class, 'index']);
    Route::post('/tests', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\MedicalTestController::class, 'store']);
    Route::put('/tests/{id}', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\MedicalTestController::class, 'update']);
    Route::put('/tests/{id}/toggle-status', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\MedicalTestController::class, 'toggleStatus']);
    Route::delete('/tests/{id}', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\MedicalTestController::class, 'destroy']);

    Route::get('/sample-types', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\SampleTypeController::class, 'index']);
    Route::post('/sample-types', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\SampleTypeController::class, 'store']);
    Route::put('/sample-types/{id}', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\SampleTypeController::class, 'update']);
    Route::delete('/sample-types/{id}', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\SampleTypeController::class, 'destroy']);

    Route::get('/tube-types', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\TubeTypeController::class, 'index']);
    Route::post('/tube-types', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\TubeTypeController::class, 'store']);
    Route::put('/tube-types/{id}', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\TubeTypeController::class, 'update']);
    Route::delete('/tube-types/{id}', [\App\Http\Controllers\Api\V1\Admin\MedicalDictionary\TubeTypeController::class, 'destroy']);
});

Route::prefix('coupons')->group(function () {
    Route::get('/', [CouponController::class, 'index']);
    Route::post('/', [CouponController::class, 'store']);
    Route::get('/{id}', [CouponController::class, 'show']);
    Route::put('/{id}', [CouponController::class, 'update']);
    Route::put('/{id}/toggle-status', [CouponController::class, 'toggleStatus']);
    Route::delete('/{id}', [CouponController::class, 'destroy']);
    Route::post('/{id}/record-usage', [CouponController::class, 'recordUsage']);
});

Route::prefix('package-offers')->group(function () {
    Route::get('/', [PackageOfferController::class, 'index']);
    Route::post('/', [PackageOfferController::class, 'store']);
    Route::get('/available-tests', [PackageOfferController::class, 'availableTests']);
    Route::match(['put', 'post'], '/toggle-global-status', [PackageOfferController::class, 'toggleGlobalSection']);
    Route::match(['put', 'post'], '/toggle-global-section', [PackageOfferController::class, 'toggleGlobalSection']);
    Route::post('/upload-image', [PackageOfferController::class, 'uploadImage']);
    Route::get('/{id}', [PackageOfferController::class, 'show']);
    Route::put('/{id}', [PackageOfferController::class, 'update']);
    Route::put('/{id}/toggle-status', [PackageOfferController::class, 'toggleStatus']);
    Route::delete('/{id}', [PackageOfferController::class, 'destroy']);
});

// Branches — إدارة الفروع
Route::apiResource('branches', BranchController::class);
Route::patch('branches/{branch}/toggle-active', [BranchController::class, 'toggleActive']);

// Technicians — إدارة الفنيين
Route::apiResource('technicians', TechnicianController::class);
Route::patch('technicians/{technician}/toggle-status', [TechnicianController::class, 'toggleStatus']);
Route::post('technicians/upload-image', [TechnicianController::class, 'uploadImage']);

// Banners — إدارة المحتوى (البنرات) — القراءة عامة أعلاه
Route::apiResource('banners', BannerController::class)->except(['index', 'show']);
Route::patch('banners/{banner}/toggle-active', [BannerController::class, 'toggleActive']);
Route::post('banners/reorder', [BannerController::class, 'reorder']);

// Popup Stories Admin API — إدارة إعلانات الستوري الميدانية
Route::apiResource('popup-stories', \App\Http\Controllers\Api\PopupStoryController::class);
Route::patch('popup-stories/{popup_story}/toggle-active', [\App\Http\Controllers\Api\PopupStoryController::class, 'toggleActive']);
Route::post('popup-stories/reorder', [\App\Http\Controllers\Api\PopupStoryController::class, 'reorder']);

// Legal Pages — إدارة الصفحات القانونية والشروط والأحكام
Route::apiResource('legal-pages', LegalPageController::class)->except(['index', 'show']);
Route::patch('legal-pages/{legal_page}/toggle-active', [LegalPageController::class, 'toggleActive']);

// FAQs — إدارة الأسئلة الشائعة
Route::apiResource('faqs', FaqController::class)->except(['index', 'show']);

// Contact Info — إدارة معلومات التواصل
Route::apiResource('contact-infos', ContactInfoController::class)->except(['index', 'show']);
Route::patch('contact-infos/{contact_info}/toggle-active', [ContactInfoController::class, 'toggleActive']);

// Districts & Areas — إدارة الأقضية والمناطق وتغطية الخدمة
Route::apiResource('districts', DistrictController::class)->except(['index', 'show']);
Route::patch('districts/{district}/toggle-active', [DistrictController::class, 'toggleActive']);

Route::apiResource('areas', AreaController::class)->except(['index', 'show']);
Route::patch('areas/{area}/toggle-active', [AreaController::class, 'toggleActive']);

// Settings — إعدادات النظام وخدمة الـ OTP
Route::prefix('settings/otp')->group(function () {
    Route::get('/', [OtpSettingController::class, 'index']);
    Route::post('/', [OtpSettingController::class, 'update']);
    Route::post('/test-send', [OtpSettingController::class, 'testSend']);
});

Route::prefix('settings/general')->group(function () {
    Route::get('/', [GeneralSettingController::class, 'index']);
    Route::match(['post', 'put'], '/', [GeneralSettingController::class, 'update']);
});

// Patients — إدارة المرضى والزبائن المسجلين وسجلاتهم الطبية
Route::apiResource('patients', PatientController::class);
Route::patch('patients/{patient}/toggle-status', [PatientController::class, 'toggleStatus']);
Route::post('patients/{patient}/revoke-tokens', [PatientController::class, 'revokeTokens']);
Route::post('patients/{patient}/medical-records', [\App\Http\Controllers\Api\PatientMedicalRecordController::class, 'store']);
Route::post('patients/{patient}/medical-records/{type}', [\App\Http\Controllers\Api\PatientMedicalRecordController::class, 'store']);
Route::put('patients/{patient}/medical-records/{type}/{id}', [\App\Http\Controllers\Api\PatientMedicalRecordController::class, 'update']);
Route::delete('patients/{patient}/medical-records/{type}/{id}', [\App\Http\Controllers\Api\PatientMedicalRecordController::class, 'destroy']);
Route::delete('patients/medical-records/{type}/{id}', [\App\Http\Controllers\Api\PatientMedicalRecordController::class, 'destroy']);

// Branch Service Fees — تحديد كلفة ورسوم الزيارة لكل فرع والحد المجاني
Route::get('branch-service-fees', [\App\Http\Controllers\Api\BranchServiceFeeController::class, 'index']);
Route::put('branch-service-fees/{branch}', [\App\Http\Controllers\Api\BranchServiceFeeController::class, 'update']);
Route::patch('branch-service-fees/{branch}', [\App\Http\Controllers\Api\BranchServiceFeeController::class, 'update']);

// ─── Executive Dashboard Stats — إحصائيات لوحة التحكم الرئيسية ──────────
Route::get('dashboard/stats', [\App\Http\Controllers\Api\DashboardController::class, 'stats']);

// ─── Dashboard Orders — إدارة الطلبات من لوحة التحكم ─────────────────────
Route::get('orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
Route::get('orders/{order}', [\App\Http\Controllers\Api\OrderController::class, 'show']);
Route::patch('orders/{order}/status', [\App\Http\Controllers\Api\OrderController::class, 'updateStatus']);
Route::post('orders/{order}/results', [\App\Http\Controllers\Api\OrderController::class, 'storeResult']);
Route::delete('orders/{order}/results/{result}', [\App\Http\Controllers\Api\OrderController::class, 'destroyResult']);

}); // ─── نهاية مجموعة راوتات لوحة التحكم المحمية ──────────────────────────

// Mobile Authentication & Unified Onboarding Flow (Delegating to clean modularized V1 Auth controllers for Zero Breaking Changes)
Route::prefix('mobile/auth')->group(function () {
    Route::post('request-otp', [\App\Http\Controllers\Api\V1\Patient\Auth\OtpAuthController::class, 'requestOtp'])->middleware('throttle:5,1');
    Route::post('send-otp', [\App\Http\Controllers\Api\V1\Patient\Auth\OtpAuthController::class, 'requestOtp'])->middleware('throttle:5,1');
    Route::post('verify-otp', [\App\Http\Controllers\Api\V1\Patient\Auth\OtpAuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
    Route::get('onboarding-data', [\App\Http\Controllers\Api\V1\Patient\Auth\OnboardingController::class, 'getOnboardingData']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('complete-profile', [\App\Http\Controllers\Api\V1\Patient\Auth\ProfileController::class, 'completeProfile']);
        Route::post('update-profile', [\App\Http\Controllers\Api\V1\Patient\Auth\ProfileController::class, 'updateProfile']);
        Route::put('update-profile', [\App\Http\Controllers\Api\V1\Patient\Auth\ProfileController::class, 'updateProfile']);
        Route::put('profile', [\App\Http\Controllers\Api\V1\Patient\Auth\ProfileController::class, 'updateProfile']);
        Route::get('me', [\App\Http\Controllers\Api\V1\Patient\Auth\ProfileController::class, 'me']);
        Route::post('refresh-token', [\App\Http\Controllers\Api\V1\Patient\Auth\OtpAuthController::class, 'refreshToken']);
        Route::post('logout', [\App\Http\Controllers\Api\V1\Patient\Auth\OtpAuthController::class, 'logout']);
        Route::delete('delete-account', [\App\Http\Controllers\Api\V1\Patient\Auth\ProfileController::class, 'deleteAccount']);
    });
});

// Mobile Medical Records — السجل الدوائي والطبي للمريض (معزول معمارياً في V1/Patient/MedicalRecordController)
Route::prefix('mobile/medical-records')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\V1\Patient\MedicalRecordController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\V1\Patient\MedicalRecordController::class, 'store']);
    Route::put('/{type}/{id}', [\App\Http\Controllers\Api\V1\Patient\MedicalRecordController::class, 'update']);
    Route::patch('/{type}/{id}', [\App\Http\Controllers\Api\V1\Patient\MedicalRecordController::class, 'update']);
    Route::delete('/{type}/{id}', [\App\Http\Controllers\Api\V1\Patient\MedicalRecordController::class, 'destroy']);
});

// ─── Mobile Orders & Catalog — نظام الطلبات والكتالوج للموبايل (مقسّم ونظيف) ─────
Route::prefix('mobile')->middleware('throttle:60,1')->group(function () {
    // الكتالوج والبحث متاحان بدون تسجيل دخول
    Route::get('/catalog', [\App\Http\Controllers\Api\Mobile\CatalogController::class, 'catalog']);
    Route::get('/tests/{id}', [\App\Http\Controllers\Api\Mobile\CatalogController::class, 'testDetails']);
    Route::get('/search', [\App\Http\Controllers\Api\Mobile\SearchController::class, 'search']);
    Route::get('/packages', [\App\Http\Controllers\Api\Mobile\PackageController::class, 'packages']);
    Route::get('/packages/{id}', [\App\Http\Controllers\Api\Mobile\PackageController::class, 'packageDetails']);
    Route::post('/cart/preview', [\App\Http\Controllers\Api\Mobile\CartController::class, 'previewCart']);
    Route::post('/coupon/validate', [\App\Http\Controllers\Api\Mobile\CouponController::class, 'validateCoupon']);

    // يتطلب تسجيل دخول
    Route::middleware(['auth:sanctum', 'patient.active'])->group(function () {
        Route::post('/orders/upload-referral', [\App\Http\Controllers\Api\Mobile\ReferralController::class, 'uploadReferralImage']);
        Route::post('/orders', [\App\Http\Controllers\Api\Mobile\OrderController::class, 'store']);
        Route::get('/orders', [\App\Http\Controllers\Api\Mobile\OrderController::class, 'myOrders']);
        Route::get('/orders/{id}', [\App\Http\Controllers\Api\Mobile\OrderController::class, 'show']);
        Route::post('/orders/{id}/cancel', [\App\Http\Controllers\Api\Mobile\OrderController::class, 'cancel']);
    });
});

// ─── Admin Auth — تسجيل دخول الأدمن للوحة التحكم ──────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\AdminAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [\App\Http\Controllers\Api\AdminAuthController::class, 'me']);
        Route::post('/logout', [\App\Http\Controllers\Api\AdminAuthController::class, 'logout']);
    });
});

// ─── Chat Module — موديول الدردشة (معزول معمارياً في مجلد Modules/Chat) ─────────
// راوتات الموبايل (المريض)
Route::prefix('mobile/chat')->middleware('throttle:30,1')->group(function () {
    Route::middleware(['auth:sanctum', 'patient.active'])->group(function () {
        Route::get('/', [\App\Modules\Chat\Controllers\MobileChatController::class, 'show']);
        Route::get('/messages', [\App\Modules\Chat\Controllers\MobileChatController::class, 'loadMoreMessages']);
        Route::post('/send', [\App\Modules\Chat\Controllers\MobileChatController::class, 'send']);
        Route::post('/read', [\App\Modules\Chat\Controllers\MobileChatController::class, 'markRead']);
        Route::get('/history', [\App\Modules\Chat\Controllers\MobileChatController::class, 'history']);
    });
});

// راوتات لوحة التحكم (الأدمن)
Route::prefix('admin/chat')->middleware('auth:sanctum', 'role:admin', 'throttle:60,1')->group(function () {
    Route::get('/', [\App\Modules\Chat\Controllers\AdminChatController::class, 'index']);
    Route::get('/canned-responses', [\App\Modules\Chat\Controllers\AdminChatController::class, 'cannedResponses']);
    Route::get('/patient/{patientId}/history', [\App\Modules\Chat\Controllers\AdminChatController::class, 'patientHistory']);
    Route::get('/patient/{patientId}/profile', [\App\Modules\Chat\Controllers\AdminChatController::class, 'patientProfile']);
    Route::get('/{conversation}', [\App\Modules\Chat\Controllers\AdminChatController::class, 'show']);
    Route::get('/{conversation}/messages', [\App\Modules\Chat\Controllers\AdminChatController::class, 'loadMoreMessages']);
    Route::post('/{conversation}/send', [\App\Modules\Chat\Controllers\AdminChatController::class, 'send']);
    Route::post('/{conversation}/claim', [\App\Modules\Chat\Controllers\AdminChatController::class, 'claim']);
    Route::post('/{conversation}/close', [\App\Modules\Chat\Controllers\AdminChatController::class, 'close']);
    Route::post('/{conversation}/reopen', [\App\Modules\Chat\Controllers\AdminChatController::class, 'reopen']);
    Route::post('/{conversation}/read', [\App\Modules\Chat\Controllers\AdminChatController::class, 'markRead']);
});

// ─── API Version 1 (V1) — واجهات المراجعين والفنيين بالإصدار الرسمي الأول ──────────
Route::prefix('v1')->group(function () {
    // 1. واجهات الفني الميداني (Technician Mobile API v1)
    Route::prefix('technician')->group(function () {
        Route::post('login', [\App\Http\Controllers\Api\V1\Technician\TechnicianAuthController::class, 'login'])->middleware('throttle:10,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [\App\Http\Controllers\Api\V1\Technician\TechnicianAuthController::class, 'me']);
            Route::post('logout', [\App\Http\Controllers\Api\V1\Technician\TechnicianAuthController::class, 'logout']);
            Route::get('orders', [\App\Http\Controllers\Api\V1\Technician\TechnicianOrderController::class, 'myOrders']);
            Route::get('orders/{id}', [\App\Http\Controllers\Api\V1\Technician\TechnicianOrderController::class, 'show']);
            Route::patch('orders/{id}/status', [\App\Http\Controllers\Api\V1\Technician\TechnicianOrderController::class, 'updateStatus']);
        });
    });

    // 2. واجهات المراجع/المريض (Patient Mobile API v1)
    Route::prefix('patient')->middleware('throttle:60,1')->group(function () {
        // مصادقة المراجع وتهيئة الحساب (V1 Patient Auth)
        Route::prefix('auth')->group(function () {
            Route::post('request-otp', [\App\Http\Controllers\Api\V1\Patient\Auth\OtpAuthController::class, 'requestOtp'])->middleware('throttle:5,1');
            Route::post('send-otp', [\App\Http\Controllers\Api\V1\Patient\Auth\OtpAuthController::class, 'requestOtp'])->middleware('throttle:5,1');
            Route::post('verify-otp', [\App\Http\Controllers\Api\V1\Patient\Auth\OtpAuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
            Route::get('onboarding-data', [\App\Http\Controllers\Api\V1\Patient\Auth\OnboardingController::class, 'getOnboardingData']);

            Route::middleware(['auth:sanctum', 'patient.active'])->group(function () {
                Route::post('complete-profile', [\App\Http\Controllers\Api\V1\Patient\Auth\ProfileController::class, 'completeProfile']);
                Route::post('update-profile', [\App\Http\Controllers\Api\V1\Patient\Auth\ProfileController::class, 'updateProfile']);
                Route::put('update-profile', [\App\Http\Controllers\Api\V1\Patient\Auth\ProfileController::class, 'updateProfile']);
                Route::put('profile', [\App\Http\Controllers\Api\V1\Patient\Auth\ProfileController::class, 'updateProfile']);
                Route::get('me', [\App\Http\Controllers\Api\V1\Patient\Auth\ProfileController::class, 'me']);
                Route::post('refresh-token', [\App\Http\Controllers\Api\V1\Patient\Auth\OtpAuthController::class, 'refreshToken']);
                Route::post('logout', [\App\Http\Controllers\Api\V1\Patient\Auth\OtpAuthController::class, 'logout']);
                Route::delete('delete-account', [\App\Http\Controllers\Api\V1\Patient\Auth\ProfileController::class, 'deleteAccount']);
            });
        });

        // الكتالوج والبحث والباقات
        Route::get('catalog', [\App\Http\Controllers\Api\Mobile\CatalogController::class, 'catalog']);
        Route::get('tests/{id}', [\App\Http\Controllers\Api\Mobile\CatalogController::class, 'testDetails']);
        Route::get('search', [\App\Http\Controllers\Api\Mobile\SearchController::class, 'search']);
        Route::get('packages', [\App\Http\Controllers\Api\Mobile\PackageController::class, 'packages']);
        Route::get('packages/{id}', [\App\Http\Controllers\Api\Mobile\PackageController::class, 'packageDetails']);
        Route::post('cart/preview', [\App\Http\Controllers\Api\Mobile\CartController::class, 'previewCart']);
        Route::post('coupon/validate', [\App\Http\Controllers\Api\Mobile\CouponController::class, 'validateCoupon']);

        Route::middleware(['auth:sanctum', 'patient.active'])->group(function () {
            // السجل الدوائي والطبي للمريض (V1 Medical Records)
            Route::prefix('medical-records')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\V1\Patient\MedicalRecordController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\Api\V1\Patient\MedicalRecordController::class, 'store']);
                Route::put('/{type}/{id}', [\App\Http\Controllers\Api\V1\Patient\MedicalRecordController::class, 'update']);
                Route::patch('/{type}/{id}', [\App\Http\Controllers\Api\V1\Patient\MedicalRecordController::class, 'update']);
                Route::delete('/{type}/{id}', [\App\Http\Controllers\Api\V1\Patient\MedicalRecordController::class, 'destroy']);
            });

            // الطلبات والزيارات الميدانية للمريض
            Route::post('orders', [\App\Http\Controllers\Api\Mobile\OrderController::class, 'store']);
            Route::get('orders', [\App\Http\Controllers\Api\Mobile\OrderController::class, 'myOrders']);
            Route::get('orders/{id}', [\App\Http\Controllers\Api\Mobile\OrderController::class, 'show']);
            Route::post('orders/{id}/cancel', [\App\Http\Controllers\Api\Mobile\OrderController::class, 'cancel']);
        });
    });
});

