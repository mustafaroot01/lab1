# قواعد معمارية الباك اند (Laravel Backend Architecture & API Standards)

هذه القاعدة إجبارية وثابتة ويجب تطبيقها وحفظها في الذاكرة لجميع المشاريع الحالية والمستقبلية:

## 1. استخدام Form Requests دائماً للتحقق من البيانات (Validation Requests)
- يمنع كتابة `$request->validate([...])` مباشرة داخل الـ Controllers.
- يجب إنشاء ملفات `FormRequest` مخصصة في `app/Http/Requests` (مثال: `StoreCouponRequest`, `UpdateCouponRequest`, `RecordCouponUsageRequest`).
- يتم وضع جميع شروط التحقق (`rules`) والرسائل المخصصة (`messages`) داخل هذه الملفات لضمان نظافة وفصل المسؤوليات في الـ Controllers.

## 2. استخدام API Resources دائماً لتحويل وتنسيق الردود (API Resources)
- يمنع إرجاع الـ Eloquent Models مباشرة في الـ JSON responses.
- يجب دائماً إنشاء واستخدام ملفات `JsonResource` في `app/Http/Resources` (مثال: `CouponResource`, `CouponUsageResource`).
- يتم تحديد الحقول المطلوبة وتنسيق التواريخ والـ Casts والعلاقات (`$this->whenLoaded(...)`) داخل الـ Resource.

## 3. توحيد صيغة الاستجابة (Standard API Response Format)
يجب أن تكون استجابات الـ API دائماً مهيكلة وثابتة بهذا النمط:
```json
{
  "status": true,
  "message": "رسالة توضيحية للعملية",
  "data": { ... } // أو الـ resource المرجع
}
```
وفي حال الجداول والـ Pagination:
```json
{
  "status": true,
  "message": "تم جلب البيانات بنجاح",
  "coupons": [ ... ], // قائمة العناصر عبر Resource::collection
  "totalCoupons": 50,
  "summary": { ... }
}
```

## 4. الحفاظ على التصميم العام والواجهات (Frontend Standards)
- عدم كتابة أكواد فرونت اند عشوائية من الصفر بل يتم استخدام تصميم ومكونات قالب **Materio Template** دائماً وتنسيقاته الجاهزة (`VRow`, `VCol`, `AppTextField`, `AppSelect`, `AppDateTimePicker`).
- يجب أن تكون صفحات الإضافة والتعديل متناسقة مع الأنماط الموجودة في المشروع (مثل صفحة الإضافة الخاصة بمنتجات المتجر `/apps/ecommerce/product/add`).
