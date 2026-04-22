<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\ApiTestRun;
use App\Models\ApiTestSuite;

class TestRunController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'suite' => (string) $request->string('suite'),
            'status' => (string) $request->string('status'),
            'startDate' => (string) $request->string('startDate'),
            'endDate' => (string) $request->string('endDate'),
        ];

        $baseQuery = ApiTestRun::with('suite');
        $this->applyFilters($baseQuery, $filters);

        $summaryQuery = clone $baseQuery;

        $runsPaginator = $baseQuery
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $runsPaginator->getCollection()->transform(function ($run) {
            return [
                'id' => $run->id,
                'suite_id' => $run->suite?->id,
                'suite_name' => $run->suite?->name ?? 'Suite removida',
                'created_at' => optional($run->created_at)->format('d/m/Y H:i:s'),
                'created_at_iso' => optional($run->created_at)?->toISOString(),
                'total_tests' => (int) $run->total_tests,
                'passed' => (int) $run->passed,
                'failed' => (int) $run->failed,
                'success' => (int) $run->failed === 0,
            ];
        });

        $summary = [
            'runs_total' => (clone $summaryQuery)->count(),
            'runs_success' => (clone $summaryQuery)->where('failed', 0)->count(),
            'runs_failed' => (clone $summaryQuery)->where('failed', '>', 0)->count(),
            'tests_total' => (int) (clone $summaryQuery)->sum('total_tests'),
            'tests_passed' => (int) (clone $summaryQuery)->sum('passed'),
            'tests_failed' => (int) (clone $summaryQuery)->sum('failed'),
        ];

        $latestBySuite = (clone $summaryQuery)
            ->latest()
            ->get()
            ->groupBy('suite_id')
            ->map(function ($items) {
                $latest = $items->first();

                return [
                    'suite_name' => $latest->suite?->name ?? 'Suite removida',
                    'failed' => (int) $latest->failed,
                    'total_tests' => (int) $latest->total_tests,
                    'created_at' => optional($latest->created_at)->format('d/m/Y H:i:s'),
                ];
            })
            ->values()
            ->take(10);

        $trendRaw = (clone $summaryQuery)
            ->select(['created_at', 'failed'])
            ->latest()
            ->get()
            ->filter(fn ($run) => (int) $run->failed > 0)
            ->groupBy(fn ($run) => optional($run->created_at)->format('Y-m-d'))
            ->sortKeys()
            ->take(-14);

        $trendData = $trendRaw->map(function ($items, $date) {
            return [
                'date' => $date,
                'label' => \Carbon\Carbon::parse($date)->format('d/m/Y'),
                'shortLabel' => \Carbon\Carbon::parse($date)->format('d/m'),
                'failedRuns' => $items->count(),
            ];
        })->values();

        $maxFailedRuns = max(1, (int) $trendData->max('failedRuns'));

        $trendData = $trendData->map(function ($point) use ($maxFailedRuns) {
            $point['height'] = max(6, (int) round(($point['failedRuns'] / $maxFailedRuns) * 84));

            return $point;
        });

        $suiteOptions = ApiTestSuite::query()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        return Inertia::render('test-orchestrator/pages/TestRunDashboard', [
            'summary' => $summary,
            'runs' => $runsPaginator,
            'latestBySuite' => $latestBySuite,
            'suiteOptions' => $suiteOptions,
            'filters' => $filters,
            'trendData' => $trendData,
        ]);
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['suite'])) {
            $query->whereHas('suite', function ($suiteQuery) use ($filters) {
                $suiteQuery->where('name', $filters['suite']);
            });
        }

        if ($filters['status'] === 'success') {
            $query->where('failed', 0);
        }

        if ($filters['status'] === 'failed') {
            $query->where('failed', '>', 0);
        }

        if (!empty($filters['startDate'])) {
            $query->whereDate('created_at', '>=', $filters['startDate']);
        }

        if (!empty($filters['endDate'])) {
            $query->whereDate('created_at', '<=', $filters['endDate']);
        }
    }

    public function show(ApiTestRun $run)
    {
        $run->load([
            'suite',
            'results.environment',
            'results.testCase.endpoint'
        ]);

        return Inertia::render('test-orchestrator/pages/TestRunShow', [
            'run' => $run
        ]);
    }
}