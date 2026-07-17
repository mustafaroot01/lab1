<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use App\Http\Resources\AreaResource;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * إضافة منطقة جديدة لقضاء
     */
    public function store(StoreAreaRequest $request)
    {
        $data = $request->validated();
        if (empty($data['sort_order'])) {
            $max = Area::where('district_id', $data['district_id'])->max('sort_order');
            $data['sort_order'] = $max ? $max + 1 : 1;
        }

        $area = Area::create($data);

        return response()->json([
            'status'  => true,
            'message' => 'تم إضافة المنطقة بنجاح',
            'area'    => new AreaResource($area->load('district')),
        ], 201);
    }

    /**
     * تحديث منطقة
     */
    public function update(UpdateAreaRequest $request, Area $area)
    {
        $area->update($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث المنطقة بنجاح',
            'area'    => new AreaResource($area->load('district')),
        ]);
    }

    /**
     * حذف منطقة
     */
    public function destroy(Area $area)
    {
        $area->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف المنطقة بنجاح',
        ]);
    }

    /**
     * تفعيل / إخفاء منطقة
     */
    public function toggleActive(Area $area)
    {
        $area->update(['is_active' => !$area->is_active]);

        return response()->json([
            'status'  => true,
            'message' => $area->is_active ? 'تم تفعيل المنطقة بنجاح' : 'تم إخفاء المنطقة بنجاح',
            'area'    => new AreaResource($area->load('district')),
        ]);
    }
}
