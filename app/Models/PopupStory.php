<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopupStory extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'duration_seconds',
        'display_frequency',
        'button_text',
        'button_link_type',
        'button_link_target',
        'start_at',
        'end_at',
        'sort_order',
        'is_active',
        'views_count',
        'clicks_count',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'sort_order'       => 'integer',
        'is_active'        => 'boolean',
        'views_count'      => 'integer',
        'clicks_count'     => 'integer',
        'start_at'         => 'datetime',
        'end_at'           => 'datetime',
    ];

    /**
     * هل الإعلان متاح للعرض حالياً بناءً على الوقت والحالة؟
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->start_at && $now->lt($this->start_at)) {
            return false;
        }

        if ($this->end_at && $now->gt($this->end_at)) {
            return false;
        }

        return true;
    }

    /**
     * Scope لجلب الإعلانات الفعالة والمتاحة زمنياً فقط
     */
    public function scopeActive($query)
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            });
    }

    /**
     * Scope لترتيب الإعلانات حسب الأولوية
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc');
    }
}
