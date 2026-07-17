<?php

namespace Database\Seeders;

use App\Models\TestGroup;
use App\Models\MedicalTest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MedicalDictionarySeeder extends Seeder
{
    /**
     */
    public function run(): void
    {
        $filePath = base_path('database/data/قاموس_التحاليل_الكامل.md');
        if (!File::exists($filePath)) {
            $filePath = '/Users/root1/Desktop/lab/قاموس_التحاليل_الكامل.md';
        }

        if (!File::exists($filePath)) {
            $this->command->error("File not found: {$filePath}");
            return;
        }

        $content = File::get($filePath);
        $lines = explode("\n", $content);

        // First pass: extract summary groups from table "ملخص المجموعات"
        // | # | المجموعة (عربي) | المجموعة (إنجليزي) | المفتاح | الأيقونة | اللون | عدد التحاليل |
        $inSummaryTable = false;
        $groupsByKey = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (str_contains($line, '## 📊 ملخص المجموعات')) {
                $inSummaryTable = true;
                continue;
            }
            if ($inSummaryTable && str_starts_with($line, '## ')) {
                $inSummaryTable = false;
            }
            if ($inSummaryTable && str_starts_with($line, '|')) {
                if (str_contains($line, '---|') || str_contains($line, 'المجموعة (عربي)')) {
                    continue;
                }
                $cols = array_map('trim', explode('|', $line));
                // $cols[0] is empty before first |, $cols[1] is #, $cols[2] is ar, $cols[3] is en, $cols[4] is key, $cols[5] is icon, $cols[6] is color, $cols[7] is count
                if (count($cols) >= 8) {
                    $order = (int)$cols[1];
                    $nameAr = $cols[2];
                    $nameEn = $cols[3];
                    $key = trim($cols[4], ' `');
                    $icon = trim($cols[5], ' `');
                    $color = trim($cols[6], ' `');

                    if (!empty($key) && !empty($nameAr)) {
                        $group = TestGroup::updateOrCreate(
                            ['key' => $key],
                            [
                                'name_ar' => $nameAr,
                                'name_en' => $nameEn,
                                'icon' => $icon,
                                'color' => $color,
                                'is_active' => true,
                                'sort_order' => $order > 0 ? $order : 1,
                            ]
                        );
                        $groupsByKey[$key] = $group->id;
                    }
                }
            }
        }

        $this->command->info("Seeded " . count($groupsByKey) . " test groups.");

        // Second pass: extract tests per group
        $currentGroupKey = null;
        $inTestsTable = false;
        $testsCount = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (str_starts_with($line, '### ')) {
                $inTestsTable = false;
                $currentGroupKey = null;
                continue;
            }

            if (str_starts_with($line, '- **المفتاح:**')) {
                // e.g. - **المفتاح:** `blood_contents`
                if (preg_match('/`([^`]+)`/', $line, $matches)) {
                    $currentGroupKey = trim($matches[1]);
                } else {
                    $parts = explode(':', $line);
                    if (isset($parts[1])) {
                        $currentGroupKey = trim(str_replace(['`', '*'], '', $parts[1]));
                    }
                }
                if ($currentGroupKey && isset($groupsByKey[$currentGroupKey])) {
                    $inTestsTable = true;
                }
                continue;
            }

            if ($inTestsTable && $currentGroupKey) {
                if (str_starts_with($line, '|')) {
                    if (str_contains($line, '---|') || str_contains($line, 'اسم التحليل (عربي)')) {
                        continue;
                    }
                    // | # | اسم التحليل (عربي) | اسم التحليل (إنجليزي) | المفتاح | نوع العينة | الأنبوب | صيام | وقت النتيجة | الوصف |
                    if (count($cols = array_map('trim', explode('|', $line))) >= 10) {
                        $order = (int)$cols[1];
                        $nameAr = $cols[2];
                        $nameEn = $cols[3];
                        $key = trim($cols[4], ' `');
                        $sampleType = $cols[5];
                        $tubeType = $cols[6];
                        $fastingStr = $cols[7];
                        $fasting = (str_contains($fastingStr, 'نعم') || str_contains($fastingStr, '✅'));
                        $resultTime = $cols[8];
                        $description = $cols[9];

                        if (!empty($nameAr)) {
                            $testsCount++;
                            $isActive = $testsCount <= 25; // First 25 active and priced for demo/ready inventory
                            $price = $isActive ? (15000 + (($testsCount % 5) * 5000)) : null;
                            $platformPrice = $isActive ? 2000 : null;
                            $totalPrice = $isActive ? ($price + $platformPrice) : null;

                            MedicalTest::updateOrCreate(
                                [
                                    'test_group_id' => $groupsByKey[$currentGroupKey],
                                    'name_ar' => $nameAr,
                                ],
                                [
                                    'name_en' => $nameEn,
                                    'key' => !empty($key) && $key !== '—' ? $key : null,
                                    'sample_type' => $sampleType !== '—' ? $sampleType : null,
                                    'tube_type' => $tubeType !== '—' ? $tubeType : null,
                                    'fasting_required' => $fasting,
                                    'result_time' => $resultTime !== '—' ? $resultTime : null,
                                    'price' => $price,
                                    'platform_price' => $platformPrice,
                                    'total_price' => $totalPrice,
                                    'is_active' => $isActive,
                                    'description' => $description !== '—' ? $description : null,
                                    'sort_order' => $order > 0 ? $order : 1,
                                ]
                            );
                        }
                    } else {
                        if (!empty($line) && !str_starts_with($line, '|')) {
                            $inTestsTable = false;
                        }
                    }
                }
            }
        }

        $this->command->info("Seeded {$testsCount} medical tests across all groups.");
    }
}
