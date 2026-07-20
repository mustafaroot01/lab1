# أمثلة الاستجابات لجميع واجهات برمجة التطبيقات (All API JSON Responses)

هذا الملف يحتوي على شكل الـ JSON Response (الاستجابة) لكل "عنوان / مسار" (Endpoint) في المشروع لتسهيل عملية الربط على مطور الموبايل.

---

## 📱 أولاً: واجهات المريض (Patient App)

### 1. طلب رمز التحقق (Request OTP)
`POST /api/v1/patient/auth/request-otp`
```json
{
  "status": true,
  "message": "تم إرسال رمز التحقق بنجاح",
  "dev_otp": "1234"
}
```

### 2. التحقق من الرمز (Verify OTP)
`POST /api/v1/patient/auth/verify-otp`
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

### 3. بيانات التهيئة (Onboarding Data)
`GET /api/v1/patient/auth/onboarding-data`
```json
{
  "status": true,
  "message": "تم جلب بيانات التسجيل بنجاح",
  "districts": [
    {
      "id": 1,
      "name": "بعقوبة",
      "governorate": "ديالى",
      "branch_id": 1,
      "branch": {
        "id": 1,
        "name_ar": "الفرع الرئيسي",
        "service_fee": 5000,
        "free_threshold": 25000
    }
  ],
  "terms": {
    "title": "الشروط والأحكام",
    "content": "نص شروط الخدمة..."
  },
  "privacy": {
    "title": "سياسة الخصوصية",
    "content": "نص سياسة الحفاظ على السرية..."
  }
}
```

### 3.1 العناوين المنفصلة للأقضية والمناطق (Get Addresses Separated)
`GET /api/v1/patient/auth/addresses`
يُستخدم في حال كان التطبيق يحتاج لملء قوائم العناوين بدون الحاجة لجلب نصوص الشروط والخصوصية الثقيلة.
```json
{
  "status": true,
  "message": "تم جلب العناوين بنجاح",
  "districts": [
    {
      "id": 1,
      "name": "بعقوبة",
      "governorate": "ديالى",
      "branch_id": 1,
      "branch": {
        "id": 1,
        "name_ar": "الفرع الرئيسي",
        "service_fee": 5000,
        "free_threshold": 25000
    }
  ]
}
```

### 4. إكمال / تعديل الملف الشخصي (Complete / Update Profile)
`POST /api/v1/patient/auth/complete-profile`
`PUT /api/v1/patient/auth/update-profile`
```json
{
  "status": true,
  "message": "تم إكمال/تحديث الملف الشخصي بنجاح",
  "user": {
    "id": 1,
    "name": "أحمد محمد علي",
    "phone": "07700000000",
    "gender": "male",
    "birth_date": "1990-05-15",
    "is_profile_completed": true,
    "district_id": 1,
    "address_text": "بغداد - الكرادة"
  }
}
```

### 5. الملف الشخصي الحالي (Get Profile / Me)
`GET /api/v1/patient/auth/me`
```json
{
  "status": true,
  "user": {
    "id": 1,
    "name": "أحمد محمد علي",
    "phone": "07700000000",
    "gender": "male",
    "birth_date": "1990-05-15",
    "is_profile_completed": true,
    "district_id": 1,
    "area_id": 5,
    "address_text": "بغداد - الكرادة"
  }
}
```

### 6. البنرات (Banners)
`GET /api/banners`
```json
{
  "status": true,
  "message": "تم جلب قائمة البنرات بنجاح",
  "banners": [
    {
      "id": 1,
      "title": "عرض خاص",
      "position": "top_home",
      "image_url": "https://lab.diyala.org/storage/banners/xyz.jpg",
      "link_type": "internal_offer",
      "link_target": "2"
    }
  ]
}
```

### 7. الكتالوج (Catalog)
`GET /api/v1/patient/catalog`
```json
{
  "status": true,
  "message": "تم جلب الكتالوج بنجاح",
  "groups": [
    {
      "id": 1,
      "name": "أمراض الدم",
      "tests": [
        {
          "id": 10,
          "name_en": "CBC",
          "name_ar": "صورة الدم الشاملة",
          "price": 15000,
          "fasting_hours": 0
        }
      ]
    }
  ],
  "packages": [
    {
      "id": 2,
      "name": "الباقة الشاملة",
      "price": 50000,
      "discount_price": 45000
    }
  ]
}
```

### 8. معاينة السلة (Cart Preview)
`POST /api/v1/patient/cart/preview`
```json
{
  "status": true,
  "subtotal": 50000,
  "discount_amount": 5000,
  "home_visit_fee": 5000,
  "total_amount": 50000,
  "items": [
    {
      "id": 1,
      "type": "test",
      "name_ar": "صورة الدم الشاملة",
      "price": 15000
    }
  ],
  "is_free_visit": false
}
```

