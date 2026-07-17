<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactInfoRequest;
use App\Http\Requests\UpdateContactInfoRequest;
use App\Http\Resources\ContactInfoResource;
use App\Models\ContactInfo;
use Illuminate\Http\Request;

class ContactInfoController extends Controller
{
    /**
     * إنشاء قنوات تواصل نموذجية في حال كان الجدول فارغاً
     */
    protected function ensureDefaultContactInfosExist(): void
    {
        if (ContactInfo::count() === 0) {
            $defaults = [
                [
                    'channel_type' => 'phone',
                    'title'        => 'اتصل بنا المباشر (خدمة العملاء)',
                    'value'        => '07700000000',
                    'sort_order'   => 1,
                    'is_active'    => true,
                ],
                [
                    'channel_type' => 'whatsapp',
                    'title'        => 'الدعم الفني عبر واتساب',
                    'value'        => '9647700000000',
                    'sort_order'   => 2,
                    'is_active'    => true,
                ],
                [
                    'channel_type' => 'working_hours',
                    'title'        => 'أوقات العمل الميداني والمخبري',
                    'value'        => 'يومياً من الساعة 8:00 صباحاً حتى 10:00 مساءً (خدمة الطوارئ 24 ساعة)',
                    'sort_order'   => 3,
                    'is_active'    => true,
                ],
                [
                    'channel_type' => 'address',
                    'title'        => 'المقر الرئيسي للمختبر',
                    'value'        => 'العراق - بغداد - الكرادة - شارع 62 الطبي - مجمع المختبرات',
                    'sort_order'   => 4,
                    'is_active'    => true,
                ],
                [
                    'channel_type' => 'email',
                    'title'        => 'البريد الإلكتروني للإدارة والاستفسارات',
                    'value'        => 'info@healthylab-iq.com',
                    'sort_order'   => 5,
                    'is_active'    => true,
                ],
                [
                    'channel_type' => 'facebook',
                    'title'        => 'صفحتنا الرسمية على فيسبوك',
                    'value'        => 'https://facebook.com/healthylab.iq',
                    'sort_order'   => 6,
                    'is_active'    => true,
                ],
                [
                    'channel_type' => 'telegram',
                    'title'        => 'قناة المختبر على تيليجرام للنتائج والعروض',
                    'value'        => 'https://t.me/healthylab_iq',
                    'sort_order'   => 7,
                    'is_active'    => true,
                ],
            ];

            foreach ($defaults as $info) {
                ContactInfo::create($info);
            }
        }
    }

    /**
     * قائمة وسائل التواصل
     */
    public function index()
    {
        $this->ensureDefaultContactInfosExist();

        $infos = ContactInfo::orderBy('sort_order', 'asc')->orderBy('id', 'desc')->get();

        return response()->json([
            'status'     => true,
            'message'    => 'تم جلب معلومات التواصل بنجاح',
            'infos'      => ContactInfoResource::collection($infos),
            'totalInfos' => $infos->count(),
            'summary'    => [
                'total'    => ContactInfo::count(),
                'active'   => ContactInfo::where('is_active', true)->count(),
                'inactive' => ContactInfo::where('is_active', false)->count(),
            ],
        ]);
    }

    /**
     * إضافة وسيلة تواصل جديدة
     */
    public function store(StoreContactInfoRequest $request)
    {
        $data = $request->validated();
        if (empty($data['sort_order'])) {
            $max = ContactInfo::max('sort_order');
            $data['sort_order'] = $max ? $max + 1 : 1;
        }

        $info = ContactInfo::create($data);

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة وسيلة التواصل بنجاح',
            'info'    => new ContactInfoResource($info),
        ], 201);
    }

    /**
     * عرض وسيلة تواصل
     */
    public function show(ContactInfo $contactInfo)
    {
        return response()->json([
            'status' => true,
            'info'   => new ContactInfoResource($contactInfo),
        ]);
    }

    /**
     * تحديث وسيلة تواصل
     */
    public function update(UpdateContactInfoRequest $request, ContactInfo $contactInfo)
    {
        $contactInfo->update($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث معلومات التواصل بنجاح',
            'info'    => new ContactInfoResource($contactInfo->fresh()),
        ]);
    }

    /**
     * حذف وسيلة تواصل
     */
    public function destroy(ContactInfo $contactInfo)
    {
        $contactInfo->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف وسيلة التواصل بنجاح',
        ]);
    }

    /**
     * تفعيل / إيقاف وسيلة التواصل
     */
    public function toggleActive(ContactInfo $contactInfo)
    {
        $contactInfo->update(['is_active' => !$contactInfo->is_active]);

        return response()->json([
            'status'    => true,
            'message'   => $contactInfo->is_active ? 'تم تفعيل وسيلة التواصل بنجاح' : 'تم إيقاف وسيلة التواصل بنجاح',
            'info'      => new ContactInfoResource($contactInfo->fresh()),
        ]);
    }
}
