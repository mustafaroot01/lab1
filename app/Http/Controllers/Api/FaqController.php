<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * إنشاء أسئلة نموذجية أولية في التصنيفات الخمسة في حال كان الجدول فارغاً
     */
    protected function ensureDefaultFaqsExist(): void
    {
        if (Faq::count() === 0) {
            $defaultFaqs = [
                // أسئلة عامة
                [
                    'category' => 'general',
                    'question' => 'ما هو تطبيق Healthy Lab؟',
                    'answer'   => 'هو تطبيق يربط المرضى بالمختبرات الطبية المرخصة لإجراء فحوصات منزلية وسحب العينة من منزلك وإرسال النتائج إلكترونياً وبكل سهولة.',
                    'sort_order' => 1,
                ],
                [
                    'category' => 'general',
                    'question' => 'هل التطبيق متوفر في جميع المحافظات؟',
                    'answer'   => 'نعم، نعمل على تغطية معظم المدن والمحافظات العراقية عبر شبكة واسعة من الفروع المخبرية والفنيين الميدانيين.',
                    'sort_order' => 2,
                ],
                [
                    'category' => 'general',
                    'question' => 'هل أحتاج وصفة طبية لإجراء التحاليل؟',
                    'answer'   => 'لا يشترط وجود وصفة طبية لمعظم التحاليل، ويمكنك اختيار التحليل أو الباقة المخبرية التي ترغب بها مباشرة من التطبيق.',
                    'sort_order' => 3,
                ],
                [
                    'category' => 'general',
                    'question' => 'هل بياناتي الطبية محمية؟',
                    'answer'   => 'بكل تأكيد، جميع بياناتك الطبية وسجل تحاليلك مشفر ومحمي ولا يمكن لأي طرف ثالث الاطلاع عليه.',
                    'sort_order' => 4,
                ],

                // الطلبات والحجز
                [
                    'category' => 'orders',
                    'question' => 'كيف أطلب تحليل من التطبيق؟',
                    'answer'   => 'قم باختيار التحاليل أو الباقة المطلوبة، حدد عنوان منزلك والوقت المناسب لزيارة الفني، ثم أكد الحجز.',
                    'sort_order' => 1,
                ],
                [
                    'category' => 'orders',
                    'question' => 'هل يمكنني إلغاء الطلب؟',
                    'answer'   => 'نعم، يمكنك إلغاء الطلب أو إعادة جدولته من خلال شاشة طلباتي قبل تحرك الفني الميداني.',
                    'sort_order' => 2,
                ],

                // الدفع والأسعار
                [
                    'category' => 'payments',
                    'question' => 'ما هي طرق الدفع المتاحة؟',
                    'answer'   => 'يمكنك الدفع نقداً للفني عند سحب العينة أو الدفع الإلكتروني عبر بطاقات الدفع المعتمدة والدفع السريع.',
                    'sort_order' => 1,
                ],
                [
                    'category' => 'payments',
                    'question' => 'هل تضاف رسوم إضافية للزيارة المنزلية؟',
                    'answer'   => 'تعتمد رسوم سحب العينة على بعد موقعك عن الفرع، وتظهر لك التكلفة الإجمالية بوضوح قبل تأكيد الحجز.',
                    'sort_order' => 2,
                ],

                // النتائج والتقارير
                [
                    'category' => 'results',
                    'question' => 'متى تظهر نتائج التحاليل؟',
                    'answer'   => 'تظهر معظم النتائج الروتينية خلال 4 إلى 12 ساعة من سحب العينة، ويصلك إشعار فوري عند صدور التقرير.',
                    'sort_order' => 1,
                ],
                [
                    'category' => 'results',
                    'question' => 'كيف أستلم التقرير الطبي للنتيجة؟',
                    'answer'   => 'يمكنك تحميل التقرير الطبي بصيغة PDF معتمد ومختوم مباشرة من شاشة النتائج داخل التطبيق في أي وقت.',
                    'sort_order' => 2,
                ],

                // الفني وسحب العينة
                [
                    'category' => 'technician',
                    'question' => 'هل الفنيون الميدانيون مرخصون ومؤهلون؟',
                    'answer'   => 'نعم، جميع الفنيين الميدانيين حاصلون على تراخيص رسمية ومدربون على سحب العينات للأطفال والكبار بأعلى معايير التعقيم العالمية.',
                    'sort_order' => 1,
                ],
                [
                    'category' => 'technician',
                    'question' => 'ما هي شروط الصيام قبل سحب العينة؟',
                    'answer'   => 'بعض الفحوصات مثل السكر التراكمي أو الدهون تتطلب الصيام من 8 إلى 12 ساعة، وتظهر تعليمات الصيام بوضوح لكل تحليل عند الحجز.',
                    'sort_order' => 2,
                ],
            ];

            foreach ($defaultFaqs as $faq) {
                Faq::create($faq);
            }
        }
    }

    /**
     * قائمة الأسئلة الشائعة مع الإحصائيات حسب التصنيفات
     */
    public function index(Request $request)
    {
        $this->ensureDefaultFaqsExist();

        $query = Faq::query();

        // تصفية حسب التصنيف
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $faqs = $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->get();

        // إحصائيات التصنيفات
        $counts = [
            'all'        => Faq::count(),
            'general'    => Faq::where('category', 'general')->count(),
            'orders'     => Faq::where('category', 'orders')->count(),
            'payments'   => Faq::where('category', 'payments')->count(),
            'results'    => Faq::where('category', 'results')->count(),
            'technician' => Faq::where('category', 'technician')->count(),
        ];

        return response()->json([
            'status'    => true,
            'message'   => 'تم جلب الأسئلة الشائعة بنجاح',
            'faqs'      => FaqResource::collection($faqs),
            'totalFaqs' => $faqs->count(),
            'counts'    => $counts,
        ]);
    }

    /**
     * إضافة سؤال جديد
     */
    public function store(StoreFaqRequest $request)
    {
        $data = $request->validated();
        if (empty($data['sort_order'])) {
            $max = Faq::where('category', $data['category'])->max('sort_order');
            $data['sort_order'] = $max ? $max + 1 : 1;
        }

        $faq = Faq::create($data);

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة السؤال بنجاح',
            'faq'     => new FaqResource($faq),
        ], 201);
    }

    /**
     * عرض سؤال
     */
    public function show(Faq $faq)
    {
        return response()->json([
            'status' => true,
            'faq'    => new FaqResource($faq),
        ]);
    }

    /**
     * تحديث سؤال
     */
    public function update(UpdateFaqRequest $request, Faq $faq)
    {
        $faq->update($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'تم تعديل السؤال بنجاح',
            'faq'     => new FaqResource($faq->fresh()),
        ]);
    }

    /**
     * حذف سؤال
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف السؤال بنجاح',
        ]);
    }
}
