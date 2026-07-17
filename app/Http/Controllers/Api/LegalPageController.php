<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLegalPageRequest;
use App\Http\Requests\UpdateLegalPageRequest;
use App\Http\Resources\LegalPageResource;
use App\Models\LegalPage;
use Illuminate\Http\Request;

class LegalPageController extends Controller
{
    /**
     * التحقق من وجود الصفحات الأساسية وإنشاؤها عند أول زيارة
     */
    protected function ensureDefaultPagesExist(): void
    {
        if (LegalPage::count() === 0) {
            LegalPage::create([
                'title'           => 'الشروط والأحكام (Terms & Conditions)',
                'slug'            => 'terms',
                'content'         => "# الشروط والأحكام عامة ومقدمة\n\nمرحباً بكم في تطبيق المختبر الطبي للخدمات المنزلية والمخبرية. يُرجى قراءة هذه الشروط والأحكام بعناية قبل استخدام خدماتنا...\n\n### 1. شروط الخدمة والحجز الميداني\n- يجب تقديم معلومات دقيقة وصحيحة عند طلب فني سحب العينات.\n- تلتزم الإدارة بالمواعيد المحددة وفق المتاح جغرافياً.\n\n### 2. سياسة الإلغاء\n- يمكن إلغاء الطلب قبل تحرك الفني الميداني باتجاه موقع العميل دون أي رسوم.",
                'is_active'       => true,
                'last_updated_at' => now(),
            ]);

            LegalPage::create([
                'title'           => 'سياسة الخصوصية وحماية البيانات (Privacy Policy)',
                'slug'            => 'privacy',
                'content'         => "# سياسة الخصوصية وحسرية البيانات المخبرية\n\nنحن نولي سرية وخصوصية نتائج تحاليل المرضى وبياناتهم الطبية أقصى درجات الحماية والأمان وفق معايير الجودة الصحية الدولية.\n\n### 1. البيانات التي نجمعها\n- الاسم ورقم الهاتف والعنوان الجغرافي للوصول الميداني.\n- نتائج الفحوصات وسجل التحاليل المخبرية للمريض.",
                'is_active'       => true,
                'last_updated_at' => now(),
            ]);
        }
    }

    /**
     * قائمة الصفحات القانونية
     */
    public function index()
    {
        $this->ensureDefaultPagesExist();

        $pages = LegalPage::orderBy('id', 'asc')->get();

        return response()->json([
            'status'     => true,
            'message'    => 'تم جلب قائمة الصفحات القانونية بنجاح',
            'pages'      => LegalPageResource::collection($pages),
            'totalPages' => $pages->count(),
            'summary'    => [
                'total'    => LegalPage::count(),
                'active'   => LegalPage::where('is_active', true)->count(),
                'inactive' => LegalPage::where('is_active', false)->count(),
            ],
        ]);
    }

    /**
     * إضافة صفحة قانونية جديدة
     */
    public function store(StoreLegalPageRequest $request)
    {
        $data = $request->validated();
        $data['last_updated_at'] = now();

        $page = LegalPage::create($data);

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة الصفحة القانونية بنجاح',
            'page'    => new LegalPageResource($page),
        ], 201);
    }

    /**
     * عرض صفحة قانونية محددة
     */
    public function show(LegalPage $legalPage)
    {
        return response()->json([
            'status'  => true,
            'message' => 'تم جلب بيانات الصفحة بنجاح',
            'page'    => new LegalPageResource($legalPage),
        ]);
    }

    /**
     * تحديث صفحة قانونية
     */
    public function update(UpdateLegalPageRequest $request, LegalPage $legalPage)
    {
        $data = $request->validated();
        $data['last_updated_at'] = now();

        $legalPage->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'تم حفظ وتحديث الصفحة القانونية بنجاح',
            'page'    => new LegalPageResource($legalPage->fresh()),
        ]);
    }

    /**
     * حذف صفحة قانونية
     */
    public function destroy(LegalPage $legalPage)
    {
        $legalPage->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف الصفحة بنجاح',
        ]);
    }

    /**
     * تفعيل / إيقاف الصفحة القانونية
     */
    public function toggleActive(LegalPage $legalPage)
    {
        $legalPage->update([
            'is_active'       => !$legalPage->is_active,
            'last_updated_at' => now(),
        ]);

        return response()->json([
            'status'    => true,
            'message'   => $legalPage->is_active ? 'تم تفعيل الصفحة بنجاح' : 'تم إيقاف الصفحة بنجاح',
            'page'      => new LegalPageResource($legalPage->fresh()),
        ]);
    }
}
