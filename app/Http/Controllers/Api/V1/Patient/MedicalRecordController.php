<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Resources\AllergyResource;
use App\Http\Resources\ChronicDiseaseResource;
use App\Http\Resources\MedicationResource;
use App\Models\PatientAllergy;
use App\Models\PatientChronicDisease;
use App\Models\PatientMedication;
use App\Models\User;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    /**
     * جلب السجل الطبي المجمّع للمريض (الأمراض المزمنة، الأدوية، والحساسية)
     */
    public function index(Request $request, ?User $patient = null)
    {
        $targetUser = $patient ?: $request->user('sanctum');

        if (!$targetUser) {
            return response()->json([
                'status'  => true,
                'message' => 'السجل الطبي متاح للعرض',
                'data'    => [
                    'chronic_diseases' => [],
                    'medications'      => [],
                    'allergies'        => [],
                ],
            ]);
        }

        // تحميل علاقات السجلات الدوائية والطبية لمنع الـ N+1
        $targetUser->load(['chronicDiseases', 'medications', 'allergies']);

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب السجل الطبي والدواء بنجاح',
            'data'    => [
                'chronic_diseases' => ChronicDiseaseResource::collection($targetUser->chronicDiseases),
                'medications'      => MedicationResource::collection($targetUser->medications),
                'allergies'        => AllergyResource::collection($targetUser->allergies),
            ],
        ]);
    }

    /**
     * إضافة عنصر لسجل المريض (مرض مزمن chronic_disease، دواء medication، أو حساسية allergy)
     */
    public function store(StoreMedicalRecordRequest $request, ?User $patient = null)
    {
        $targetUser = $patient ?: $request->user('sanctum');

        if (!$targetUser) {
            return response()->json([
                'status'        => false,
                'message'       => 'يجب تسجيل الدخول أولاً لإضافة سجل طبي',
                'require_login' => true,
            ], 401);
        }

        $validated = $request->validated();
        $type = $validated['type'];

        if ($type === 'chronic_disease') {
            $record = $targetUser->chronicDiseases()->create([
                'disease_name'   => $validated['disease_name'],
                'severity'       => $validated['severity'] ?? 'medium',
                'diagnosis_date' => $validated['diagnosis_date'] ?? null,
                'notes'          => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'تم إضافة المرض المزمن بنجاح',
                'data'    => new ChronicDiseaseResource($record),
            ]);
        }

        if ($type === 'medication') {
            $record = $targetUser->medications()->create([
                'medication_name' => $validated['medication_name'],
                'dosage'          => $validated['dosage'] ?? null,
                'frequency'       => $validated['frequency'] ?? null,
                'start_date'      => $validated['start_date'] ?? null,
                'notes'           => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'تم إضافة الدواء بنجاح',
                'data'    => new MedicationResource($record),
            ]);
        }

        if ($type === 'allergy') {
            $record = $targetUser->allergies()->create([
                'allergen' => $validated['allergen'],
                'severity' => $validated['severity'] ?? 'medium',
                'reaction' => $validated['reaction'] ?? null,
                'notes'    => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'تم إضافة الحساسية بنجاح',
                'data'    => new AllergyResource($record),
            ]);
        }

        return response()->json([
            'status'  => false,
            'message' => 'نوع السجل غير معروف',
        ], 400);
    }

    /**
     * تعديل عنصر في السجل الطبي أو الدوائي
     */
    public function update(StoreMedicalRecordRequest $request, string $type, int $id, ?User $patient = null)
    {
        $targetUser = $patient ?: $request->user('sanctum');

        if (!$targetUser) {
            return response()->json([
                'status'        => false,
                'message'       => 'يجب تسجيل الدخول لتعديل السجل الطبي',
                'require_login' => true,
            ], 401);
        }

        $validated = $request->validated();

        if ($type === 'chronic_disease') {
            $record = PatientChronicDisease::where('id', $id)->where('user_id', $targetUser->id)->first();
            if (!$record) {
                return response()->json(['status' => false, 'message' => 'السجل غير موجود'], 404);
            }

            $record->update([
                'disease_name'   => $validated['disease_name'],
                'severity'       => $validated['severity'] ?? 'medium',
                'diagnosis_date' => $validated['diagnosis_date'] ?? null,
                'notes'          => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'تم تعديل المرض المزمن بنجاح',
                'data'    => new ChronicDiseaseResource($record),
            ]);
        }

        if ($type === 'medication') {
            $record = PatientMedication::where('id', $id)->where('user_id', $targetUser->id)->first();
            if (!$record) {
                return response()->json(['status' => false, 'message' => 'السجل غير موجود'], 404);
            }

            $record->update([
                'medication_name' => $validated['medication_name'],
                'dosage'          => $validated['dosage'] ?? null,
                'frequency'       => $validated['frequency'] ?? null,
                'start_date'      => $validated['start_date'] ?? null,
                'notes'           => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'تم تعديل الدواء بنجاح',
                'data'    => new MedicationResource($record),
            ]);
        }

        if ($type === 'allergy') {
            $record = PatientAllergy::where('id', $id)->where('user_id', $targetUser->id)->first();
            if (!$record) {
                return response()->json(['status' => false, 'message' => 'السجل غير موجود'], 404);
            }

            $record->update([
                'allergen' => $validated['allergen'],
                'severity' => $validated['severity'] ?? 'medium',
                'reaction' => $validated['reaction'] ?? null,
                'notes'    => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'تم تعديل الحساسية بنجاح',
                'data'    => new AllergyResource($record),
            ]);
        }

        return response()->json([
            'status'  => false,
            'message' => 'نوع السجل غير معروف',
        ], 400);
    }

    /**
     * حذف عنصر من السجل الطبي للدواء أو المرض أو الحساسية
     */
    public function destroy(Request $request, string $type, int $id, ?User $patient = null)
    {
        $targetUser = $patient ?: $request->user('sanctum');

        $query = null;
        if ($type === 'chronic_disease') {
            $query = PatientChronicDisease::where('id', $id);
        } elseif ($type === 'medication') {
            $query = PatientMedication::where('id', $id);
        } elseif ($type === 'allergy') {
            $query = PatientAllergy::where('id', $id);
        }

        if ($query) {
            if ($targetUser) {
                $query->where('user_id', $targetUser->id);
            }
            $query->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم الحذف بنجاح',
        ]);
    }
}
