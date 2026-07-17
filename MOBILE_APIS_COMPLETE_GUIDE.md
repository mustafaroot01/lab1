# الدليل المرجعي الشامل لوصلات تطبيق الموبايل (Complete Mobile APIs Guide - V1)

هذا الملف يوثق جميع واجهات برمجة التطبيقات (APIs) الخاصة بتطبيقات الموبايل للمشروع بنظام **Sanctum Authentication** وبأحدث إصدار **V1**، مقسمة إلى قسمين رئيسيين منفصلين تماماً:
1. **واجهات المراجع / المريض (Patient Mobile App API)**
2. **واجهات الفني الميداني / ساحب العينات (Technician Mobile App API)**

---

## ⚙️ الإعدادات العامة والترويسات (Base Configuration & Headers)

* **الرابط الأساسي للسيرفر (Base URL):**
  `https://lab.diyala.org/api/v1`
* **الترويسات الأساسية الإجبارية لكل الطلبات (Headers):**
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
* **في الطلبات المحمية التي تتطلب تسجيل دخول (Protected Routes):**
  ```http
  Authorization: Bearer {BEARER_TOKEN}
  ```

---
---

# 📱 الجزء الأول: واجهات تطبيق المريض / المراجع (Patient Mobile API)

## 1. المصادقة وإدارة الحساب (Patient Authentication)

### 1.1 طلب وإرسال رمز التحقق (OTP Request)
يستخدم لتسجيل الدخول أو إنشاء حساب جديد برقم الهاتف دون الحاجة لكلمة مرور.
* **الرابط:** `POST /api/v1/patient/auth/request-otp` (أو `send-otp`)
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
    "dev_otp": "1234" // يظهر في بيئة التطوير لتسهيل الفحص
  }
  ```

---

### 1.2 التحقق من رمز الـ OTP والحصول على التوكن (Verify OTP)
* **الرابط:** `POST /api/v1/patient/auth/verify-otp`
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
    "token": "1|abcdef123456789...",
    "user": {
      "id": 1,
      "phone": "07700000000",
      "name": "أحمد محمد",
      "is_profile_completed": true
    }
  }
  ```
  > **⚠️ تنبيه لمبرمج الموبايل:** إذا كانت قيمة `"is_profile_completed": false`، يجب توجيه المريض فوراً لشاشة إكمال الملف الشخصي قبل السماح له بطلب زيارة منزلية.

---

### 1.3 إكمال الملف الشخصي للمريض الجديد (Complete Profile)
* **الرابط:** `POST /api/v1/patient/auth/complete-profile`
* **الحماية:** يتطلب توكن (`Bearer Token`)
* **جسم الطلب (Request Body):**
  ```json
  {
    "name": "أحمد محمد علي",
    "gender": "male",
    "birth_date": "1990-05-15",
    "district_id": 1,
    "area_id": 5
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

### 1.4 تعديل الملف الشخصي (Update Profile)
* **الرابط:** `PUT /api/v1/patient/auth/update-profile`
* **الحماية:** يتطلب توكن (`Bearer Token`)
* **جسم الطلب (Request Body):**
  ```json
  {
    "name": "أحمد محمد علي",
    "gender": "male",
    "birth_date": "1990-05-15",
    "address_text": "بغداد - الكرادة - شارع 62"
  }
  ```

---

### 1.5 جلب بيانات المريض الحالي (Get Profile / Me)
* **الرابط:** `GET /api/v1/patient/auth/me`
* **الحماية:** يتطلب توكن (`Bearer Token`)
* **الرد الناجح (200 OK):** يرجع بيانات المريض كاملة مع العنوان ورقم الهاتف.

---

### 1.6 تسجيل الخروج وطلب حذف الحساب (Logout & Delete Account)
* **تسجيل الخروج وإبطال التوكن الحالي:**
  `POST /api/v1/patient/auth/logout` (`Bearer Token`)
* **طلب حذف الحساب (مطلوب لمتجر Apple Store):**
  `DELETE /api/v1/patient/auth/delete-account` (`Bearer Token`)

---

## 2. الكتالوج والتحاليل والباقات والعروض (Catalog & Packages)

### 2.1 جلب البنرات الإعلانية (Banners)
* **الرابط:** `GET /api/banners`
* **الحماية:** متاح للعامة (Public)
* **الرد الناجح (200 OK):** يرجع قائمة البنرات النشطة مع رابط الصورة `image_url` سواء كان `http` أو `data:image/...`.

---

### 2.2 جلب كتالوج التحاليل والمجموعات المخبرية (Catalog)
* **الرابط:** `GET /api/v1/patient/catalog?search=`
* **الحماية:** متاح للعامة (Public)
* **الرد الناجح (200 OK):** يرجع المجموعات (`groups`) والتحاليل (`tests`) التابعة لكل مجموعة مع الأسعار ووقت ظهور النتيجة وشروط الصيام.

---

### 2.3 البحث السريع في التحاليل والباقات (Search)
* **الرابط:** `GET /api/v1/patient/search?query=CBC`
* **الحماية:** متاح للعامة (Public)

---

### 2.4 قائمة الباقات الطبية وتفاصيلها (Packages)
* **قائمة الباقات:** `GET /api/v1/patient/packages`
* **تفاصيل باقة محددة مع تحاليلها:** `GET /api/v1/patient/packages/{id}`
* **تفاصيل تحليل محدد:** `GET /api/v1/patient/tests/{id}`

---

## 3. السلة والكوبونات وطلب الزيارات (Cart & Orders)

### 3.1 معاينة السلة وحساب التكلفة والخصم (Cart Preview)
* **الرابط:** `POST /api/v1/patient/cart/preview`
* **الحماية:** متاح للعامة أو بتوكن
* **جسم الطلب (Request Body):**
  ```json
  {
    "items": [
      { "type": "test", "id": 1 },
      { "type": "package", "id": 2 }
    ],
    "coupon_code": "HEALTH2026" // اختياري
  }
  ```
* **الرد الناجح:** يرجع إجمالي السعر، السعر بعد الخصم، وأجرة الزيارة المنزلية.

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

### 3.3 إنشاء طلب زيارة منزلية جديد (Create Order)
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
    "notes": "يرجى الاتصال قبل الوصول بـ 15 دقيقة",
    "coupon_code": null
  }
  ```
