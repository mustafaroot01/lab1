<?php

namespace App\Services\Coverage\Strategies;

use Illuminate\Database\Eloquent\Collection;

class PriorityStrategy implements CoverageStrategyInterface
{
    public function process(Collection $zones, float $lat, float $lng): Collection
    {
        if ($zones->isEmpty()) {
            return $zones;
        }

        // Sort by priority descending (highest priority first).
        // If priorities are equal, we can prioritize 'polygon' match over 'grace_distance' match.
        $sorted = $zones->sort(function ($a, $b) {
            if ($a->priority === $b->priority) {
                if (isset($a->_match_type) && $b->_match_type !== $a->_match_type) {
                    return $a->_match_type === 'polygon' ? -1 : 1;
                }
                return 0;
            }
            return $a->priority > $b->priority ? -1 : 1;
        });

        // Return only the top zone in a new collection.
        return new Collection([$sorted->first()]);
    }
}
