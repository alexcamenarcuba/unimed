<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ApiTestSuite;
use App\Models\ApiTestCase;
use App\Services\TestRunnerService;
use Illuminate\Http\Request;

class TestSuiteController extends Controller
{
    public function index()
    {
        $hasSuiteId = Schema::hasColumn('api_test_runs', 'suite_id');

        $suites = ApiTestSuite::withCount('cases')
            ->latest()
            ->get()
            ->map(function ($suite) use ($hasSuiteId) {
                $lastStatus = null;

                if ($hasSuiteId) {
                    $lastRun = DB::table('api_test_runs')
                        ->where('suite_id', $suite->id)
                        ->latest()
                        ->first();

                    if ($lastRun) {
                        $lastStatus = (int) $lastRun->failed === 0;
                    }
                }
            
                return [
                    'id'          => $suite->id,
                    'name'        => $suite->name,
                    'base_url'    => $suite->base_url,
                    'cases_count' => $suite->cases_count,
                    'last_status' => $lastStatus,
                ];
            });

        return Inertia::render('test-orchestrator/pages/TestSuiteList', [
            'suites' => $suites,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'base_url' => 'required|string|max:500',
        ]);

        $suite = ApiTestSuite::create($request->only('name', 'base_url'));

        return redirect("/test-suites/{$suite->id}");
    }

    public function show(ApiTestSuite $suite)
    {
        $suite->load('cases');

        $resultCaseForeignKey = Schema::hasColumn('api_test_results', 'test_case_id')
            ? 'test_case_id'
            : 'api_test_case_id';

        $latestResults = DB::table('api_test_results as r')
            ->select(
                "r.{$resultCaseForeignKey} as case_id",
                'r.passed',
                'r.response_body'
            )
            ->join(
                DB::raw("(
            SELECT {$resultCaseForeignKey} as case_id, MAX(created_at) as latest_created_at
            FROM api_test_results
            GROUP BY {$resultCaseForeignKey}
        ) as lr"),
                function ($join) use ($resultCaseForeignKey) {
                    $join->on("r.{$resultCaseForeignKey}", '=', 'lr.case_id')
                        ->on('r.created_at', '=', 'lr.latest_created_at');
                }
            )
            ->get()
            ->keyBy('case_id');

        $suite->setRelation('cases', $suite->cases->map(function ($case) use ($latestResults) {
            $result = $latestResults->get($case->id);

            $case->last_result = $result ? (bool) $result->passed : null;
            $case->response_body = $result->response_body ?? null;

            return $case;
        }));
        return Inertia::render('test-orchestrator/pages/TestSuiteShow', [
            'suite' => $suite
        ]);
    }

    public function createCase(ApiTestSuite $suite)
    {
        return Inertia::render('test-orchestrator/pages/TestCaseFormPage', [
            'suiteId' => $suite->id,
            'testCase' => null,
        ]);
    }

    public function editCase(ApiTestSuite $suite, ApiTestCase $case)
    {
        if ($case->suite_id !== $suite->id) {
            abort(404);
        }

        return Inertia::render('test-orchestrator/pages/TestCaseFormPage', [
            'suiteId' => $suite->id,
            'testCase' => $case,
        ]);
    }

    /**
     * Criar cenário
     */
    public function storeCase(Request $request, ApiTestSuite $suite)
    {
        ApiTestCase::create([
            'suite_id'          => $suite->id,
            'name'              => $request->name,
            'method'            => $request->method,
            'endpoint'          => $request->endpoint,
            'request_payload'   => $request->request_json,
            'expected_response' => $request->expected_json,
            'expected_status'   => $request->expected_status ?? 200,
        ]);

        return back();
    }

    public function updateCase(Request $request, ApiTestSuite $suite, ApiTestCase $apiCase)
    {
        $apiCase->update([
            'name'              => $request->name,
            'method'            => $request->method,
            'endpoint'          => $request->endpoint,
            'request_payload'   => $request->request_json,
            'expected_status'   => $request->expected_status ?? $suite->expected_status,
        ]);
        return response()->json(['success' => true]);
    }


    /**
     * Deletar cenário
     */
    public function deleteCase(ApiTestCase $case)
    {
        $case->delete();

        return back();
    }

    /**
     * Rodar testes
     */
    public function run(ApiTestSuite $suite, TestRunnerService $runner)
    {
        $run = $runner->runSuite($suite);

        return redirect("/test-runs/{$run->id}");
    }
}