* **الرد الناجح (201 Created):**
  ```json
  {
    "status": true,
    "message": "تم إنشاء طلب الزيارة المنزلية بنجاح",
    "order": {
      "id": 105,
      "status": "pending_confirmation",
      "total_amount": 45000,
      "...": "..."
    }
  }
  ```

---

### 3.4 قائمة طلباتي وتفاصيل الطلب وتحميل النتائج (My Orders)
* **جلب جميع طلباتي السابقة والحالية:**
  `GET /api/v1/patient/orders` (`Bearer Token`)
* **جلب تفاصيل طلب محدد مع النتائج المخبرية وسجل الحالات:**
  `GET /api/v1/patient/orders/{id}` (`Bearer Token`)
  > **ملاحظة:** يرجع داخل كائن الطلب مصفوفة `results` تحتوي على روابط ملفات النتيجة المخبرية (PDF أو صور)، ومصفوفة `status_logs` لتتبع مراحل الطلب.
* **إلغاء طلب:**
  `POST /api/v1/patient/orders/{id}/cancel` (`Bearer Token`)

---

## 4. السجل الطبي الشامل للمريض (Medical Records)
تتيح للمريض إدخال الأمراض المزمنة، الأدوية، والحساسية لمساعدة الطبيب المخبري أثناء قراءة النتيجة:
* **عرض السجل الطبي:** `GET /api/v1/patient/medical-records` (`Bearer Token`)
* **إضافة سجل طبي جديد:** `POST /api/v1/patient/medical-records` (`Bearer Token`)
  ```json
  {
    "type": "chronic_disease", // أو medication أو allergy
    "disease_name": "الضغط",
    "severity": "medium"
  }
  ```
* **تعديل سجل طبي:** `PUT /api/v1/patient/medical-records/{type}/{id}`
* **حذف سجل طبي:** `DELETE /api/v1/patient/medical-records/{type}/{id}`

---
---

# 🛠️ الجزء الثاني: واجهات تطبيق الفني الميداني (Technician Mobile API)

هذا التطبيق مخصص للفنيين الميدانيين الذين يتوجهون لمنازل المرضى لسحب العينات.

## 1. مصادقة الفني الميداني (Technician Auth)

