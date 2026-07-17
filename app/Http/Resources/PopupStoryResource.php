<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PopupStoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'image_url'          => str_starts_with($this->image_path, 'data:') || str_starts_with($this->image_path, 'http')
                ? $this->image_path
                : (str_contains($this->image_path, '/storage/')
                    ? asset('storage/' . ltrim(explode('/storage/', $this->image_path)[1], '/'))
                    : asset('storage/' . ltrim($this->image_path, '/'))),
            'image_path'         => $this->image_path,
            'duration_seconds'   => (int) $this->duration_seconds,
            'display_frequency'  => $this->display_frequency,
            'display_frequency_label' => match ($this->display_frequency) {
                'always'           => 'في كل دخول لتطبيق الهوم',
                'once_per_day'     => 'مرة واحدة يومياً للمراجع',
                'once_per_session' => 'مرة واحدة عند فتح التطبيق',
                default            => $this->display_frequency,
            },
            'button_text'        => $this->button_text,
            'button_link_type'   => $this->button_link_type ?? 'none',
            'button_link_target' => $this->button_link_target,
            'start_at'           => $this->start_at?->format('Y-m-d H:i:s'),
            'end_at'             => $this->end_at?->format('Y-m-d H:i:s'),
            'sort_order'         => (int) $this->sort_order,
            'is_active'          => (bool) $this->is_active,
            'is_currently_active'=> $this->isCurrentlyActive(),
            'views_count'        => (int) $this->views_count,
            'clicks_count'       => (int) $this->clicks_count,
            'ctr_percentage'     => $this->views_count > 0 ? round(($this->clicks_count / $this->views_count) * 100, 1) : 0,
            'created_at'         => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
