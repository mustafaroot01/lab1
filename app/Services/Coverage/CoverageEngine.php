<?php

namespace App\Services\Coverage;

use App\DTOs\CoverageResultDTO;
use App\Repositories\Coverage\CoverageRepository;
use App\Services\Coverage\Contracts\CoverageEngineInterface;
use App\Services\Coverage\Strategies\BoundingBoxStrategy;
use App\Services\Coverage\Strategies\GraceDistanceStrategy;
use App\Services\Coverage\Strategies\PolygonStrategy;
use App\Services\Coverage\Strategies\PriorityStrategy;

class CoverageEngine implements CoverageEngineInterface
{
    public function __construct(
        private CoverageRepository $repository,
        private BoundingBoxStrategy $boundingBoxStrategy,
        private PolygonStrategy $polygonStrategy,
        private GraceDistanceStrategy $graceDistanceStrategy,
        private PriorityStrategy $priorityStrategy,
        private CoverageLogger $logger
    ) {}

    public function verifyCoverage(float $lat, float $lng, ?int $patientId = null): CoverageResultDTO
    {
        $startTime = microtime(true);
        $zones = $this->repository->getActiveZones();

        if ($zones->isEmpty()) {
            return $this->logAndReturnResult($lat, $lng, $patientId, $startTime, new CoverageResultDTO(
                isCovered: false,
                message: 'عذراً، خدمة الزيارات المنزلية غير مفعلة أو لا توجد مناطق تغطية.'
            ), 'none', false, false, null);
        }

        // 1. Filter by Bounding Box (Fast rejection)
        $candidates = $this->boundingBoxStrategy->process($zones, $lat, $lng);

        if ($candidates->isEmpty()) {
            return $this->logAndReturnResult($lat, $lng, $patientId, $startTime, new CoverageResultDTO(
                isCovered: false,
                message: 'عذراً، موقعك خارج نطاق التغطية بالكامل.'
            ), 'bounding_box', false, false, null);
        }

        // 2. Exact Match (Point In Polygon)
        $polygonMatched = $this->polygonStrategy->process($candidates, $lat, $lng);

        // 3. Grace Match (for candidates that didn't match polygon)
        // We pass ALL candidates to grace distance strategy; it skips the ones that already polygon-matched.
        $allMatched = $this->graceDistanceStrategy->process($candidates, $lat, $lng);

        // We only keep zones that matched either via polygon or grace.
        $validMatches = $allMatched->filter(fn($z) => isset($z->_match_type));

        if ($validMatches->isEmpty()) {
            return $this->logAndReturnResult($lat, $lng, $patientId, $startTime, new CoverageResultDTO(
                isCovered: false,
                message: 'عذراً، موقعك خارج نطاق التغطية ومسافة السماح المحددة.'
            ), 'polygon_and_grace', false, false, null);
        }

        // 4. Resolve Priority
        $finalZone = $this->priorityStrategy->process($validMatches, $lat, $lng)->first();

        $result = new CoverageResultDTO(
            isCovered: true,
            message: 'تم تحديد منطقة التغطية بنجاح.',
            zone: $finalZone,
            fee: (float) $finalZone->service_fee,
            matchType: $finalZone->_match_type,
            distance: $finalZone->_distance_from_border,
            freeVisitThreshold: $finalZone->free_visit_threshold !== null ? (float) $finalZone->free_visit_threshold : null
        );

        $isInside = $finalZone->_match_type === 'polygon_exact';
        $isGrace = $finalZone->_match_type === 'grace_distance';

        return $this->logAndReturnResult($lat, $lng, $patientId, $startTime, $result, 'full_engine', $isInside, $isGrace, $finalZone->_distance_from_border);
    }

    private function logAndReturnResult(
        float $lat,
        float $lng,
        ?int $patientId,
        float $startTime,
        CoverageResultDTO $result,
        string $algorithmUsed,
        bool $insidePolygon,
        bool $graceMatch,
        ?float $distanceFromBorder
    ): CoverageResultDTO {
        $executionTimeMs = (microtime(true) - $startTime) * 1000;
        
        $this->logger->log(
            $lat,
            $lng,
            $patientId,
            $result->zone?->id,
            $executionTimeMs,
            $algorithmUsed,
            $insidePolygon,
            $graceMatch,
            $distanceFromBorder
        );

        return $result;
    }
}
