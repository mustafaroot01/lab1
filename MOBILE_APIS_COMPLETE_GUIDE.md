# الدليل المرجعي الشامل لوصلات تطبيق الموبايل (Complete Mobile APIs Guide - V1 & Mobile)

هذا الملف يوثق بشكل كامل وشامل جميع واجهات برمجة التطبيقات (APIs) لتطبيقات الموبايل (المراجعين والمرضى + الفنيين الميدانيين) الخاصة بنظام **Healthy Lab** والمبنية على **Laravel Sanctum Authentication**. 

تم تقسيم التوثيق إلى قسمين مستقلين تماماً لسهولة تسليمه لمطوري الجوال:
1. **واجهات تطبيق المريض / المراجع (Patient Mobile App API)**
2. **واجهات تطبيق الفني الميداني / ساحب العينات (Technician Mobile App API)**

---

## ⚙️ الإعدادات الأساسية والترويسات (Base Configuration & Headers)

* **الرابط الأساسي للسيرفر (Base URL):**
  - المسار الحديث (V1): `https://lab.diyala.org/api/v1`
  - المسار القياسي (Mobile): `https://lab.diyala.org/api/mobile`
  *(كلا المسارين يعملان ويؤديان لنفس الوظائف المعزولة معمارياً).*

* **الترويسات الأساسية الإجبارية لكل الطلبات (Mandatory Headers):**
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
* **في الطلبات المحمية التي تتطلب توكن (Protected Routes):**
  ```http
  Authorization: Bearer {BEARER_TOKEN}
  ```

---
---

# 📱 الجزء الأول: واجهات تطبيق المريض / المراجع (Patient Mobile API)

## 1. المصادقة وإدارة الحساب (Patient Authentication & Onboarding)

### 1.1 طلب وإرسال رمز التحقق (OTP Request)
يستخدم لتسجيل الدخول أو إنشاء حساب جديد برقم الهاتف دون الحاجة لكلمة مرور.
* **الرابط:** `POST /api/v1/patient/auth/request-otp` (أو `POST /api/mobile/auth/request-otp`)
* **الحماية:** متاح للعامة (Public)
* **جسم الطلب (Request Body):**
  ```json
  {
    "phone": "07700000000"
  }
  ```
* **الرد الناجح (200 OK):**
  ```json
  {
    "status": true,
    "message": "تم إرسال رمز التحقق بنجاح",
    "dev_otp": "1234" // يظهر في بيئة التطوير لتسهيل الفحص السريع
  }
  ```

---

### 1.2 التحقق من رمز الـ OTP والحصول على التوكن (Verify OTP)
* **الرابط:** `POST /api/v1/patient/auth/verify-otp` (أو `POST /api/mobile/auth/verify-otp`)
* **الحماية:** متاح للعامة (Public)
* **جسم الطلب (Request Body):**
  ```json
  {
    "phone": "07700000000",
    "otp": "1234"
  }
  ```
* **الرد الناجح (200 OK):**
  ```json
  {
    "status": true,
    "message": "تم التحقق بنجاح",
    "token": "1|abcdef1234567890...",
    "user": {
      "id": 1,
      "phone": "07700000000",
      "name": "أحمد محمد",
      "is_profile_completed": true
    }
  }
  ```
  > **⚠️ تنبيه لمبرمج الموبايل:** إذا كانت قيمة `"is_profile_completed": false`، يجب توجيه المريض فوراً لشاشة إكمال الملف الشخصي (Onboarding Screen) قبل السماح له بطلب أي خدمة أو زيارة منزلية.

---

### 1.3 جلب البيانات الأولية لتهيئة الحساب (Get Onboarding Data)
يجلب قائمة المحافظات والمناطق وألوان التميز لتسهيل تعبئة شاشة التسجيل الأولي للمريض.
* **الرابط:** `GET /api/v1/patient/auth/onboarding-data` (أو `GET /api/mobile/auth/onboarding-data`)
* **الحماية:** متاح للعامة (Public)

---

### 1.4 إكمال الملف الشخصي للمريض الجديد (Complete Profile)
* **الرابط:** `POST /api/v1/patient/auth/complete-profile`
* **الحماية:** يتطلب توكن (`Bearer Token`)
* **جسم الطلب (Request Body):**
  ```json
  {
    "name": "أحمد محمد علي",
    "gender": "male",
    "birth_date": "1990-05-15",
    "district_id": 1,
    "area_id": 5,
    "address_text": "بغداد - الكرادة - شارع 62 - منزل 15"
  }
  ```
