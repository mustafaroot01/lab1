<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePopupStoryRequest;
use App\Http\Requests\UpdatePopupStoryRequest;
use App\Http\Resources\PopupStoryResource;
use App\Models\PopupStory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PopupStoryController extends Controller
{
    /**
     * قائمة إعلانات البوب أب والستوريات في لوحة التحكم
     */
    public function index(Request $request)
    {
        $query = PopupStory::ordered();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        $stories = $query->get();

        return response()->json([
            'status'        => true,
            'message'       => 'تم جلب إعلانات الستوري بنجاح',
            'stories'       => PopupStoryResource::collection($stories),
            'totalStories'  => $stories->count(),
            'summary'       => [
                'total'        => PopupStory::count(),
                'active'       => PopupStory::where('is_active', true)->count(),
                'inactive'     => PopupStory::where('is_active', false)->count(),
                'total_views'  => PopupStory::sum('views_count'),
                'total_clicks' => PopupStory::sum('clicks_count'),
            ],
        ]);
    }

    /**
     * إضافة إعلان ستوري جديد
     */
    public function store(StorePopupStoryRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('popups', 'public');
            $data['image_path'] = $path;
        }

        if (empty($data['sort_order'])) {
            $maxOrder = PopupStory::max('sort_order');
            $data['sort_order'] = ($maxOrder ? $maxOrder + 1 : 1);
        }

        $story = PopupStory::create($data);

        return response()->json([
            'status'  => true,
            'message' => 'تم حفظ إعلان الستوري بنجاح',
            'story'   => new PopupStoryResource($story),
        ], 201);
    }

    /**
     * عرض إعلان ستوري محدد
     */
    public function show(PopupStory $popupStory)
    {
        return response()->json([
            'status' => true,
            'story'  => new PopupStoryResource($popupStory),
        ]);
    }

    /**
     * تحديث إعلان الستوري
     */
    public function update(UpdatePopupStoryRequest $request, PopupStory $popupStory)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($popupStory->image_path && Storage::disk('public')->exists($popupStory->image_path)) {
                Storage::disk('public')->delete($popupStory->image_path);
            }
            $data['image_path'] = $request->file('image')->store('popups', 'public');
        }

        $popupStory->update($data);

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث إعلان الستوري بنجاح',
            'story'   => new PopupStoryResource($popupStory->fresh()),
        ]);
    }

    /**
     * حذف إعلان الستوري
     */
    public function destroy(PopupStory $popupStory)
    {
        if ($popupStory->image_path && Storage::disk('public')->exists($popupStory->image_path)) {
            Storage::disk('public')->delete($popupStory->image_path);
        }

        $popupStory->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم حذف إعلان الستوري بنجاح',
        ]);
    }

    /**
     * تفعيل / إيقاف إعلان الستوري
     */
    public function toggleActive(PopupStory $popupStory)
    {
        $popupStory->update(['is_active' => !$popupStory->is_active]);

        return response()->json([
            'status'  => true,
            'message' => $popupStory->is_active ? 'تم تفعيل الستوري بنجاح' : 'تم إيقاف الستوري بنجاح',
            'story'   => new PopupStoryResource($popupStory->fresh()),
        ]);
    }

    /**
     * إعادة ترتيب الستوريات
     */
    public function reorder(Request $request)
    {
        $orders = $request->input('orders', []);

        foreach ($orders as $item) {
            if (isset($item['id'], $item['sort_order'])) {
                PopupStory::where('id', $item['id'])->update(['sort_order' => (int) $item['sort_order']]);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث ترتيب ظهور الستوريات بنجاح',
        ]);
    }
}
