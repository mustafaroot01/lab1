<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BranchAvailabilityController extends Controller
{
    /**
     * جلب الفترات وأوقات العمل المتاحة للحجز (الفرع الرئيسي الوحيد)
     * GET /api/mobile/availability?date=YYYY-MM-DD
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ], [
            'date.required' => 'يرجى إرسال تاريخ الحجز',
            'date.after_or_equal' => 'لا يمكن اختيار تاريخ في الماضي',
        ]);

        $date = Carbon::parse($request->query('date'));
        $dayName = strtolower($date->englishDayOfWeek); // مثلا: saturday

        // قراءة أوقات العمل من إعدادات النظام كـ JSON
        $workingHoursJson = SystemSetting::getValue('working_hours', '[]');
        $workingHours = json_decode($workingHoursJson, true) ?? [];
        
        // البحث عن إعدادات اليوم المطلوب
        $dayConfig = collect($workingHours)->firstWhere('key', $dayName);

        if (!$dayConfig || empty($dayConfig['is_working'])) {
            return response()->json([
                'status' => true,
                'is_working' => false,
                'message' => 'الفرع مغلق في هذا اليوم',
                'shifts' => []
            ]);
        }

        $shifts = $dayConfig['shifts'] ?? [];
        $isToday = $date->isToday();
        $currentTime = Carbon::now();

        $availableShifts = [];

        foreach (['morning', 'noon', 'evening'] as $period) {
            if (!empty($shifts[$period]) && !empty($shifts[$period]['is_active'])) {
                $times = $shifts[$period]['times'] ?? [];
                
                // فلترة الأوقات الماضية إذا كان الحجز لليوم الحالي
                if ($isToday) {
                    $times = array_values(array_filter($times, function($time) use ($currentTime) {
                        return Carbon::createFromFormat('H:i', $time)->isAfter($currentTime);
                    }));
                }

                if (count($times) > 0) {
                    $availableShifts[] = [
                        'period' => $period,
                        'label' => $this->getPeriodLabel($period),
                        'times' => $times,
                    ];
                }
            }
        }

        return response()->json([
            'status' => true,
            'is_working' => true,
            'message' => count($availableShifts) > 0 ? 'تم جلب الأوقات المتاحة' : 'لا توجد أوقات متاحة متبقية لهذا اليوم',
            'shifts' => $availableShifts,
        ]);
    }

    private function getPeriodLabel($period)
    {
        return match ($period) {
            'morning' => 'صباحاً',
            'noon' => 'ظهراً',
            'evening' => 'مساءً',
            default => $period,
        };
    }
}
