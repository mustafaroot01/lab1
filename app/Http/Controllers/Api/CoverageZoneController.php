<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoverageZone;
use App\Repositories\Coverage\CoverageRepository;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CoverageZones\StoreCoverageZoneRequest;
use App\Http\Requests\CoverageZones\UpdateCoverageZoneRequest;

class CoverageZoneController extends Controller
{
    public function index()
    {
        if (DB::getDriverName() === 'sqlite') {
            $zones = CoverageZone::orderBy('priority', 'desc')->get();
            $zones->map(function($zone) {
                $zone->geometry_json = $zone->geometry;
                return $zone;
            });
        } else {
            $zones = CoverageZone::select('*', DB::raw('ST_AsGeoJSON(geometry) as geometry_json'))
                ->orderBy('priority', 'desc')
                ->get();
        }
            
        return response()->json([
            'status' => true,
            'data' => $zones->map(function($zone) {
                $array = $zone->toArray();
                if (!empty($zone->geometry_json)) {
                    $array['geojson'] = is_string($zone->geometry_json) ? json_decode($zone->geometry_json, true) : $zone->geometry_json;
                }
                return $array;
            })
        ]);
    }

    public function store(StoreCoverageZoneRequest $request)
    {
        $validated = $request->validated();
        
        $data = collect($validated)->except(['geojson', 'center_lat', 'center_lng', 'radius_meters'])->toArray();
        
        DB::transaction(function () use ($validated, $data) {
            if (DB::getDriverName() === 'sqlite') {
                if ($validated['coverage_type'] === 'POLYGON') {
                    $data['geometry'] = json_encode($validated['geojson']);
                } else {
                    $data['geometry'] = "POINT({$validated['center_lng']} {$validated['center_lat']})";
                }
                CoverageZone::create($data);
            } else {
                // Insert with dummy point to satisfy NOT NULL constraint safely
                $data['geometry'] = DB::raw("ST_GeomFromText('POINT(0 0)')");
                $zone = CoverageZone::create($data);

                // Update with real geometry using safe bindings
                if ($validated['coverage_type'] === 'POLYGON') {
                    DB::statement('UPDATE coverage_zones SET geometry = ST_GeomFromGeoJSON(?) WHERE id = ?', [
                        json_encode($validated['geojson']), $zone->id
                    ]);
                } elseif ($validated['coverage_type'] === 'RADIUS') {
                    DB::statement('UPDATE coverage_zones SET geometry = ST_GeomFromText(?) WHERE id = ?', [
                        "POINT({$validated['center_lng']} {$validated['center_lat']})", $zone->id
                    ]);
                }
            }
        });
        
        app(CoverageRepository::class)->refreshCache();
        
        return response()->json(['status' => true, 'message' => 'تم حفظ منطقة التغطية بنجاح']);
    }

    public function update(UpdateCoverageZoneRequest $request, $id)
    {
        $zone = CoverageZone::findOrFail($id);
        $validated = $request->validated();
        
        $data = collect($validated)->except(['geojson', 'center_lat', 'center_lng', 'radius_meters'])->toArray();
        
        DB::transaction(function () use ($validated, $data, $zone) {
            if ($validated['coverage_type'] === 'POLYGON') {
                $data['radius_meters'] = null;
                $data['center_lat'] = null;
                $data['center_lng'] = null;
            } elseif ($validated['coverage_type'] === 'RADIUS') {
                $data['radius_meters'] = $validated['radius_meters'];
                $data['center_lat'] = $validated['center_lat'];
                $data['center_lng'] = $validated['center_lng'];
            }
            
            if (DB::getDriverName() === 'sqlite') {
                if ($validated['coverage_type'] === 'POLYGON') {
                    $data['geometry'] = json_encode($validated['geojson']);
                } else {
                    $data['geometry'] = "POINT({$validated['center_lng']} {$validated['center_lat']})";
                }
                $zone->update($data);
            } else {
                // Dummy geometry to satisfy Eloquent if it triggers a re-save of geometry
                $data['geometry'] = DB::raw("ST_GeomFromText('POINT(0 0)')");
                $zone->update($data);

                // Update with real geometry using safe bindings
                if ($validated['coverage_type'] === 'POLYGON') {
                    DB::statement('UPDATE coverage_zones SET geometry = ST_GeomFromGeoJSON(?) WHERE id = ?', [
                        json_encode($validated['geojson']), $zone->id
                    ]);
                } elseif ($validated['coverage_type'] === 'RADIUS') {
                    DB::statement('UPDATE coverage_zones SET geometry = ST_GeomFromText(?) WHERE id = ?', [
                        "POINT({$validated['center_lng']} {$validated['center_lat']})", $zone->id
                    ]);
                }
            }
        });
        
        app(CoverageRepository::class)->refreshCache();
        
        return response()->json(['status' => true, 'message' => 'تم تعديل منطقة التغطية بنجاح']);
    }

    public function destroy($id)
    {
        CoverageZone::findOrFail($id)->delete();
        app(CoverageRepository::class)->refreshCache();
        return response()->json(['status' => true, 'message' => 'تم حذف منطقة التغطية بنجاح']);
    }
}
