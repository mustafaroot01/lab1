<?php
namespace App\DTOs;

use App\Models\CoverageZone;

class CoverageResultDTO
{
    public function __construct(
        public bool $isCovered,
        public ?string $message = null,
        public ?CoverageZone $zone = null,
        public ?float $fee = null,
        public ?string $matchType = null, // 'polygon' or 'grace_distance'
        public ?float $distance = null, // distance from border if grace_match
        public ?float $freeVisitThreshold = null
    ) {}

    public function toArray(): array
    {
        return [
            'covered' => $this->isCovered,
            'zone_id' => $this->zone?->id,
            'zone_name' => $this->zone?->name,
            'fee' => $this->fee,
            'match_type' => $this->matchType,
            'distance_from_border' => $this->distance,
            'free_visit_threshold' => $this->freeVisitThreshold,
            'message' => $this->message,
        ];
    }
}
