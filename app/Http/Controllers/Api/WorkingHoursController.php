<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class WorkingHoursController extends Controller
{
    /**
     * Get the configured working hours.
     */
    public function index()
    {
        $workingHoursJson = SystemSetting::getValue('working_hours', '[]');
        $workingHours = json_decode($workingHoursJson, true) ?? [];

        if (empty($workingHours)) {
            $workingHours = $this->getDefaultWorkingHours();
        }

        return response()->json([
            'status' => true,
            'data' => $workingHours,
        ]);
    }

    /**
     * Update the working hours.
     */
    public function update(Request $request)
    {
        $request->validate([
            'working_hours' => 'required|array',
        ]);

        SystemSetting::setValue('working_hours', json_encode($request->working_hours));

        return response()->json([
            'status' => true,
            'message' => 'تم حفظ أوقات العمل بنجاح',
        ]);
    }

    private function getDefaultWorkingHours()
    {
        $days = [
            ['key' => 'saturday', 'name' => 'السبت'],
            ['key' => 'sunday', 'name' => 'الأحد'],
            ['key' => 'monday', 'name' => 'الإثنين'],
            ['key' => 'tuesday', 'name' => 'الثلاثاء'],
            ['key' => 'wednesday', 'name' => 'الأربعاء'],
            ['key' => 'thursday', 'name' => 'الخميس'],
            ['key' => 'friday', 'name' => 'الجمعة'],
        ];

        $defaultShifts = [
            'morning' => ['is_active' => true, 'times' => ['08:00', '09:00', '10:00', '11:00', '12:00']],
            'noon' => ['is_active' => true, 'times' => ['13:00', '14:00', '15:00']],
            'evening' => ['is_active' => true, 'times' => ['16:00', '17:00', '18:00', '19:00', '20:00']],
        ];

        return array_map(function ($day) use ($defaultShifts) {
            return [
                'key' => $day['key'],
                'name' => $day['name'],
                'is_working' => $day['key'] !== 'friday',
                'shifts' => $defaultShifts,
            ];
        }, $days);
    }
}
