<?php

namespace Database\Seeders;

use App\Models\SampleType;
use App\Models\TubeType;
use App\Models\MedicalTest;
use Illuminate\Database\Seeder;

class SampleAndTubeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $samples = [
            [
                'name_ar' => 'دم',
                'name_en' => 'Blood / Serum / Plasma',
                'code' => 'BLD',
                'icon' => 'tabler-droplet-filled',
                'color' => 'error',
                'description' => 'عينة دم وريدي لسحب الفحوصات الكيميائية، الدموية، والمناعية أو الهرمونات',
                'sort_order' => 1,
            ],
            [
                'name_ar' => 'بول',
                'name_en' => 'Urine Sample',
                'code' => 'URN',
                'icon' => 'tabler-flask',
                'color' => 'warning',
                'description' => 'عينة بول للتحاليل العامة، وظائف الكلى، أو المزرعة الميكروبية',
                'sort_order' => 2,
            ],
            [
                'name_ar' => 'ادرار',
                'name_en' => 'Urine / Midstream',
                'code' => 'URN_M',
                'icon' => 'tabler-flask-2',
                'color' => 'warning',
                'description' => 'عينة إدرار (بول الصباح أو وسط المجرى) لفحوصات البروتين والزلال الدقيق',
                'sort_order' => 3,
            ],
            [
                'name_ar' => 'مسحة',
                'name_en' => 'Swab / Smear',
                'code' => 'SWB',
                'icon' => 'tabler-test-pipe-2',
                'color' => 'info',
                'description' => 'مسحة للزرع البكتيري أو الفيروسي من الحلق، الأنف، أو الجروح',
                'sort_order' => 4,
            ],
            [
                'name_ar' => 'براز',
                'name_en' => 'Stool Sample',
                'code' => 'STL',
                'icon' => 'tabler-biohazard',
                'color' => 'secondary',
                'description' => 'عينة براز لفحص الطفيليات، الدم الخفي، والمزارع البكتيرية',
                'sort_order' => 5,
            ],
            [
                'name_ar' => 'سائل شوكي',
                'name_en' => 'Cerebrospinal Fluid (CSF)',
                'code' => 'CSF',
                'icon' => 'tabler-dna',
                'color' => 'primary',
                'description' => 'عينة السائل النخاعي الشوكي للفحوصات العصبية والدقيقة الخاصة بالمستشفيات',
                'sort_order' => 6,
            ]
        ];

        foreach ($samples as $sample) {
            SampleType::updateOrCreate(
                ['name_ar' => $sample['name_ar']],
                $sample
            );
        }

        // Also ensure any distinct sample_type in medical_tests exists
        $distinctSamples = MedicalTest::whereNotNull('sample_type')->distinct()->pluck('sample_type');
        $sort = 10;
        foreach ($distinctSamples as $ds) {
            if (!SampleType::where('name_ar', $ds)->exists()) {
                SampleType::create([
                    'name_ar' => $ds,
                    'name_en' => $ds,
                    'code' => mb_strtoupper(mb_substr($ds, 0, 5, 'UTF-8'), 'UTF-8'),
                    'icon' => 'tabler-test-pipe',
                    'color' => 'primary',
                    'description' => 'نوع عينة مستخرج تلقائياً من القاموس المخبري',
                    'sort_order' => $sort++,
                ]);
            }
        }

        $tubes = [
            [
                'name_ar' => 'أنبوب EDTA البنفسجي',
                'name_en' => 'EDTA Lavender Top Tube',
                'code' => 'EDTA-PURPLE',
                'cap_color' => 'بنفسجي (Lavender)',
                'color_hex' => '#8e24aa',
                'additive' => 'مضاد التخثر K2/K3 EDTA',
                'icon' => 'tabler-color-swatch',
                'description' => 'يستخدم لفحوصات صورة الدم الكاملة CBC وفحوصات الهيموجلوبين التراكمي HbA1c والمناعة',
                'sort_order' => 1,
            ],
            [
                'name_ar' => 'sst_yellow',
                'name_en' => 'SST Yellow Top Tube (Gel Separator)',
                'code' => 'SST-YELLOW',
                'cap_color' => 'أصفر (Yellow / Gold)',
                'color_hex' => '#fbc02d',
                'additive' => 'جل فاصل ومسرع للتجلط (Clot Activator)',
                'icon' => 'tabler-color-swatch',
                'description' => 'الأنبوب الأكثر استخداماً لفحوصات الكيمياء، وظائف الكبد والكلى، الدهون، الهرمونات والفيتامينات',
                'sort_order' => 2,
            ],
            [
                'name_ar' => 'أنبوب Citrate الأزرق',
                'name_en' => 'Sodium Citrate Blue Top Tube',
                'code' => 'CITRATE-BLUE',
                'cap_color' => 'أزرق فاتح (Light Blue)',
                'color_hex' => '#1976d2',
                'additive' => 'سترات الصوديوم (3.2% Sodium Citrate)',
                'icon' => 'tabler-color-swatch',
                'description' => 'مخصص حصرياً لفحوصات تخثر الدم وسيولته مثل PT, PTT, INR, D-Dimer',
                'sort_order' => 3,
            ],
            [
                'name_ar' => 'URINE_CUP',
                'name_en' => 'Sterile Urine Container',
                'code' => 'URINE-CUP',
                'cap_color' => 'أصفر/شفاف (Sterile Cup)',
                'color_hex' => '#ffb300',
                'additive' => 'بدون مواد مضافة (أو حمض البوريك للمزارع)',
                'icon' => 'tabler-cup',
                'description' => 'عبوة معقمة لتجميع عينات البول والإدرار العام أو الزرع البكتيري الميكروبي',
                'sort_order' => 4,
            ],
            [
                'name_ar' => 'أنبوب أحمر عادى',
                'name_en' => 'Plain Red Top Tube',
                'code' => 'PLAIN-RED',
                'cap_color' => 'أحمر (Red Top)',
                'color_hex' => '#d32f2f',
                'additive' => 'بدون مواد مانعة للتخثر (أو Clot Activator فقط)',
                'icon' => 'tabler-color-swatch',
                'description' => 'للحصول على مصل الدم (Serum) النقي لبعض الفحوصات المناعية ومصل الأجسام المضادة',
                'sort_order' => 5,
            ],
            [
                'name_ar' => 'سواب (Swab)',
                'name_en' => 'Sterile Transport Swab',
                'code' => 'SWAB-TUBE',
                'cap_color' => 'أبيض / أزرق (Swab Tube)',
                'color_hex' => '#0288d1',
                'additive' => 'وسط نقل مغذي (Stuart / Amies Medium)',
                'icon' => 'tabler-vaccine',
                'description' => 'أنبوب يحتوي على مسحة قطنية معقمة ووسط نقل حيوي للحفاظ على البكتيريا والفيروسات لحين الفحص',
                'sort_order' => 6,
            ]
        ];

        foreach ($tubes as $tube) {
            TubeType::updateOrCreate(
                ['name_ar' => $tube['name_ar']],
                $tube
            );
        }

        // Also ensure any distinct tube_type in medical_tests exists
        $distinctTubes = MedicalTest::whereNotNull('tube_type')->distinct()->pluck('tube_type');
        $sort = 10;
        foreach ($distinctTubes as $dt) {
            if (!TubeType::where('name_ar', $dt)->exists()) {
                TubeType::create([
                    'name_ar' => $dt,
                    'name_en' => $dt,
                    'code' => mb_strtoupper(mb_substr($dt, 0, 6, 'UTF-8'), 'UTF-8'),
                    'cap_color' => 'قياسي',
                    'color_hex' => '#607d8b',
                    'additive' => 'حسب نوع الأنبوب',
                    'icon' => 'tabler-color-swatch',
                    'description' => 'نوع أنبوب مستخرج تلقائياً من القاموس المخبري',
                    'sort_order' => $sort++,
                ]);
            }
        }

        // Link all existing tests to their foreign IDs
        $samplesMap = SampleType::pluck('id', 'name_ar');
        $tubesMap = TubeType::pluck('id', 'name_ar');

        foreach (MedicalTest::all() as $test) {
            $sampleId = $samplesMap[$test->sample_type] ?? null;
            $tubeId = $tubesMap[$test->tube_type] ?? null;

            if ($sampleId || $tubeId) {
                $test->update([
                    'sample_type_id' => $sampleId,
                    'tube_type_id' => $tubeId,
                ]);
            }
        }
    }
}
