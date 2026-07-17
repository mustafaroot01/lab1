<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource لعرض الباقات الطبية في تطبيق الموبايل
 * يُستخدم في: PackageController@packages و packageDetails
 * يُستخدم أيضاً في: CatalogController@catalog (قسم الباقات)
 */
class MobilePackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $original = (float) $this->original_price;
        $current  = (float) ($this->discount_price ?? $this->original_price);
        $savings  = $original > $current
            ? round((($original - $current) / $original) * 100)
            : 0;

        return [
            'id'               => $this->id,
            'item_type'        => 'package',
            'name_ar'          => $this->name_ar,
            'name_en'          => $this->name_en ?? null,
            'description_ar'   => $this->description_ar,
            'description_en'   => $this->description_en ?? null,
            'original_price'   => $original,
            'price'            => $current,
            'discount_percent' => $savings,
            'image'            => $this->image ? (
                str_contains($this->image, '/storage/')
                    ? asset('storage/' . ltrim(explode('/storage/', $this->image)[1], '/'))
                    : (str_starts_with($this->image, 'http')
                        ? $this->image
                        : (str_starts_with($this->image, 'storage/')
                            ? asset($this->image)
                            : asset('storage/' . ltrim($this->image, '/'))))
            ) : null,
            'is_active'        => (bool) $this->is_active,
            'tests_count'      => $this->whenLoaded('tests', fn() => $this->tests->count(), 0),
            'tests'            => $this->whenLoaded('tests', fn() => $this->tests->map(fn($t) => [
                'id'               => $t->id,
                'name_ar'          => $t->name_ar,
                'name_en'          => $t->name_en,
                'individual_price' => (float) ($t->total_price ?? 0),
                'fasting_required' => (bool) $t->fasting_required,
                'result_time'      => $t->result_time,
            ])),
        ];
    }
}
