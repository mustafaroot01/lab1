<?php

namespace App\Http\Controllers\Api\V1\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\PopupStoryResource;
use App\Models\PopupStory;
use Illuminate\Http\Request;

class MobilePopupStoryController extends Controller
{
    /**
     * جلب إعلانات الستوري المتاحة والنشطة للموبايل ليتم عرضها في شاشة الهوم الطولية
     */
    public function getActiveStories(Request $request)
    {
        $stories = PopupStory::active()->ordered()->get();

        return response()->json([
            'status'  => true,
            'message' => 'تم جلب إعلانات الستوري النشطة بنجاح',
            'stories' => PopupStoryResource::collection($stories),
        ]);
    }

    /**
     * تسجيل مشاهدة من الموبايل لإحصائيات الإدارة
     */
    public function recordView(PopupStory $popupStory)
    {
        $popupStory->increment('views_count');

        return response()->json(['status' => true]);
    }

    /**
     * تسجيل نقرة (Click CTA) من الموبايل لإحصائيات الإدارة
     */
    public function recordClick(PopupStory $popupStory)
    {
        $popupStory->increment('clicks_count');

        return response()->json(['status' => true]);
    }
}
