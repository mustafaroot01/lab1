<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CoverageZone;
use Illuminate\Support\Facades\DB;

echo "=== إصلاح مناطق التغطية بدون Bounding Box ===\n\n";

// Use DB directly to bypass casting issues and check for NULL
$rawZones = DB::table('coverage_zones')
    ->whereNull('min_lat')
    ->whereNull('deleted_at') // NULL deleted_at = record exists (not soft-deleted)
    ->get();

echo "المناطق التي تحتاج إصلاح: " . $rawZones->count() . "\n\n";

foreach ($rawZones as $rawZone) {
    echo "🔧 Zone #{$rawZone->id} - {$rawZone->name}\n";
    
    // Parse geometry from DB (stored as string, possibly double-encoded)
    $geomRaw = $rawZone->geometry;
    
    // First decode
    $geom = json_decode($geomRaw, true);
    
    // If it's still a string (double-encoded), decode again
    if (is_string($geom)) {
        $geom = json_decode($geom, true);
    }
    
    if (!$geom || !isset($geom['coordinates'])) {
        echo "   ❌ Cannot parse geometry: $geomRaw\n\n";
        continue;
    }
    
    $coords = $geom['coordinates'];
    $ring = isset($coords[0][0]) && is_array($coords[0][0]) ? $coords[0] : $coords;
    
    if (empty($ring)) {
        echo "   ❌ Empty coordinates ring\n\n";
        continue;
    }
    
    $minLat = 90.0; $maxLat = -90.0;
    $minLng = 180.0; $maxLng = -180.0;
    
    foreach ($ring as $point) {
        $pLng = (float) $point[0];
        $pLat = (float) $point[1];
        if ($pLat < $minLat) $minLat = $pLat;
        if ($pLat > $maxLat) $maxLat = $pLat;
        if ($pLng < $minLng) $minLng = $pLng;
        if ($pLng > $maxLng) $maxLng = $pLng;
    }
    
    DB::table('coverage_zones')->where('id', $rawZone->id)->update([
        'min_lat' => $minLat,
        'max_lat' => $maxLat,
        'min_lng' => $minLng,
        'max_lng' => $maxLng,
    ]);
    
    echo "   ✅ محدّث: min_lat=$minLat max_lat=$maxLat min_lng=$minLng max_lng=$maxLng\n\n";
}

echo "✅ انتهى الإصلاح!\n";
