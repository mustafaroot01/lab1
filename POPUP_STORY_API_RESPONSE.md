# استجابة إعلانات البوب ستوري (Popup Stories API Responses)

يحتوي هذا الملف على شكل الـ JSON Response الخاص بميزة "الستوري الطولية" (Popup Stories) التي تظهر للمراجع عند فتح تطبيق الموبايل.

---

### 1. جلب الستوريات النشطة (Get Active Stories)
تُستخدم لجلب الإعلانات النشطة حالياً لعرضها للمريض في الشاشة الرئيسية (Home Screen).

* **الرابط:** `GET /api/popup-stories/active`
* **الحماية:** متاح للعامة (Public) ولا يتطلب تسجيل دخول.
* **الرد الناجح (200 OK):**
```json
{
  "status": true,
  "message": "تم جلب إعلانات الستوري النشطة بنجاح",
  "stories": [
    {
      "id": 1,
      "title": "عرض خاص بمناسبة الافتتاح",
      "image_url": "https://lab.diyala.org/storage/stories/img_123.jpg",
      "image_path": "stories/img_123.jpg",
      "duration_seconds": 15,
      "display_frequency": "once_per_day",
      "display_frequency_label": "مرة واحدة يومياً للمراجع",
      "button_text": "احجز العرض الآن",
      "button_link_type": "internal_package",
      "button_link_target": "5",
      "start_at": "2026-07-20 00:00:00",
      "end_at": "2026-07-30 23:59:59",
      "sort_order": 1,
      "is_active": true,
      "is_currently_active": true,
      "views_count": 150,
      "clicks_count": 45,
      "ctr_percentage": 30.0,
      "created_at": "2026-07-19 15:30"
    }
  ]
}
```
**ملاحظة للمبرمج:**
- `duration_seconds`: يمثل عدد الثواني التي يجب أن تظهر فيها الستوري قبل التخطي التلقائي (مثلاً 15 ثانية).
- `display_frequency`: يجب على التطبيق تخزين حالة الظهور محلياً (SharedPreferences / AsyncStorage) بناءً على هذه القيمة (`always`, `once_per_day`, `once_per_session`) لعدم إزعاج المستخدم.
- `button_link_type` و `button_link_target`: يحددان ما يفعله الزر عند النقر (مثلاً توجيه المريض لتفاصيل باقة معينة `internal_package` ورقم الباقة `5`).

---

### 2. تسجيل مشاهدة (Record View)
تُستخدم لإعلام السيرفر بأن المستخدم شاهد هذه الستوري لغرض الإحصائيات. يجب استدعاؤها في الخلفية (Background) عندما تُعرض الستوري.

* **الرابط:** `POST /api/popup-stories/{id}/view`
* **الحماية:** متاح للعامة (Public)
* **جسم الطلب:** فارغ
* **الرد الناجح (200 OK):**
```json
{
  "status": true
}
```

---

### 3. تسجيل نقرة (Record Click)
تُستخدم لإعلام السيرفر بأن المستخدم ضغط على زر الـ Action (مثل "احجز الآن") داخل الستوري لغرض الإحصائيات (حساب الـ CTR). تُستدعى قبل الانتقال للشاشة الهدف.

* **الرابط:** `POST /api/popup-stories/{id}/click`
* **الحماية:** متاح للعامة (Public)
* **جسم الطلب:** فارغ
* **الرد الناجح (200 OK):**
```json
{
  "status": true
}
```
