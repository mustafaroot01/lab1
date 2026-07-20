# هيكل استجابة الـ API الخاص ببيانات التهيئة والـ Popup (Onboarding)

بعد التحديثات وحذف المناطق (Areas)، أصبحت استجابة الـ API التي تغذي الـ Popup أو شاشة التهيئة في تطبيق الموبايل أنظف وتعتمد حصراً على الأقضية (Districts).

## المسار (Endpoint)
`GET /api/v1/patient/auth/onboarding`

## الاستجابة المتوقعة (JSON Response)

```json
{
  "status": true,
  "message": "تم جلب بيانات التسجيل بنجاح",
  "districts": [
    {
      "id": 1,
      "name": "بعقوبة",
      "governorate": "ديالى",
      "service_fee": 5000,
      "free_threshold": 50000,
      "branch_id": 2,
      "sort_order": 1,
      "is_active": true
    },
    {
      "id": 2,
      "name": "الخالص",
      "governorate": "ديالى",
      "service_fee": 10000,
      "free_threshold": 75000,
      "branch_id": 2,
      "sort_order": 2,
      "is_active": true
    }
  ],
  "terms": {
    "title": "شروط الخدمة",
    "content": "باستخدامك للتطبيق، فإنك توافق على شروط وأحكام طلب التحاليل الطبية والخدمات المنزلية."
  },
  "privacy": {
    "title": "سياسة الخصوصية",
    "content": "نحن نحافظ على سرية بياناتك الطبية والشخصية بأعلى معايير الأمان وخصوصية المرضى."
  }
}
```

### ملاحظات هامة:
1. تم إزالة مصفوفة `areas` بالكامل من كل قضاء.
2. لا يتم إرجاع إحداثيات `lat` أو `lng`.
3. واجهة المستخدم في الموبايل (Popup) يجب أن تعرض الآن قائمة الأقضية فقط `districts`.
4. عند قيام المستخدم بتسجيل حسابه أو إنشاء طلب، سيحتاج فقط لإرسال `district_id` بالإضافة إلى حقل نصي إجباري `address` أو `address_text`.
