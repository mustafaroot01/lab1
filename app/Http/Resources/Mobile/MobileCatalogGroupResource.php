<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource لعرض مجموعة مخبرية مع تحاليلها في كتالوج تطبيق الموبايل
 * يُستخدم في: CatalogController@catalog
 */
class MobileCatalogGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name_ar'     => $this->name_ar,
            'name_en'     => $this->name_en,
            'icon'        => $this->icon,
            'color'       => $this->color,
            'tests_count' => $this->whenLoaded('tests', fn() => $this->tests->count(), 0),
            'tests'       => MobileCatalogTestResource::collection($this->whenLoaded('tests')),
        ];
    }
}
