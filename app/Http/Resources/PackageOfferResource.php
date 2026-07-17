<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name_ar'        => $this->name_ar,
            'name_en'        => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'original_price' => (float) $this->original_price,
            'discount_price' => $this->discount_price !== null ? (float) $this->discount_price : null,
            'image'          => $this->image ? (
                str_contains($this->image, '/storage/')
                    ? asset('storage/' . ltrim(explode('/storage/', $this->image)[1], '/'))
                    : (str_starts_with($this->image, 'http')
                        ? $this->image
                        : (str_starts_with($this->image, 'storage/')
                            ? asset($this->image)
                            : asset('storage/' . ltrim($this->image, '/'))))
            ) : null,
            'sort_order'     => (int) $this->sort_order,
            'is_active'      => (bool) $this->is_active,
            'tests_count'    => $this->whenCounted('tests'),
            'tests'          => $this->whenLoaded('tests', function () {
                return $this->tests->map(function ($test) {
                    return [
                        'id'           => $test->id,
                        'name_ar'      => $test->name_ar,
                        'name_en'      => $test->name_en,
                        'key'          => $test->key,
                        'price'        => (float) $test->price,
                        'total_price'  => (float) $test->total_price,
                    ];
                });
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
