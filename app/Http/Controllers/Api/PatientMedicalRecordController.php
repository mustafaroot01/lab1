<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Resources\AllergyResource;
use App\Http\Resources\ChronicDiseaseResource;
use App\Http\Resources\MedicationResource;
use App\Models\PatientAllergy;
use App\Models\PatientChronicDisease;
use App\Models\PatientMedication;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientMedicalRecordController extends Controller
{
    /**
     * جلب السجل الطبي لمريض محدد (أو للمريض المسجل في الموبايل)
     */
    public function index(Request $request, ?Patient $patient = null)
    {
        // إذا لم يتم تحديد مريض (في الموبايل)، يتم استخدام المستخدم المسجل
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

        $targetUser->load(['chronicDiseases', 'medications', 'allergies']);

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب السجل الطبي بنجاح',
            'data'    => [
                'chronic_diseases' => ChronicDiseaseResource::collection($targetUser->chronicDiseases),
                'medications'      => MedicationResource::collection($targetUser->medications),
                'allergies'        => AllergyResource::collection($targetUser->allergies),
            ],
        ]);
    }


    /**
     * إضافة عنصر للسجل الطبي (مرض مزمن، دواء، أو حساسية)
     */
    public function store(StoreMedicalRecordRequest $request, ?Patient $patient = null)
    {
        $targetUser = $patient ?: $request->user('sanctum');

        if (!$targetUser) {
            return response()->json([
                'status'  => false,
                'message' => 'يجب تسجيل الدخول أولاً لإضافة سجل طبي',
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
     * تعديل عنصر في السجل الطبي (مرض مزمن، دواء، أو حساسية)
     */
    public function update(StoreMedicalRecordRequest $request, string $type, int $id, ?Patient $patient = null)
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

        if (in_array($type, ['chronic-diseases', 'chronic_diseases', 'chronic_disease'])) {
            $type = 'chronic_disease';
        } elseif (in_array($type, ['medications', 'medication'])) {
            $type = 'medication';
        } elseif (in_array($type, ['allergies', 'allergy'])) {
            $type = 'allergy';
        }

        if ($type === 'chronic_disease') {
            $query = PatientChronicDisease::where('id', $id);
            if ($targetUser && !($targetUser instanceof \App\Models\Admin) && !$patient) {
                $query->where(function($q) use ($targetUser) {
                    $q->where('patient_id', $targetUser->id)->orWhere('user_id', $targetUser->id);
                });
            } elseif ($patient) {
                $query->where(function($q) use ($patient) {
                    $q->where('patient_id', $patient->id)->orWhere('user_id', $patient->id);
                });
            }
            $record = $query->first();
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
            $query = PatientMedication::where('id', $id);
            if ($targetUser && !($targetUser instanceof \App\Models\Admin) && !$patient) {
                $query->where(function($q) use ($targetUser) {
                    $q->where('patient_id', $targetUser->id)->orWhere('user_id', $targetUser->id);
                });
            } elseif ($patient) {
                $query->where(function($q) use ($patient) {
                    $q->where('patient_id', $patient->id)->orWhere('user_id', $patient->id);
                });
            }
            $record = $query->first();
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
            $query = PatientAllergy::where('id', $id);
            if ($targetUser && !($targetUser instanceof \App\Models\Admin) && !$patient) {
                $query->where(function($q) use ($targetUser) {
                    $q->where('patient_id', $targetUser->id)->orWhere('user_id', $targetUser->id);
                });
            } elseif ($patient) {
                $query->where(function($q) use ($patient) {
                    $q->where('patient_id', $patient->id)->orWhere('user_id', $patient->id);
                });
            }
            $record = $query->first();
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
     * حذف عنصر من السجل الطبي
     */
    public function destroy(Request $request, string $type, int $id, ?Patient $patient = null)
    {
        $targetUser = $patient ?: $request->user('sanctum');

        if (in_array($type, ['chronic-diseases', 'chronic_diseases', 'chronic_disease'])) {
            $type = 'chronic_disease';
        } elseif (in_array($type, ['medications', 'medication'])) {
            $type = 'medication';
        } elseif (in_array($type, ['allergies', 'allergy'])) {
            $type = 'allergy';
        }

        $query = null;
        if ($type === 'chronic_disease') {
            $query = PatientChronicDisease::where('id', $id);
        } elseif ($type === 'medication') {
            $query = PatientMedication::where('id', $id);
        } elseif ($type === 'allergy') {
            $query = PatientAllergy::where('id', $id);
        }

        if ($query) {
            // إذا كان المريض يحذف من حسابه وليس مشرفاً، يجب التأكد من ملكيته للسجل
            if ($targetUser && !($targetUser instanceof \App\Models\Admin) && !$patient) {
                $query->where(function($q) use ($targetUser) {
                    $q->where('patient_id', $targetUser->id)->orWhere('user_id', $targetUser->id);
                });
            } elseif ($patient) {
                $query->where(function($q) use ($patient) {
                    $q->where('patient_id', $patient->id)->orWhere('user_id', $patient->id);
                });
            }
            $query->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم الحذف بنجاح',
        ]);
    }
}