### 9. التحقق من الكوبون (Validate Coupon)
`POST /api/v1/patient/coupon/validate`
```json
{
  "status": true,
  "message": "تم تطبيق الكوبون بنجاح",
  "discount_amount": 5000,
  "new_total": 45000
}
```

### 10. رفع صورة الروشتة (Upload Referral Image)
`POST /api/v1/patient/orders/upload-referral`
```json
{
  "status": true,
  "message": "تم رفع صورة الروشتة الطبية بنجاح",
  "image_url": "https://lab.diyala.org/storage/orders/referrals/img.png",
  "file_path": "orders/referrals/img.png"
}
```

### 11. إنشاء طلب زيارة منزلية (Create Order)
`POST /api/v1/patient/orders`
```json
{
  "status": true,
  "message": "تم استلام طلبك بنجاح",
  "order": {
    "id": 105,
    "order_number": "ORD-105",
    "status": "pending_confirmation",
    "total_amount": 45000
  }
}
```

### 12. طلباتي (My Orders)
`GET /api/v1/patient/orders`
```json
{
  "status": true,
  "orders": [
    {
      "id": 105,
      "order_number": "ORD-105",
      "status": "completed",
      "status_label": "تمت الموافقة وصدور النتائج",
      "visit_date": "2026-07-20",
      "visit_time": "10:00 AM",
      "total_amount": 45000,
      "items_count": 2
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 1
  }
}
```

### 13. تفاصيل الطلب مع النتائج (Order Details)
`GET /api/v1/patient/orders/{id}`
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

### 14. السجل الطبي (Medical Records)
`GET /api/v1/patient/medical-records`
```json
{
  "status": true,
  "data": {
    "chronic_diseases": [
      { "id": 1, "disease_name": "الضغط", "severity": "medium" }
    ],
    "medications": [],
    "allergies": []
  }
}
```

---

## 🛠️ ثانياً: واجهات الفني الميداني (Technician App)

### 1. تسجيل الدخول (Login)
`POST /api/v1/technician/login`
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
      "status": "active"
    }
  }
}
```

### 2. ملخص الإحصائيات (Me & Stats)
`GET /api/v1/technician/me`
```json
{
  "status": true,
  "message": "تم جلب بيانات الفني بنجاح",
  "data": {
    "technician": {
      "id": 1,
      "name": "يوسف أحمد",
      "phone": "07711111111"
    },
    "summary": {
      "total_orders": 8,
      "in_progress": 3,
      "completed_today": 5
    }
  }
}
```

### 3. طلبات الفني الميداني (My Assigned Orders)
`GET /api/v1/technician/orders?status=active`
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
      "address_text": "بغداد - الكرادة - منزل 15",
      "visit_date": "2026-07-20",
      "visit_time": "10:00 AM",
      "total_amount": 45000,
      "items_count": 2
    }
  ],
  "meta": { "current_page": 1, "total": 1 }
}
```

### 4. تحديث حالة الزيارة (Update Order Status)
`PATCH /api/v1/technician/orders/{id}/status`
```json
{
  "status": true,
  "message": "تم تحديث حالة الزيارة الميدانية بنجاح",
  "data": {
    "order": {
      "id": 105,
      "status": "on_the_way",
      "status_label": "الفني في الطريق للمراجع"
    }
  }
}
```

---

## 🔔 ثالثاً: واجهات الإشعارات العامة (Push Notifications) للمريض والفني

### 1. حفظ / تحديث توكن الجهاز (Device Token)
`POST /api/mobile/device-token` (للمريض)
`POST /api/v1/technician/device-token` (للفني)
```json
{
  "status": true,
  "message": "تم حفظ رمز الجهاز بنجاح لاستقبال الإشعارات"
}
```

### 2. سجل الإشعارات (Notifications History)
`GET /api/mobile/notifications` (للمريض)
`GET /api/v1/technician/notifications` (للفني)
```json
{
  "status": true,
  "message": "تم جلب الإشعارات بنجاح",
  "notifications": [
    {
      "id": "abc-123-xyz",
      "title": "الفني في الطريق!",
      "body": "الفني يوسف أحمد في الطريق إليك لسحب العينة.",
      "type": "order_status_changed",
      "data": {
        "order_id": 105,
        "status": "on_the_way"
      },
      "is_read": false,
      "created_at": "2026-07-20 10:15:00"
    }
  ],
  "meta": { "current_page": 1, "total": 5 }
}
```
