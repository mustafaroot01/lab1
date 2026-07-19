<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\Tag;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class ScrambleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // السماح بالوصول لصفحة التوثيق /docs/api في أي وقت لاختبار كافة الراوتات بسهولة
        Gate::define('viewApiDocs', function ($user = null) {
            return true;
        });

        // قصر التوثيق على راوتات الـ API فقط لضمان دقة ونظافة التوثيق
        Scramble::routes(function (Route $route) {
            return Str::startsWith($route->uri, 'api/');
        });

        // إدراج توثيق المصادقة Bearer Token وترتيب وتصنيف كافة الواجهات في أقسام منظمة
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer', 'JWT')
            );

            // ─── 1. تعريف قائمة التصنيفات مرتبة ومرقمة بوضوح مع وصف عربي/إنجليزي ───
            $orderedTags = [
                new Tag('01. 🔐 مصادقة الإدارة (Admin Auth)', 'واجهات تسجيل دخول ومصادقة مدراء وموظفي لوحة التحكم'),
                new Tag('02. 📊 إحصائيات لوحة التحكم (Dashboard Stats)', 'مؤشرات الأداء وإحصائيات المختبر العامة في الصفحة الرئيسية'),
                new Tag('03. 📦 الباقات والعروض المخبرية (Packages & Offers)', 'إدارة عروض الفحوصات المخبرية الشاملة وأسعارها وتفاصيلها'),
                new Tag('04. 🎟️ الكوبونات والخصومات (Coupons)', 'إدارة أكواد الخصم الترويجية وتتبع سجلات استخدامها'),
                new Tag('05. 🏥 إدارة الفروع والتغطية (Branches & Coverage)', 'إدارة فروع المختبر وأجور خدمة الزيارات المنزلية وتغطية الأحياء'),
                new Tag('06. 💉 الفنيين والمندوبين (Technicians Management)', 'إدارة بيانات وأطقم سحب العينات الميدانية وحالاتهم الميدانية'),
                new Tag('07. 🧑‍🤝‍🧑 المرضى والمراجعين (Patients Management)', 'إدارة ملفات المرضى المسجلين وسجلاتهم وحجوزاتهم المخبرية'),
                new Tag('08. 📋 إدارة الطلبات والزيارات (Orders Management)', 'متابعة وتعيين وتحديث حالات طلبات التحاليل وسحب العينات'),
                new Tag('09. 🔬 القاموس الطبي والتحاليل (Medical Dictionary)', 'إدارة قائمة الفحوصات والتحاليل والمجموعات الطبية وأنابيب السحب'),
                new Tag('10. 💬 المحادثات والدعم الفني للإدارة (Admin Chat)', 'إدارة قنوات ومحادثات الدعم الفني المباشر مع المراجعين عبر WebSockets'),
                new Tag('11. ⚙️ إعدادات النظام والـ OTP (System Settings)', 'الإعدادات العامة وإعدادات وسجلات خدمة إرسال رموز الـ OTP'),
                new Tag('12. 🖼️ البنرات والستوريات الإعلانية (Banners & Stories)', 'إدارة البنرات الإعلانية والقصص الطولية (Stories) في تطبيق الموبايل'),
                new Tag('13. 🗺️ المناطق والجغرافيا (Districts & Areas)', 'إدارة المحافظات والمناطق والأحياء الجغرافية المعتمدة في النظام'),
                new Tag('14. ❓ الأسئلة الشائعة وصفحات المعلومات (FAQs & Legal)', 'إدارة صفحات الشروط والأحكام وسياسة الخصوصية والأسئلة المكررة'),
                new Tag('15. 📱 تطبيق المرضى — المصادقة والملف الشخصي (Mobile Patient Auth)', 'واجهات مصادقة المرضى عبر OTP وشاشات الترحيب وإدارة الملف الشخصي'),
                new Tag('16. 🛒 تطبيق المرضى — الكتالوج والسلة والطلبات (Mobile Catalog & Orders)', 'تصفح الفحوصات والبحث وسلة العينات وإتمام طلبات الزيارات المنزلية'),
                new Tag('17. 📁 تطبيق المرضى — السجلات والنتائج المخبرية (Mobile Medical Records)', 'عرض وتحميل السجلات والتقارير والنتائج المخبرية للمرضى'),
                new Tag('18. 💬 تطبيق المرضى — الدردشة الفورية مع الدعم (Mobile Chat)', 'قنوات التواصل المباشر ومشاركة المرفقات بين المريض والمختبر'),
                new Tag('19. 🛠️ تطبيق الفنيين الميدانيين (Technician App APIs)', 'واجهات مصادقة وعمل الفنيين الميدانيين لإنجاز مهام سحب العينات'),
            ];

            $openApi->tags = $orderedTags;

            // ─── 2. إعادة تصنيف كل مسارات الـ API وربطها بالأقسام المحددة ───
            foreach ($openApi->paths as $pathItem) {
                $path = ltrim($pathItem->path, '/');
                $tagName = '14. ❓ الأسئلة الشائعة وصفحات المعلومات (FAQs & Legal)'; // الافتراضي العام

                if (Str::startsWith($path, ['api/v1/technician', 'v1/technician'])) {
                    $tagName = '19. 🛠️ تطبيق الفنيين الميدانيين (Technician App APIs)';
                } elseif (Str::startsWith($path, ['api/v1/mobile/auth', 'v1/mobile/auth', 'mobile/auth', 'api/v1/patient/auth', 'v1/patient/auth', 'api/v1/patient/profile', 'v1/patient/profile', 'api/v1/mobile/onboarding', 'v1/mobile/onboarding', 'mobile/onboarding'])) {
                    $tagName = '15. 📱 تطبيق المرضى — المصادقة والملف الشخصي (Mobile Patient Auth)';
                } elseif (Str::startsWith($path, ['api/v1/mobile/catalog', 'v1/mobile/catalog', 'mobile/catalog', 'v1/patient/catalog', 'api/v1/mobile/cart', 'v1/mobile/cart', 'mobile/cart', 'v1/patient/cart', 'api/v1/mobile/orders', 'v1/mobile/orders', 'mobile/orders', 'v1/patient/orders', 'api/v1/mobile/search', 'v1/mobile/search', 'mobile/search', 'v1/patient/search', 'mobile/packages', 'v1/patient/packages', 'mobile/tests', 'v1/patient/tests', 'mobile/coupon', 'v1/patient/coupon', 'api/medical-dictionary/patient-catalog', 'medical-dictionary/patient-catalog'])) {
                    $tagName = '16. 🛒 تطبيق المرضى — الكتالوج والسلة والطلبات (Mobile Catalog & Orders)';
                } elseif (Str::startsWith($path, ['api/v1/mobile/medical-records', 'v1/mobile/medical-records', 'mobile/medical-records', 'api/v1/patient/medical-records', 'v1/patient/medical-records'])) {
                    $tagName = '17. 📁 تطبيق المرضى — السجلات والنتائج المخبرية (Mobile Medical Records)';
                } elseif (Str::startsWith($path, ['api/v1/mobile/chat', 'v1/mobile/chat', 'mobile/chat'])) {
                    $tagName = '18. 💬 تطبيق المرضى — الدردشة الفورية مع الدعم (Mobile Chat)';
                } elseif (Str::startsWith($path, ['api/v1/mobile/popup-stories', 'v1/mobile/popup-stories', 'mobile/popup-stories', 'api/popup-stories/active', 'popup-stories/active'])) {
                    $tagName = '12. 🖼️ البنرات والستوريات الإعلانية (Banners & Stories)';
                } elseif (Str::startsWith($path, ['api/auth', 'auth'])) {
                    $tagName = '01. 🔐 مصادقة الإدارة (Admin Auth)';
                } elseif (Str::startsWith($path, ['api/admin/dashboard', 'admin/dashboard', 'api/dashboard', 'dashboard'])) {
                    $tagName = '02. 📊 إحصائيات لوحة التحكم (Dashboard Stats)';
                } elseif (Str::startsWith($path, ['api/package-offers', 'package-offers'])) {
                    $tagName = '03. 📦 الباقات والعروض المخبرية (Packages & Offers)';
                } elseif (Str::startsWith($path, ['api/coupons', 'coupons'])) {
                    $tagName = '04. 🎟️ الكوبونات والخصومات (Coupons)';
                } elseif (Str::startsWith($path, ['api/branches', 'branches', 'api/branch-service-fees', 'branch-service-fees'])) {
                    $tagName = '05. 🏥 إدارة الفروع والتغطية (Branches & Coverage)';
                } elseif (Str::startsWith($path, ['api/technicians', 'technicians'])) {
                    $tagName = '06. 💉 الفنيين والمندوبين (Technicians Management)';
                } elseif (Str::startsWith($path, ['api/patients', 'patients'])) {
                    $tagName = '07. 🧑‍🤝‍🧑 المرضى والمراجعين (Patients Management)';
                } elseif (Str::startsWith($path, ['api/orders', 'orders'])) {
                    $tagName = '08. 📋 إدارة الطلبات والزيارات (Orders Management)';
                } elseif (Str::startsWith($path, ['api/medical-dictionary', 'medical-dictionary'])) {
                    $tagName = '09. 🔬 القاموس الطبي والتحاليل (Medical Dictionary)';
                } elseif (Str::startsWith($path, ['api/admin/chat', 'admin/chat', 'broadcasting/auth'])) {
                    $tagName = '10. 💬 المحادثات والدعم الفني للإدارة (Admin Chat)';
                } elseif (Str::startsWith($path, ['api/settings', 'settings'])) {
                    $tagName = '11. ⚙️ إعدادات النظام والـ OTP (System Settings)';
                } elseif (Str::startsWith($path, ['api/banners', 'banners', 'api/popup-stories', 'popup-stories'])) {
                    $tagName = '12. 🖼️ البنرات والستوريات الإعلانية (Banners & Stories)';
                } elseif (Str::startsWith($path, ['api/districts', 'districts', 'api/areas', 'areas'])) {
                    $tagName = '13. 🗺️ المناطق والجغرافيا (Districts & Areas)';
                } elseif (Str::startsWith($path, ['api/faqs', 'faqs', 'api/legal-pages', 'legal-pages', 'api/contact-infos', 'contact-infos'])) {
                    $tagName = '14. ❓ الأسئلة الشائعة وصفحات المعلومات (FAQs & Legal)';
                }

                foreach ($pathItem->operations as $method => $operation) {
                    if ($operation) {
                        $operation->tags = [$tagName];
                    }
                }
            }
        });
    }
}
