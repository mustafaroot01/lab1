<?php

namespace App\Services\Coverage\Strategies;

use Illuminate\Database\Eloquent\Collection;

interface CoverageStrategyInterface
{
    /**
     * Process the collection of zones based on the strategy.
     * Must return a filtered or modified collection WITHOUT side effects.
     */
    public function process(Collection $zones, float $lat, float $lng): Collection;
}