* **الرد الناجح (200 OK):**
  ```json
  {
    "status": true,
    "message": "تم إكمال الملف الشخصي بنجاح",
    "user": { ... }
  }
  ```

---

### 1.5 تعديل الملف الشخصي (Update Profile)
* **الرابط:** `PUT /api/v1/patient/auth/update-profile`
* **الحماية:** يتطلب توكن (`Bearer Token`)
* **جسم الطلب (Request Body):** نفس حقول إكمال الملف الشخصي (`name`, `gender`, `birth_date`, `district_id`, `area_id`, `address_text`).

---

### 1.6 جلب بيانات المريض الحالي (Get Profile / Me)
* **الرابط:** `GET /api/v1/patient/auth/me`
* **الحماية:** يتطلب توكن (`Bearer Token`)
* **الرد الناجح (200 OK):** يرجع بيانات المريض كاملة وتاريخ التسجيل والعنوان الافتراضي.

---

### 1.7 تجديد التوكن وتسجيل الخروج وحذف الحساب (Tokens & Deletion)
* **تجديد التوكن:** `POST /api/v1/patient/auth/refresh-token` (`Bearer Token`)
* **تسجيل الخروج وإبطال التوكن الحالي:** `POST /api/v1/patient/auth/logout` (`Bearer Token`)
* **طلب حذف الحساب (إجباري لقبول التطبيق في Apple App Store):**
  `DELETE /api/v1/patient/auth/delete-account` (`Bearer Token`)  
  يقوم بإرسال إشعار لإدارة المختبر لحذف الحساب مع تعطيل الجلسة وإرجاع رسالة:
  *"تم إرسال طلب حذف الحساب بنجاح، سيتم مراجعة طلبك من قبل الإدارة."*

---

## 2. الكتالوج والتحاليل والباقات والعروض (Catalog & Packages)

### 2.1 جلب البنرات الإعلانية (Banners)
* **الرابط:** `GET /api/banners`
* **الحماية:** متاح للعامة (Public)
* **الرد الناجح (200 OK):**
  ```json
  {
    "status": true,
    "message": "تم جلب قائمة البنرات بنجاح",
    "banners": [
      {
        "id": 1,
        "title": "عرض خاص الفحص الشامل",
        "position": "top_home",
        "image_url": "data:image/jpeg;base64,... أو https://...",
        "link_type": "internal_offer",
        "link_target": "2"
      }
    ]
  }
  ```

---

### 2.2 جلب كتالوج التحاليل والمجموعات المخبرية (Catalog)
* **الرابط:** `GET /api/v1/patient/catalog` (أو `GET /api/mobile/catalog`)
* **الحماية:** متاح للعامة (Public)
* **معاملات التصفية والبحث:** `?search=CBC` أو `?group_id=1`
* **الرد الناجح (200 OK):** يرجع قائمة المجموعات (`groups`) مع التحاليل التابعة لها، بالإضافة لقائمة الباقات المميزة (`packages`).

---

### 2.3 البحث السريع في التحاليل والباقات (Search)
* **الرابط:** `GET /api/v1/patient/search?query=فيتامين`
* **الحماية:** متاح للعامة (Public)

---

### 2.4 قائمة الباقات الطبية وتفاصيلها (Packages)
* **قائمة الباقات:** `GET /api/v1/patient/packages`
* **تفاصيل باقة محددة مع تحاليلها المشمولة:** `GET /api/v1/patient/packages/{id}`
* **تفاصيل تحليل محدد وشروط الصيام:** `GET /api/v1/patient/tests/{id}`

---

## 3. السلة، الروشتات المخبرية، وإنشاء الطلبات (Cart & Orders)

### 3.1 معاينة السلة وحساب التكلفة والخصم (Cart Preview)
* **الرابط:** `POST /api/v1/patient/cart/preview`
* **جسم الطلب (Request Body):**
  ```json
  {
    "items": [
      { "type": "test", "id": 1 },
      { "type": "package", "id": 2 }
    ],
    "coupon_code": "HEALTH2026"
  }
  ```
* **الرد الناجح:** يرجع التكلفة الإجمالية (`subtotal`)، الخصم (`discount_amount`)، أجرة الزيارة المنزلية (`home_visit_fee`)، والمبلغ النهائي المطلوب (`total_amount`).

---

