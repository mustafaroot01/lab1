<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // 1. WELCOME20 (Percentage, Active, 50 Limit, 3 Usages)
        $c1 = Coupon::firstOrCreate(
            ['code' => 'WELCOME20'],
            [
                'name_ar' => 'خصم ترحيبي للمرضى الجدد 20%',
                'name_en' => 'Welcome New Patients 20% Off',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addMonths(6),
                'usage_limit' => 50,
                'used_count' => 3,
                'is_active' => true,
                'notes' => 'يطبق على أول فحص للمرضى المسجلين حديثاً في المنصة',
            ]
        );

        if ($c1->wasRecentlyCreated) {
            CouponUsage::create([
                'coupon_id' => $c1->id,
                'user_name' => 'أحمد محمد العبيدي',
                'phone' => '07701234567',
                'discount_amount' => 7000,
                'total_before_discount' => 35000,
                'total_after_discount' => 28000,
                'used_at' => Carbon::now()->subDays(5)->setTime(10, 30),
            ]);

            CouponUsage::create([
                'coupon_id' => $c1->id,
                'user_name' => 'فاطمة سعد الخفاجي',
                'phone' => '07809876543',
                'discount_amount' => 12000,
                'total_before_discount' => 60000,
                'total_after_discount' => 48000,
                'used_at' => Carbon::now()->subDays(2)->setTime(14, 15),
            ]);

            CouponUsage::create([
                'coupon_id' => $c1->id,
                'user_name' => 'عمر خالد الدليمي',
                'phone' => '07503332211',
                'discount_amount' => 5000,
                'total_before_discount' => 25000,
                'total_after_discount' => 20000,
                'used_at' => Carbon::now()->subHours(6),
            ]);
        }

        // 2. SUMMER15 (Fixed, Expired Limit reach)
        $c2 = Coupon::firstOrCreate(
            ['code' => 'SUMMER15'],
            [
                'name_ar' => 'خصم الفحوصات الصيفية الشاملة (15,000 د.ع)',
                'name_en' => 'Summer Checkup Fixed Discount',
                'discount_type' => 'fixed',
                'discount_value' => 15000,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(2),
                'usage_limit' => 2,
                'used_count' => 2,
                'is_active' => true,
                'notes' => 'تم استنفاد العدد المسموح بالكامل من هذا الكوبون',
            ]
        );

        if ($c2->wasRecentlyCreated) {
            CouponUsage::create([
                'coupon_id' => $c2->id,
                'user_name' => 'زينب علي التميمي',
                'phone' => '07715554433',
                'discount_amount' => 15000,
                'total_before_discount' => 80000,
                'total_after_discount' => 65000,
                'used_at' => Carbon::now()->subDays(15),
            ]);

            CouponUsage::create([
                'coupon_id' => $c2->id,
                'user_name' => 'حسين كمال الناصري',
                'phone' => '07818889900',
                'discount_amount' => 15000,
                'total_before_discount' => 120000,
                'total_after_discount' => 105000,
                'used_at' => Carbon::now()->subDays(12),
            ]);
        }

        // 3. OLDEXPIRED (Expired Time)
        Coupon::firstOrCreate(
            ['code' => 'HEALTH2025'],
            [
                'name_ar' => 'عرض الفحوصات الدورية المنتهي',
                'name_en' => 'Annual Health Check Expired',
                'discount_type' => 'percentage',
                'discount_value' => 30,
                'start_date' => Carbon::now()->subMonths(4),
                'end_date' => Carbon::now()->subDays(10),
                'usage_limit' => 100,
                'used_count' => 45,
                'is_active' => true,
                'notes' => 'انتهت صلاحية الوقت المحدد لهذا العرض المخبري',
            ]
        );

        // 4. VIP50 (Fixed, Unlimited, Active)
        Coupon::firstOrCreate(
            ['code' => 'VIP50'],
            [
                'name_ar' => 'خصم كبار الشخصيات والشركات المتعاقدة',
                'name_en' => 'VIP & Corporate Accounts Discount',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'start_date' => Carbon::now()->subDays(20),
                'end_date' => null,
                'usage_limit' => null, // Unlimited
                'used_count' => 12,
                'is_active' => true,
                'notes' => 'كوبون مفتوح العدد وبدون تاريخ انتهاء مخصص للعملاء المميزين',
            ]
        );
    }
}
