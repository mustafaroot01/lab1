<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PatientResource;
use App\Models\Order;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * جلب جميع المرضى المسجلين في التطبيق مع إحصائيات سريعة وتصفية ذكية
     */
    public function index(Request $request)
    {
        $query = \App\Queries\PatientSearch::run($request)->with(['district.branch'])->latest();

        // pagination اختياري — إذا أُرسل itemsPerPage نُقسّم، وإلا نرجع الكل (توافق مع الفرونت الحالي)
        if ($request->filled('itemsPerPage')) {
            $paginated = $query->paginate(min((int) $request->input('itemsPerPage', 25), 100));
            $patients = collect($paginated->items());
            $total = $paginated->total();
        } else {
            $patients = $query->get();
            $total = $patients->count();
        }

        // إحصائيات باستعلام واحد على جدول المرضى النظيف
        $stats = Patient::selectRaw("
                COUNT(*) as total_patients,
                SUM(CASE WHEN is_profile_completed = 1 THEN 1 ELSE 0 END) as completed_profile,
                SUM(CASE WHEN is_profile_completed = 0 OR is_profile_completed IS NULL THEN 1 ELSE 0 END) as pending_profile,
                SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as males,
                SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as females
            ")
            ->first();

        $summary = [
            'total_patients'    => (int) $stats->total_patients,
            'completed_profile' => (int) $stats->completed_profile,
            'pending_profile'   => (int) $stats->pending_profile,
            'males'             => (int) $stats->males,
            'females'           => (int) $stats->females,
        ];

        return response()->json([
            'status'        => true,
            'message'       => 'تم جلب قائمة المرضى بنجاح',
            'patients'      => PatientResource::collection($patients),
            'totalPatients' => $total,
            'summary'       => $summary,
        ]);
    }

    /**
     * تحديث بيانات المريض من لوحة التحكم
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $data = $request->validated();


        $patient->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث بيانات المريض بنجاح',
            'data'    => new PatientResource($patient->fresh(['district.branch'])),
        ]);
    }

    /**
     * عرض تفاصيل مريض محدد في صفحة البروفايل مع طلباته وسجله الطبي ومتابعاته
     */
    public function show(Request $request, Patient $patient)
    {
        $loadedPatient = $patient->load(['district.branch', 'chronicDiseases', 'medications', 'allergies']);

        $ordersQuery = $patient->orders()
            ->with(['patient', 'branch', 'technician', 'items', 'results', 'statusLogs.changedBy', 'coupon'])
            ->latest();

        // صفحات الطلبات — لمنع جلب آلاف السجلات للمرضى القدامى
        $itemsPerPage = (int) $request->input('itemsPerPage', 20);
        $orders = $ordersQuery->paginate($itemsPerPage);

        // استعلام تجميعي واحد فعال للملخص دون جلب كل الطلبات أو علاقاتها للذاكرة
        $ordersStats = $patient->orders()
            ->selectRaw('status, COUNT(*) as count, SUM(CASE WHEN status = "completed" THEN total ELSE 0 END) as spent')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $getCount = fn(array $statuses) => collect($statuses)->sum(fn($st) => (int) ($ordersStats[$st]->count ?? 0));

        $summary = [
            'total'       => $orders->total(),
            'completed'   => (int) ($ordersStats['completed']->count ?? 0),
            'in_progress' => $getCount(['in_progress', 'sample_collected', 'on_the_way', 'technician_assigned']),
            'pending'     => $getCount(['pending', 'confirmed', 'awaiting_technician']),
            'cancelled'   => (int) ($ordersStats['cancelled']->count ?? 0),
            'total_spent' => (float) ($ordersStats['completed']->spent ?? 0),
        ];

        return response()->json([
            'status'         => true,
            'patient'        => new PatientResource($loadedPatient),
            'orders'         => OrderResource::collection($orders->items()),
            'orders_summary' => $summary,
            'orders_meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    /**
     * تفعيل أو إيقاف حساب المريض
     * وفي حالة الإيقاف يتم حذف جميع التوكنات لطرده من التطبيق فوراً
     */
    public function toggleStatus(Patient $patient)
    {
        $patient->is_active = !$patient->is_active;
        $patient->save();

        if (!$patient->is_active) {
            // طرد المريض من التطبيق وحذف جميع التوكنات والجلسات
            $patient->tokens()->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => $patient->is_active
                ? 'تم تفعيل حساب المريض بنجاح.'
                : 'تم إيقاف حساب المريض وطرده من التطبيق فوراً (حذف جميع التوكنات).',
            'data'    => new PatientResource($patient->fresh(['district.branch'])),
        ]);
    }

    /**
     * طرد المريض وتسجيل خروجه قسراً من جميع الأجهزة (حذف التوكنات)
     */
    public function revokeTokens(Patient $patient)
    {
        $patient->tokens()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم إنهاء جميع جلسات المريض وطرده من التطبيق بنجاح.',
        ]);
    }

    /**
     * حذف المريض
     * تحذير: الحذف يشمل طلباته ونتائجه وسجله الطبي ومحادثاته (cascade)،
     * لذلك نمنعه إذا كان لديه طلبات — الأصح تعطيل الحساب بدل الحذف.
     */
    public function destroy(Patient $patient)
    {
        // منع حذف مريض لديه طلبات — حماية للسجلات الطبية والمالية من الضياع (cascade)
        if ($patient->orders()->exists()) {
            return response()->json([
                'status'  => false,
                'message' => 'لا يمكن حذف مريض لديه طلبات مسجلة — قم بإيقاف حسابه بدلاً من ذلك للحفاظ على السجلات',
            ], 422);
        }

        // حذف التوكنات ثم حذف الحساب
        $patient->tokens()->delete();
        $patient->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف المريض بنجاح',
        ]);
    }
}