### 3.2 التحقق من صلاحية كوبون الخصم (Validate Coupon)
* **الرابط:** `POST /api/v1/patient/coupon/validate`
* **جسم الطلب (Request Body):**
  ```json
  {
    "code": "HEALTH2026",
    "total_amount": 45000
  }
  ```

---

### 3.3 رفع صورة الروشتة أو الإحالة الطبية (Upload Prescription / Referral Image)
يسمح للمريض برفع صورة الروشتة أو وصفة الطبيب ليقوم المختبر بقراءتها وإضافة التحاليل تلقائياً للطلب.
* **الرابط:** `POST /api/v1/patient/orders/upload-referral` (أو `POST /api/mobile/orders/upload-referral`)
* **الحماية:** يتطلب توكن (`Bearer Token`)
* **نوع الطلب (Content-Type):** `multipart/form-data`
* **الحقل المطلوب:** `image` (ملف الصورة بـ JPG أو PNG أو PDF).
* **الرد الناجح (200 OK):**
  ```json
  {
    "status": true,
    "message": "تم رفع صورة الروشتة الطبية بنجاح",
    "image_url": "https://lab.diyala.org/storage/orders/referrals/img_xyz.png",
    "file_path": "orders/referrals/img_xyz.png"
  }
  ```
  *(يمكن للمريض إرفاق رابط الصورة الراجع عند إنشاء الطلب في خطوة إتمام الطلب).*

---

### 3.4 إنشاء طلب زيارة منزلية جديد (Create Order)
* **الرابط:** `POST /api/v1/patient/orders`
* **الحماية:** يتطلب توكن (`Bearer Token`)
* **جسم الطلب (Request Body):**
  ```json
  {
    "service_type": "home_visit",
    "items": [
      { "type": "test", "id": 1 },
      { "type": "package", "id": 2 }
    ],
    "visit_date": "2026-07-20",
    "visit_time": "10:00 AM",
    "address_details": "بغداد - الكرادة - شارع 62 - منزل 15",
    "lat": 33.3152,
    "lng": 44.3661,
    "notes": "يرجى الاتصال قبل الوصول بـ 15 دقيقة",
    "coupon_code": null,
    "referral_image": "orders/referrals/img_xyz.png" // اختيارية إذا تم رفع روشتة
  }
  ```
* **الرد الناجح (201 Created):** يرجع بيانات الطلب ورقم الطلب (`id` و `order_number`).

---

### 3.5 قائمة طلباتي وتفاصيل الطلب وتحميل نتائج التحاليل (My Orders & Results)
* **قائمة طلباتي:** `GET /api/v1/patient/orders` (`Bearer Token`)
* **تفاصيل طلب محدد مع النتائج وسجل التتبع:** `GET /api/v1/patient/orders/{id}` (`Bearer Token`)
  ```json
  {
    "status": true,
    "data": {
      "order": {
        "id": 105,
        "status": "completed",
        "status_label": "تمت الموافقة وصدور النتائج",
        "total_amount": 45000,
        "results": [
          {
            "id": 1,
            "file_name": "نتيجة صورة الدم الشاملة CBC",
            "file_type": "pdf",
            "url": "https://lab.diyala.org/storage/results/order_105_cbc.pdf"
          }
        ],
        "status_logs": [
          {
            "from_status": "pending_confirmation",
            "to_status": "technician_assigned",
            "notes": "تم تعيين الفني الميداني لسحب العينة",
            "created_at": "2026-07-17 11:30:00"
          }
        ]
      }
    }
  }
  ```
* **إلغاء طلب:** `POST /api/v1/patient/orders/{id}/cancel` (`Bearer Token`)

---

## 4. السجل الطبي الشامل للمريض (Medical Records)
تتيح للمريض إدخال الأمراض المزمنة، الأدوية الحالية، والحساسية لمساعدة الطبيب المخبري أثناء قراءة النتيجة المخبرية:
* **عرض السجل الطبي:** `GET /api/v1/patient/medical-records` (`Bearer Token`)
* **إضافة عنصر جديد:** `POST /api/v1/patient/medical-records` (`Bearer Token`)
  ```json
  {
    "type": "chronic_disease", // أو medication أو allergy
    "disease_name": "الضغط والسكر",
    "severity": "medium"
  }
  ```
* **تعديل عنصر:** `PUT /api/v1/patient/medical-records/{type}/{id}`
* **حذف عنصر:** `DELETE /api/v1/patient/medical-records/{type}/{id}`

---

