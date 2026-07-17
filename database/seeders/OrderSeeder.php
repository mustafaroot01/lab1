<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // أولاً: نضيف 5 مرضى جدد بأسماء عراقية
        $patients = [
            ['name' => 'علي حسين كاظم',     'phone' => '07701234501', 'gender' => 'male',   'birth_date' => '1990-03-15'],
            ['name' => 'فاطمة جاسم محمد',   'phone' => '07801234502', 'gender' => 'female', 'birth_date' => '1985-07-22'],
            ['name' => 'حسن عادل سعيد',     'phone' => '07901234503', 'gender' => 'male',   'birth_date' => '1998-11-10'],
            ['name' => 'زينب عبدالله نوري',  'phone' => '07501234504', 'gender' => 'female', 'birth_date' => '2000-01-05'],
            ['name' => 'مصطفى كريم جواد',   'phone' => '07701234505', 'gender' => 'male',   'birth_date' => '1975-09-28'],
        ];

        $userIds = [];
        $patientIds = [];
        foreach ($patients as $p) {
            $user = User::firstOrCreate(
                ['phone' => $p['phone']],
                [
                    'name'                 => $p['name'],
                    'gender'               => $p['gender'],
                    'birth_date'           => $p['birth_date'],
                    'is_profile_completed' => true,
                    'is_active'            => true,
                    'password'             => bcrypt('123456'),
                ]
            );
            $userIds[] = $user->id;

            $patient = \App\Models\Patient::firstOrCreate(
                ['phone' => $p['phone']],
                [
                    'name'                 => $p['name'],
                    'gender'               => $p['gender'],
                    'birth_date'           => $p['birth_date'],
                    'is_profile_completed' => true,
                    'is_active'            => true,
                ]
            );
            $patientIds[] = $patient->id;
        }

        // أضف المستخدمين والمرضى الموجودين (2, 3)
        $userIds[] = 2;
        $userIds[] = 3;
        $patientIds[] = 2;
        $patientIds[] = 3;

        // ─── 7 طلبات بحالات مختلفة ───────────────────
        $today = Carbon::today();
        $orders = [
            // 1 — قيد الانتظار (اليوم)
            [
                'user_id'      => $userIds[0],
                'patient_id'   => $patientIds[0],
                'status'       => 'pending',
                'visit_date'   => $today->toDateString(),
                'visit_time'   => '09:00',
                'visit_period' => 'morning',
                'lat'          => 33.7455,
                'lng'          => 44.6493,
                'address_text' => 'بعقوبة - حي المعلمين - قرب جامع الرحمة',
                'doctor_name'  => 'د. سامر العبيدي',
                'notes'        => 'أفضل الاتصال قبل الوصول بنصف ساعة',
                'items'        => [
                    ['item_type' => 'test', 'item_id' => 1, 'name_ar' => 'صورة الدم الكاملة CBC', 'price' => 22000],
                    ['item_type' => 'test', 'item_id' => 5, 'name_ar' => 'D-Dimer', 'price' => 17000],
                ],
            ],
            // 2 — مؤكد (غداً)
            [
                'user_id'      => $userIds[1],
                'patient_id'   => $patientIds[1],
                'status'       => 'confirmed',
                'visit_date'   => $today->copy()->addDay()->toDateString(),
                'visit_time'   => '10:00',
                'visit_period' => 'morning',
                'lat'          => 33.7520,
                'lng'          => 44.6412,
                'address_text' => 'بعقوبة - شارع التحرير - مقابل صيدلية النور',
                'items'        => [
                    ['item_type' => 'test', 'item_id' => 2, 'name_ar' => 'PT', 'price' => 27000],
                    ['item_type' => 'test', 'item_id' => 3, 'name_ar' => 'INR', 'price' => 32000],
                    ['item_type' => 'test', 'item_id' => 4, 'name_ar' => 'PTT', 'price' => 37000],
                ],
            ],
            // 3 — الفني في الطريق (اليوم)
            [
                'user_id'       => $userIds[2],
                'patient_id'    => $patientIds[2],
                'status'        => 'on_the_way',
                'technician_id' => 1,
                'visit_date'    => $today->toDateString(),
                'visit_time'    => '14:00',
                'visit_period'  => 'noon',
                'address_text'  => 'بعقوبة - حي الضباط - زقاق 5',
                'doctor_name'   => 'د. هدى الراوي',
                'items'         => [
                    ['item_type' => 'test', 'item_id' => 6, 'name_ar' => 'الفيبرينوجين Fibrinogen', 'price' => 22000],
                    ['item_type' => 'test', 'item_id' => 7, 'name_ar' => 'لطاخة الدم (blood film)', 'price' => 27000],
                    ['item_type' => 'test', 'item_id' => 8, 'name_ar' => 'فصيلة الدم ABO', 'price' => 32000],
                ],
            ],
            // 4 — تم سحب العينة
            [
                'user_id'       => $userIds[3],
                'patient_id'    => $patientIds[3],
                'status'        => 'sample_collected',
                'technician_id' => 1,
                'visit_date'    => $today->copy()->subDay()->toDateString(),
                'visit_time'    => '08:00',
                'visit_period'  => 'morning',
                'address_text'  => 'بعقوبة - حي التأميم - شارع المستشفى',
                'notes'         => 'المريضة حامل بالشهر الخامس',
                'items'         => [
                    ['item_type' => 'test', 'item_id' => 1, 'name_ar' => 'صورة الدم الكاملة CBC', 'price' => 22000],
                    ['item_type' => 'test', 'item_id' => 9, 'name_ar' => 'Transferrin TRF', 'price' => 37000],
                    ['item_type' => 'test', 'item_id' => 10, 'name_ar' => 'TSAT', 'price' => 17000],
                    ['item_type' => 'test', 'item_id' => 14, 'name_ar' => 'Protein C', 'price' => 37000],
                ],
            ],
            // 5 — النتيجة جاهزة
            [
                'user_id'       => $userIds[4],
                'patient_id'    => $patientIds[4],
                'status'        => 'results_ready',
                'technician_id' => 1,
                'visit_date'    => $today->copy()->subDays(2)->toDateString(),
                'visit_time'    => '17:00',
                'visit_period'  => 'evening',
                'address_text'  => 'بعقوبة - شارع 60 - قرب مطعم أبو سمير',
                'doctor_name'   => 'د. عمار الجبوري',
                'items'         => [
                    ['item_type' => 'test', 'item_id' => 11, 'name_ar' => 'مانع التخثر الذئبي Lupus', 'price' => 22000],
                    ['item_type' => 'test', 'item_id' => 12, 'name_ar' => 'PT,PTT,INR', 'price' => 27000],
                ],
            ],
            // 6 — تم التسليم
            [
                'user_id'       => $userIds[5],
                'patient_id'    => $patientIds[5],
                'status'        => 'delivered',
                'technician_id' => 1,
                'visit_date'    => $today->copy()->subDays(3)->toDateString(),
                'visit_time'    => '11:00',
                'visit_period'  => 'morning',
                'lat'           => 33.7410,
                'lng'           => 44.6530,
                'address_text'  => 'بعقوبة - السوق الكبير - فوق صيدلية الحياة',
                'items'         => [
                    ['item_type' => 'test', 'item_id' => 13, 'name_ar' => 'Blood Osmolality', 'price' => 32000],
                    ['item_type' => 'test', 'item_id' => 15, 'name_ar' => 'Protein S', 'price' => 17000],
                ],
            ],
            // 7 — ملغي
            [
                'user_id'       => $userIds[6],
                'patient_id'    => $patientIds[6],
                'status'        => 'cancelled',
                'visit_date'    => $today->copy()->subDay()->toDateString(),
                'visit_time'    => '20:00',
                'visit_period'  => 'evening',
                'address_text'  => 'بعقوبة - حي الجامعة',
                'cancel_reason' => 'تغير موعد الطبيب وسأطلب مجدداً لاحقاً',
                'items'         => [
                    ['item_type' => 'test', 'item_id' => 1, 'name_ar' => 'صورة الدم الكاملة CBC', 'price' => 22000],
                ],
            ],
        ];

        foreach ($orders as $data) {
            $items = $data['items'];
            unset($data['items']);

            // حساب الأسعار
            $subtotal = collect($items)->sum('price');
            $data['subtotal']        = $subtotal;
            $data['service_fee']     = 5000;
            $data['discount_amount'] = 0;
            $data['total']           = $subtotal + 5000;
            $data['branch_id']       = 1;

            $order = Order::create($data);

            foreach ($items as $item) {
                OrderItem::create(array_merge(['order_id' => $order->id], $item));
            }
        }
    }
}
