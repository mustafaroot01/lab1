<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CoverageLogController extends Controller
{
    public function dashboardKpis()
    {
        $today = Carbon::today();
        
        $zonesTotal = DB::table('coverage_zones')->count();
        $zonesActive = DB::table('coverage_zones')->where('status', 'ACTIVE')->count();
        $zonesInactive = DB::table('coverage_zones')->where('status', 'INACTIVE')->count();

        $logsToday = DB::table('coverage_verification_logs')
            ->whereDate('created_at', $today)
            ->get();

        $success = $logsToday->where('is_covered', 1)->count();
        $rejected = $logsToday->where('is_covered', 0)->count();
        $grace = $logsToday->where('match_type', 'grace_distance')->count();
        
        $avgTime = $logsToday->avg('execution_time_ms');

        $latestErrors = DB::table('coverage_verification_logs')
            ->where('is_covered', 0)
            ->orWhere('execution_time_ms', '>', 50)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'zones' => [
                    'total' => $zonesTotal,
                    'active' => $zonesActive,
                    'inactive' => $zonesInactive,
                ],
                'today' => [
                    'success' => $success,
                    'rejected' => $rejected,
                    'grace_matches' => $grace,
                    'avg_execution_ms' => round($avgTime, 2),
                ],
                'latest_issues' => $latestErrors
            ]
        ]);
    }

    public function index(Request $request)
    {
        $query = DB::table('coverage_verification_logs')->orderBy('id', 'desc');

        if ($request->filled('status')) {
            if ($request->status === 'COVERED') $query->where('is_covered', 1);
            if ($request->status === 'NOT_COVERED') $query->where('is_covered', 0);
        }

        if ($request->filled('match_type')) {
            $query->where('match_type', $request->match_type);
        }

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }
        
        if ($request->filled('is_slow')) {
            if (filter_var($request->is_slow, FILTER_VALIDATE_BOOLEAN)) {
                $query->where('execution_time_ms', '>', 30);
            }
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->date_from)->startOfDay(),
                Carbon::parse($request->date_to)->endOfDay()
            ]);
        }

        $logs = $query->paginate(20);

        return response()->json([
            'status' => true,
            'data' => $logs
        ]);
    }
}
