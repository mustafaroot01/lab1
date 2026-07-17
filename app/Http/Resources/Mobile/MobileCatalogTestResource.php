<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource لعرض بيانات التحاليل الطبية في تطبيق الموبايل
 * يُستخدم داخل CatalogGroupResource لتنسيق التحاليل في الكتالوج والبحث
 */
class MobileCatalogTestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'item_type'        => 'test',
            'name_ar'          => $this->name_ar,
            'name_en'          => $this->name_en,
            'price'            => (float) $this->total_price,
            'fasting_required' => (bool) $this->fasting_required,
            'result_time'      => $this->result_time,
            'description'      => $this->description,
            'group'            => $this->whenLoaded('group', fn() => $this->group ? [
                'id'      => $this->group->id,
                'name_ar' => $this->group->name_ar,
                'icon'    => $this->group->icon,
                'color'   => $this->group->color,
            ] : null),
        ];
    }
}