## 5. الدردشة المباشرة مع الدعم الفني للمختبر (Mobile Chat Module)
تتيح للمريض التواصل الفوري مع موظف الاستقبال أو الدعم الفني داخل التطبيق.
* **جلب المحادثة النشطة والرسائل الأخيرة:** `GET /api/mobile/chat/` (`Bearer Token`)
* **تحميل المزيد من الرسائل القديمة (Pagination):** `GET /api/mobile/chat/messages?page=2` (`Bearer Token`)
* **إرسال رسالة جديدة (نصية أو صورة إرفاق):** `POST /api/mobile/chat/send` (`Bearer Token`)
  ```json
  {
    "message": "مرحباً، هل يمكنني معرفة وقت وصول الفني؟",
    "attachment": "data:image/png;base64,..." // أو مسار ملف اختياري
  }
  ```
* **تحديد الرسائل كمقروءة:** `POST /api/mobile/chat/read` (`Bearer Token`)
* **سجل المحادثات السابقة:** `GET /api/mobile/chat/history` (`Bearer Token`)

---
---

# 🛠️ الجزء الثاني: واجهات تطبيق الفني الميداني (Technician Mobile API)

تطبيق ساحب العينات الميداني المصمم خصيصاً للفنيين لتمكينهم من إدارة الزيارات المنزلية المعينة لهم، فتح الخرائط، وتحديث حالة سحب العينة ميدانياً.

## 1. مصادقة الفني الميداني (Technician Auth)

### 1.1 تسجيل دخول الفني (Technician Login)
الدخول يتم عبر رقم الهاتف وكلمة المرور المسندة من إدارة المختبر في لوحة التحكم.
* **الرابط:** `POST /api/v1/technician/login`
* **الحماية:** متاح للعامة (Public)
* **جسم الطلب (Request Body):**
  ```json
  {
    "phone": "07711111111",
    "password": "secretpassword"
  }
  ```
* **الرد الناجح (200 OK):**
  ```json
  {
    "status": true,
    "message": "تم تسجيل دخول الفني بنجاح",
    "data": {
      "token": "2|xyz987654321...",
      "technician": {
        "id": 1,
        "name": "يوسف أحمد (فني ميداني)",
        "phone": "07711111111",
        "branch_id": 1,
        "status": "active"
      }
    }
  }
  ```
* **⚠️ حالات الخطأ الحرجة (Error Codes):**
  - **401 Unauthorized:** كلمة المرور أو رقم الهاتف غير صحيح.
  - **403 Forbidden:** حساب الفني موقوف أو تم تعطيله من المشرف.

---

### 1.2 جلب بيانات الفني وملخص الإحصائيات اليومية (Profile & Daily Stats)
يرجع الملف الشخصي للفني وعدادات فورية لعدد الطلبات المسندة إليه اليوم وتلك في الانتظار أو المنجزة.
* **الرابط:** `GET /api/v1/technician/me`
* **الحماية:** يتطلب توكن الفني (`Bearer Token`)
* **الرد الناجح (200 OK):**
  ```json
  {
    "status": true,
    "message": "تم جلب بيانات الفني بنجاح",
    "data": {
      "technician": {
        "id": 1,
        "name": "يوسف أحمد",
        "phone": "07711111111",
        "status": "active"
      },
      "summary": {
        "total_orders": 8,
        "in_progress": 3,
        "completed_today": 5
      }
    }
  }
  ```

---

### 1.3 تسجيل خروج الفني (Technician Logout)
* **الرابط:** `POST /api/v1/technician/logout`
* **الحماية:** يتطلب توكن الفني (`Bearer Token`)

---

## 2. إدارة الزيارات الميدانية وموقع المريض (Assigned Orders Management)

### 2.1 جلب قائمة الزيارات المسندة للفني (My Assigned Orders)
يعرض حصرياً الطلبات التي تم إسنادها وتعيينها لهذا الفني الميداني ليقوم بزيارتها.
* **الرابط:** `GET /api/v1/technician/orders`
* **الحماية:** يتطلب توكن الفني (`Bearer Token`)
* **معاملات التصفية والفلترة (Query Params):**
  - `?status=active`: يعرض فقط الطلبات النشطة الحالية (`technician_assigned`, `on_the_way`, `in_progress`).
  - `?status=completed`: يعرض الطلبات التي تم الانتهاء من سحب عيناتها (`sample_collected`, `completed`, `results_ready`).
