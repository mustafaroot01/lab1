<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branches\UpdateBranchServiceFeeRequest;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchServiceFeeController extends Controller
{
    /**
     * جلب قائمة الفروع وأجور الزيارات المنزلية الثابتة والحد المجاني لكل فرع
     */
    public function index()
    {
        $branches = Branch::select([
            'id', 
            'name_ar', 
            'address', 
            'phone', 
            'is_active', 
            'service_fee', 
            'free_threshold', 
            'fee_notes'
        ])->orderBy('id')->get();

        return response()->json([
            'status'   => true,
            'message'  => 'تم جلب أجور الفروع بنجاح',
            'branches' => $branches,
            'data'     => [
                'branches' => $branches,
            ],
        ]);
    }

    /**
     * تحديث أجور الزيارة المنزلية الثابتة والحد المجاني للفرع
     */
    public function update(UpdateBranchServiceFeeRequest $request, Branch $branch)
    {
        $validated = $request->validated();

        $branch->update([
            'service_fee'    => $request->has('service_fee') ? ($validated['service_fee'] ?? 0) : $branch->service_fee,
            'free_threshold' => $request->has('free_threshold') ? ($validated['free_threshold'] ?? 0) : $branch->free_threshold,
            'fee_notes'      => $request->has('fee_notes') ? ($validated['fee_notes'] ?? null) : $branch->fee_notes,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث أجور وشروط الزيارة الميدانية للفرع بنجاح',
            'branch'  => $branch->fresh(),
        ]);
    }
}
