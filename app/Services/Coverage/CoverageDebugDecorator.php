<?php

namespace App\Services\Coverage;

use App\DTOs\CoverageResultDTO;
use App\Services\Coverage\Contracts\CoverageEngineInterface;
use App\Services\Coverage\Strategies\BoundingBoxStrategy;
use App\Services\Coverage\Strategies\GraceDistanceStrategy;
use App\Services\Coverage\Strategies\PolygonStrategy;
use App\Services\Coverage\Strategies\PriorityStrategy;
use App\Repositories\Coverage\CoverageRepository;

class CoverageDebugDecorator
{
    public function __construct(
        private CoverageEngineInterface $engine,
        private CoverageRepository $repository,
        private BoundingBoxStrategy $boundingBoxStrategy,
        private PolygonStrategy $polygonStrategy,
        private GraceDistanceStrategy $graceDistanceStrategy,
        private PriorityStrategy $priorityStrategy
    ) {}

    public function simulateWithDebug(float $lat, float $lng): array
    {
        $startTime = microtime(true);
        $debugSteps = [];

        // Fetch zones
        $fetchStart = microtime(true);
        $zones = $this->repository->getActiveZones();
        $debugSteps[] = [
            'step' => 'Fetch Zones',
            'status' => $zones->isNotEmpty() ? 'Passed' : 'Failed',
            'message' => "Fetched {$zones->count()} active zones",
            'duration_ms' => round((microtime(true) - $fetchStart) * 1000, 2),
        ];

        if ($zones->isEmpty()) {
            return $this->formatResponse($lat, $lng, $startTime, false, 'لا توجد مناطق فعالة', null, $debugSteps);
        }

        // 1. Bounding Box
        $bbStart = microtime(true);
        $candidates = $this->boundingBoxStrategy->process($zones, $lat, $lng);
        $debugSteps[] = [
            'step' => 'Bounding Box Filter',
            'status' => $candidates->isNotEmpty() ? 'Passed' : 'Failed',
            'message' => "Found {$candidates->count()} candidates inside bounding boxes",
            'duration_ms' => round((microtime(true) - $bbStart) * 1000, 2),
        ];

        if ($candidates->isEmpty()) {
            return $this->formatResponse($lat, $lng, $startTime, false, 'خارج الـ Bounding Box بالكامل', null, $debugSteps);
        }

        // 2. Polygon Exact Match
        $polyStart = microtime(true);
        $polygonMatched = $this->polygonStrategy->process($candidates, $lat, $lng);
        $debugSteps[] = [
            'step' => 'Polygon Exact Match',
            'status' => $polygonMatched->isNotEmpty() ? 'Passed' : 'Outside',
            'message' => $polygonMatched->isNotEmpty() ? "Matched {$polygonMatched->count()} polygons exactly" : "No exact polygon match",
            'duration_ms' => round((microtime(true) - $polyStart) * 1000, 2),
        ];

        // 3. Grace Distance
        $graceStart = microtime(true);
        $allMatched = $this->graceDistanceStrategy->process($candidates, $lat, $lng);
        $graceMatched = $allMatched->filter(fn($z) => $z->_match_type === 'grace_distance');
        $debugSteps[] = [
            'step' => 'Grace Distance Check',
            'status' => $graceMatched->isNotEmpty() ? 'Passed' : ($polygonMatched->isNotEmpty() ? 'Skipped' : 'Failed'),
            'message' => $graceMatched->isNotEmpty() ? "Found {$graceMatched->count()} grace matches" : "No grace matches within distance",
            'duration_ms' => round((microtime(true) - $graceStart) * 1000, 2),
        ];

        $validMatches = $allMatched->filter(fn($z) => isset($z->_match_type));

        if ($validMatches->isEmpty()) {
            return $this->formatResponse($lat, $lng, $startTime, false, 'خارج المنطقة ومسافة السماح', null, $debugSteps);
        }

        // 4. Priority Resolution
        $prioStart = microtime(true);
        $finalZone = $this->priorityStrategy->process($validMatches, $lat, $lng)->first();
        $debugSteps[] = [
            'step' => 'Priority Resolution',
            'status' => 'Passed',
            'message' => "Selected Zone #{$finalZone->id} ({$finalZone->name}) with Priority {$finalZone->priority}",
            'duration_ms' => round((microtime(true) - $prioStart) * 1000, 2),
        ];

        // Call the actual engine to ensure side-effects (like logging) happen exactly as they would in production
        // Although for a simulator we might not want to log. But the engine handles its own logging settings.
        // To be pure, we just reconstruct the final output here since we manually stepped through it.
        $result = new CoverageResultDTO(
            isCovered: true,
            message: 'تم تحديد المنطقة',
            zone: $finalZone,
            fee: (float) $finalZone->service_fee,
            matchType: $finalZone->_match_type,
            distance: $finalZone->_distance_from_border
        );

        return $this->formatResponse($lat, $lng, $startTime, true, 'تمت المحاكاة بنجاح', $result, $debugSteps);
    }

    private function formatResponse(float $lat, float $lng, float $startTime, bool $isCovered, string $message, ?CoverageResultDTO $result, array $debugSteps): array
    {
        return [
            'status' => true,
            'message' => $message,
            'data' => [
                'coordinates' => ['lat' => $lat, 'lng' => $lng],
                'result' => $result ? $result->toArray() : ['covered' => false],
                'debug_timeline' => $debugSteps,
                'total_execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]
        ];
    }
}
