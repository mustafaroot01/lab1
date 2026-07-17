<?php

namespace App\Queries;

use App\Models\MedicalTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MedicalTestSearch
{
    /**
     * تشغيل استعلام البحث والتصفية للتحاليل الطبية المفعلة
     */
    public static function run(Request|string $requestOrQuery, ?int $limit = null): Builder
    {
        $queryText = is_string($requestOrQuery)
            ? trim($requestOrQuery)
            : trim($requestOrQuery->get('q') ?: $requestOrQuery->get('search', ''));

        $builder = MedicalTest::query()
            ->where('is_active', true)
            ->where('total_price', '>', 0)
            ->when($queryText !== '', function ($q) use ($queryText) {
                $q->where(function ($sub) use ($queryText) {
                    $sub->where('name_ar', 'LIKE', "%{$queryText}%")
                        ->orWhere('name_en', 'LIKE', "%{$queryText}%")
                        ->orWhere('key', 'LIKE', "%{$queryText}%");
                });
            });

        if ($limit !== null && $limit > 0) {
            $builder->limit($limit);
        }

        return $builder;
    }
}
