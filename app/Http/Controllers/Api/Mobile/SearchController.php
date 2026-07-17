<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MedicalTest;
use App\Models\PackageOffer;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * بحث سريع في التحاليل والباقات للموبايل (شريط البحث في الواجهة الرئيسية)
     */
    public function search(Request $request)
    {
        $query = trim($request->get('q') ?: $request->get('search', ''));

        $tests = \App\Queries\MedicalTestSearch::run($request, 30)
            ->with('group:id,name_ar')
            ->get(['id', 'test_group_id', 'name_ar', 'name_en', 'key', 'total_price', 'fasting_required', 'result_time'])
            ->map(fn($t) => [
                'id'               => $t->id,
                'item_type'        => 'test',
                'name_ar'          => $t->name_ar,
                'name_en'          => $t->name_en,
                'group_name'       => $t->group?->name_ar,
                'price'            => $t->total_price,
                'fasting_required' => $t->fasting_required,
                'result_time'      => $t->result_time,
            ]);

        $packages = PackageOffer::where('is_active', true)
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name_ar', 'LIKE', "%{$query}%")
                        ->orWhere('description_ar', 'LIKE', "%{$query}%");
                });
            })
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id'          => $p->id,
                'item_type'   => 'package',
                'name_ar'     => $p->name_ar,
                'description' => $p->description_ar,
                'original_price' => $p->original_price,
                'price'       => $p->discount_price ?? $p->original_price,
                'image'       => $p->image ? (str_starts_with($p->image, 'data:') || str_starts_with($p->image, 'http') ? $p->image : asset('storage/' . $p->image)) : null,
            ]);

        return response()->json([
            'status'   => true,
            'message'  => 'نتائج البحث',
            'tests'    => $tests,
            'packages' => $packages,
        ]);
    }
}