### 1.1 تسجيل دخول الفني (Technician Login)
الدخول يتم باستخدام رقم الهاتف المعتمد للفني وكلمة المرور التي تم إنشاؤها له من قبل الإدارة.
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
        "status": "active",
        "image_url": "..."
      }
    }
  }
  ```
* **⚠️ حالات الخطأ (Errors):**
  - **401 Unauthorized:** كلمة المرور أو رقم الهاتف غير صحيح.
  - **403 Forbidden:** حساب الفني موقوف أو غير نشط من قبل المشرف.

---

### 1.2 جلب بيانات الفني مع إحصائيات اليوم (Technician Profile & Stats)
يرجع الملف الشخصي للفني مع ملخص سريع لعدد الزيارات المسندة إليه اليوم والمنجزة.
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

## 2. إدارة الزيارات الميدانية والمهام (Assigned Orders Management)

### 2.1 جلب قائمة الزيارات المسندة للفني (My Assigned Orders)
يعرض فقط الطلبات التي قام مدير المختبر أو المشرف بتعيينها لهذا الفني الميداني تحديداً.
* **الرابط:** `GET /api/v1/technician/orders`
* **الحماية:** يتطلب توكن الفني (`Bearer Token`)
* **معاملات التصفية الاختيارية (Query Params):**
  - `?status=active`: لجلب الطلبات الحالية النشطة (`technician_assigned`, `on_the_way`, `in_progress`).
  - `?status=completed`: لجلب الطلبات المنجزة (`sample_collected`, `completed`, `results_ready`).
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

### 2.2 جلب تفاصيل الزيارة وموقع المراجع GPS (Order Details)
يعرض جميع بيانات الزيارة والمراجع والتحاليل المطلوبة والموقع الجغرافي الدقيق للوصول إليه عبر خرائط جوجل.
* **الرابط:** `GET /api/v1/technician/orders/{id}`
* **الحماية:** يتطلب توكن الفني (`Bearer Token`)
* **الرد الناجح (200 OK):** يرجع كائن `order` كامل التفاصيل بما في ذلك الإحداثيات (`lat`, `lng`) والملاحظات.

---

### 2.3 تحديث حالة الزيارة الميدانية وتحديث إحداثيات الفني (Update Order Status & GPS)
هذه هي الواجهة الأهم في تطبيق الفني؛ حيث يقوم من خلالها بتغيير حالة الطلب بمجرد تحركه للمريض، وبمجرد سحب العينة بنجاح، مع إمكانية إرسال إحداثيات موقعه الحالي لتتبع الزيارة.
* **الرابط:** `PATCH /api/v1/technician/orders/{id}/status`
* **الحماية:** يتطلب توكن الفني (`Bearer Token`)
* **جسم الطلب (Request Body):**
  ```json
  {
    "status": "on_the_way", // القيم المسموحة: on_the_way أو sample_collected
    "notes": "الفني تحرك نحو منزل المراجع وهو على بعد 10 دقائق", // اختياري
    "lat": 33.3152, // إحداثيات الفني الحية (اختياري)
    "lng": 44.3661
  }
  ```
* **توضيح الحالات المسموح للفني إرسالها (`status`):**
  1. `"on_the_way"`: يرسلها الفني عند تحركه من المختبر باتجاه منزل المراجع (تتحول حالة الطلب في النظام إلى: *الفني في الطريق* ويعرف المريض ذلك في تتبعه).
  2. `"sample_collected"`: يرسلها الفني فور سحب الدم والعينة بنجاح من المراجع وتجهيزها للنقل للمختبر (تتحول حالة الطلب في النظام إلى: *تم سحب العينة ميدانياً* ويتم تسجيل الحركة في الـ Status Logs تلقائياً).

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

## 💡 ملاحظات وإرشادات مهمة لمطور الموبايل (Best Practices)

1. **التعامل مع رموز الأخطاء (HTTP Status Codes):**
   - `200 / 201`: العملية نجحت.
   - `400 / 422`: خطأ في المدخلات (Validation Error)، يجب عرض مصفوفة `errors` الراجعة من السيرفر تحت الحقول المعنية.
   - `401 Unauthorized`: التوكن منتهي أو غير صالح؛ يجب توجيه المستخدم فوراً لشاشة تسجيل الدخول (`Login Screen`).
   - `403 Forbidden`: ليس لديك صلاحية أو الحساب موقوف.
   - `404 Not Found`: الطلب أو العنصر غير موجود.

2. **الصور والملفات (Base64 & Storage URL):**
   - جميع الصور الراجعة في النظام (البنرات، صور التحاليل، الباقات، الفنيين) تأتي في حقل `image_url` أو `image` كرابط جاهز للعرض مباشرة (سواء كان رابط ويب `https://...` أو سلسلة `data:image/...`). يمكنك وضع الحقل مباشرة في مكون الصورة في `Flutter / React Native`.

3. **حفظ التوكن في الهاتف (Secure Storage):**
   - لا تقم بحفظ التوكن في الـ `SharedPreferences` العادية بل استخدم `FlutterSecureStorage` في فلاتر أو `KeyChain` في iOS لحماية بيانات الدخول.
