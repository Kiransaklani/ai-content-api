<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Http\Requests\AnalyzeRequest;
use App\Http\Resources\AnalysisResource;
use App\Jobs\AnalyzeContentJob;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MainController extends Controller
{
    public function analyze(AnalyzeRequest $request)
    {
        $analysis = Analysis::create([
            'content' => $request->content,
            'score' => 0,
            'feedback' => 'Processing...',
            'status' => 'pending',
            'user_id' => auth()->id(),
        ]);

        AnalyzeContentJob::dispatch($analysis);

        return response()->json([
            'success' => true,
            'message' => 'Analysis queued. Check back shortly.',
            'data' => new AnalysisResource($analysis),
        ], 202);
    }

    public function analysisStatus($id)
    {
        $analysis = Analysis::where('user_id', auth()->id())->find($id);

        if (!$analysis) {
            return response()->json([
                'success' => false,
                'message' => 'Analysis not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new AnalysisResource($analysis),
        ]);
    }

    public function index(Request $request)
    {
        $query = Analysis::where('user_id', auth()->id());

        if ($request->filled('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $status = strtolower($request->status);
            $query->when($status === 'excellent', fn($q) => $q->where('score', '>=', 80))
                  ->when($status === 'good', fn($q) => $q->whereBetween('score', [60, 79]))
                  ->when($status === 'needs_work', fn($q) => $q->whereBetween('score', [40, 59]))
                  ->when($status === 'poor', fn($q) => $q->where('score', '<', 40));
        }

        if ($request->filled('min_score')) {
            $query->where('score', '>=', (int) $request->min_score);
        }

        $sortBy = in_array($request->sort, ['created_at', 'score']) ? $request->sort : 'created_at';
        $order = in_array($request->order, ['asc', 'desc']) ? $request->order : 'desc';
        $query->orderBy($sortBy, $order);

        $perPage = (int) ($request->per_page ?? 10);
        $perPage = min(max($perPage, 1), 100);

        $results = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => AnalysisResource::collection($results->items()),
            'meta' => [
                'page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'last_page' => $results->lastPage(),
            ]
        ]);
    }

    public function dashboardSummary()
    {
        $userId = auth()->id();
        $total = Analysis::where('user_id', $userId)->count();
        $avgScore = Analysis::where('user_id', $userId)->avg('score');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'avg_score' => round((float) $avgScore, 2),
            ]
        ]);
    }

    public function reports(Request $request)
    {
        $query = Analysis::where('user_id', auth()->id());

        if ($request->filled('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $status = strtolower($request->status);
            $query->when($status === 'excellent', fn($q) => $q->where('score', '>=', 80))
                  ->when($status === 'good', fn($q) => $q->whereBetween('score', [60, 79]))
                  ->when($status === 'needs_work', fn($q) => $q->whereBetween('score', [40, 59]))
                  ->when($status === 'poor', fn($q) => $q->where('score', '<', 40));
        }

        if ($request->filled('min_score')) {
            $query->where('score', '>=', (int) $request->min_score);
        }

        $sortBy = in_array($request->sort, ['created_at', 'score']) ? $request->sort : 'created_at';
        $order = in_array($request->order, ['asc', 'desc']) ? $request->order : 'desc';
        $query->orderBy($sortBy, $order);

        $perPage = (int) ($request->per_page ?? 10);
        $perPage = min(max($perPage, 1), 100);

        $results = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => AnalysisResource::collection($results->items()),
            'meta' => [
                'page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'last_page' => $results->lastPage(),
            ]
        ]);
    }

    public function apiUsage()
    {
        $userId = auth()->id();
        $total = Analysis::where('user_id', $userId)->count();
        $avgScore = round((float) Analysis::where('user_id', $userId)->avg('score'), 2);

        $dailyUsage = [];
        for ($i = 6; $i >= 0; $i--) {
            $carbonDate = Carbon::today()->subDays($i);
            $count = Analysis::where('user_id', $userId)
                ->whereDate('created_at', $carbonDate->toDateString())
                ->count();
            $dailyUsage[] = [
                'date' => $carbonDate->toDateString(),
                'day' => $carbonDate->format('D'),
                'count' => $count,
            ];
        }

        $excellent = Analysis::where('user_id', $userId)->where('score', '>=', 80)->count();
        $good = Analysis::where('user_id', $userId)->whereBetween('score', [60, 79])->count();
        $needsWork = Analysis::where('user_id', $userId)->whereBetween('score', [40, 59])->count();
        $poor = Analysis::where('user_id', $userId)->where('score', '<', 40)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_analyses' => $total,
                'avg_score' => $avgScore,
                'daily_usage' => $dailyUsage,
                'score_breakdown' => [
                    'excellent' => $excellent,
                    'good' => $good,
                    'needs_work' => $needsWork,
                    'poor' => $poor,
                ],
            ]
        ]);
    }

    public function deleteReport($id)
    {
        $analysis = Analysis::where('user_id', auth()->id())->find($id);

        if (!$analysis) {
            return response()->json([
                'success' => false,
                'message' => 'Report not found.',
            ], 404);
        }

        $analysis->delete();

        return response()->json([
            'success' => true,
            'message' => 'Report deleted successfully.',
        ]);
    }

    public function analysisScores()
    {
        $scores = Analysis::where('score', 82)->limit(10)->get(['id', 'content', 'score', 'feedback', 'suggestions']);

        return response()->json([
            'success' => true,
            'data' => $scores,
        ]);
    }
}