* **الرد الناجح (200 OK):**
  ```json
  {
    "status": true,
    "message": "تم جلب الزيارات المسندة للفني بنجاح",
    "orders": [
      {
        "id": 105,
        "status": "technician_assigned",
        "status_label": "تم تعيين فني لسحب العينة",
        "patient": {
          "id": 12,
          "name": "أحمد محمد علي",
          "phone": "07700000000"
        },
        "branch": { "id": 1, "name_ar": "الفرع الرئيسي" },
        "address_text": "بغداد - الكرادة - شارع 62 - منزل 15",
        "visit_date": "2026-07-20",
        "visit_time": "10:00 AM",
        "total_amount": 45000,
        "items_count": 2
      }
    ],
    "meta": { "current_page": 1, "total": 1 }
  }
  ```

---

### 2.2 جلب تفاصيل الزيارة وإحداثيات المريض للوصول عبر الـ GPS (Order Details)
* **الرابط:** `GET /api/v1/technician/orders/{id}`
* **الحماية:** يتطلب توكن الفني (`Bearer Token`)
* **الرد الناجح:** يرجع كائن `order` كامل يحتوي على `lat` و `lng` للعنوان ليقوم الفني بفتحه في `Google Maps` أو `Apple Maps`، بالإضافة لقائمة التحاليل التي يجب سحب عيناتها وأي ملاحظات كتبها المريض.

---

### 2.3 تحديث حالة الزيارة الميدانية وتتبع موقع الفني الحي (Update Order Status & GPS)
الواجهة الأساسية لعمل الفني؛ يقوم عبرها بتغيير حالة الطلب فور تحركه للمريض، وفور سحب العينة بنجاح، مع إمكانية إرسال إحداثياته الحية.
* **الرابط:** `PATCH /api/v1/technician/orders/{id}/status`
* **الحماية:** يتطلب توكن الفني (`Bearer Token`)
* **جسم الطلب (Request Body):**
  ```json
  {
    "status": "on_the_way", // القيم المسموحة فقط: on_the_way أو sample_collected
    "notes": "الفني تحرك نحو منزل المراجع، المتوقع الوصول خلال 15 دقيقة", // اختياري
    "lat": 33.3152, // إحداثيات الفني الحية عند التحديث (اختيارية)
    "lng": 44.3661
  }
  ```
* **توضيح دقيق للحالات المسموحة للفني (`status`):**
  1. `"on_the_way"`: يضغط الفني على زر **"في الطريق للمريض"** عند تحركه من المختبر أو من الزيارة السابقة. النظام يحول حالة الطلب فوراً إلى *الفني في الطريق* ويشاهد المريض ذلك على شاشته.
  2. `"sample_collected"`: يضغط الفني على زر **"تم سحب العينة"** بمجرد سحب الدم والعينة من المريض بنجاح ووضع الـ Barcode. النظام يحول الطلب إلى *تم سحب العينة ميدانياً* ليتم استقباله في المختبر للتحليل.

* **الرد الناجح (200 OK):**
  ```json
  {
    "status": true,
    "message": "تم تحديث حالة الزيارة الميدانية بنجاح",
    "data": {
      "order": {
        "id": 105,
        "status": "on_the_way",
        "status_label": "الفني في الطريق للمراجع",
        "...": "..."
      }
    }
  }
  ```

---
---

## 💡 نصائح وحلول للمشاكل الشائعة في تطوير الموبايل (Best Practices & Gotchas)

1. **دعم التنسيقات العربية والخطوط:**
   - تأكد من استخدام الخط المعتمد (Cairo أو Tajawal) وتنسيق الأرقام والأسعار بوضوح (مثلاً: `25,000 د.ع`).
2. **التعامل مع انقطاع الإنترنت الميداني للفني:**
   - يفضل حفظ المهام المسندة للفني في قاعدة بيانات محلية (`SQLite` / `Hive`) بحيث يتمكن من قراءة العنوان وتفاصيل المريض حتى لو ضعف إرسال الإنترنت في بعض المناطق.
3. **أكواد الاستجابة السريعة (HTTP Status Codes Checklist):**
   - `200 OK / 201 Created`: العملية تمت بنجاح تام.
   - `422 Unprocessable Entity`: خطأ في المدخلات؛ اعرض مصفوفة `errors` الراجعة من السيرفر تحت الحقول مباشرة.
   - `401 Unauthorized`: التوكن انتهى أو تم تغييره؛ وجه المستخدم فوراً لشاشة الدخول (`Login Screen`).
