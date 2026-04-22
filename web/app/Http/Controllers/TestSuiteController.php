<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ApiTestSuite;
use App\Models\ApiTestCase;
use App\Models\Endpoint;
use App\Services\TestRunnerService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TestSuiteController extends Controller
{
    public function create()
    {
        return Inertia::render('test-orchestrator/pages/TestSuiteFormPage');
    }

    public function index()
    {
        $hasSuiteId = Schema::hasColumn('api_test_runs', 'suite_id');

        $suites = ApiTestSuite::with('endpoints.testCases')
            ->latest()
            ->get()
            ->map(function ($suite) use ($hasSuiteId) {

                $casesCount = $suite->endpoints->sum(function ($endpoint) {
                    return $endpoint->testCases->count();
                });

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
                    'cases_count' => $casesCount,
                    'last_status' => $lastStatus,
                ];
            });

        return Inertia::render('test-orchestrator/pages/TestSuiteList', [
            'suites' => $suites,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'base_url' => 'required|string|max:500',
        ]);

        $suite = ApiTestSuite::create($validated);

        return redirect("/test-suites/{$suite->id}/endpoints/create");
    }

    public function show(ApiTestSuite $suite)
    {
        $suite->load('endpoints.testCases');

        $resultCaseForeignKey = Schema::hasColumn('api_test_results', 'test_case_id')
            ? 'test_case_id'
            : 'api_test_case_id';

        $latestResults = DB::table('api_test_results as r')
            ->select(
                "r.{$resultCaseForeignKey} as case_id",
                'r.passed',
                'r.response_body',
                'r.status_received'
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

        $suite->endpoints->each(function ($endpoint) use ($latestResults) {
            $endpoint->testCases->each(function ($case) use ($latestResults, $endpoint) {
                $result = $latestResults->get($case->id);

                $case->last_result = $result ? (bool) $result->passed : null;
                $case->response_body = $result->response_body ?? null;
                $case->status_received = $result->status_received ?? null;
                $case->endpoint = "{$endpoint->method} {$endpoint->path}";
                $case->endpoint_id = $endpoint->id;
            });
        });

        $suite->setRelation('cases', $suite->endpoints
            ->flatMap(function ($endpoint) {
                return $endpoint->testCases;
            })
            ->values());

        return Inertia::render('test-orchestrator/pages/TestSuiteShow', [
            'suite' => $suite
        ]);
    }

    public function createEndpoint(ApiTestSuite $suite)
    {
        return Inertia::render('test-orchestrator/pages/EndpointFormPage', [
            'suite' => [
                'id' => $suite->id,
                'name' => $suite->name,
            ],
            'endpoint' => null,
        ]);
    }

    public function storeEndpoint(Request $request, ApiTestSuite $suite)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'method' => 'required|string|max:10',
            'path' => 'required|string|max:255',
            'requires_auth' => 'nullable|boolean',
            'bearer_token' => 'nullable|string|max:4096',
            'auth_login_path' => 'nullable|string|max:255',
            'auth_login_method' => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'auth_payload' => 'nullable|array',
            'auth_token_path' => 'nullable|string|max:255',
            'auth_validate_path' => 'nullable|string|max:255',
            'auth_validate_method' => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'auth_validate_status' => 'nullable|integer|min:100|max:599',
        ]);

        $validated['requires_auth'] = (bool) ($validated['requires_auth'] ?? false);
        $validated['auth_login_method'] = $validated['auth_login_method'] ?? 'POST';
        $validated['auth_token_path'] = $validated['auth_token_path'] ?? 'token';
        $validated['auth_validate_method'] = $validated['auth_validate_method'] ?? 'GET';
        $validated['auth_validate_status'] = $validated['auth_validate_status'] ?? 200;

        if (
            $validated['requires_auth']
            && empty($validated['bearer_token'])
            && empty($validated['auth_login_path'])
        ) {
            throw ValidationException::withMessages([
                'auth_login_path' => 'Informe um endpoint de login ou um bearer token inicial.',
            ]);
        }

        $endpoint = $suite->endpoints()->create($validated);

        return response()->json([
            'success' => true,
            'endpoint_id' => $endpoint->id,
        ]);
    }

    public function editEndpoint(ApiTestSuite $suite, Endpoint $endpoint)
    {
        if ($endpoint->suite_id !== $suite->id) {
            abort(404);
        }

        return Inertia::render('test-orchestrator/pages/EndpointFormPage', [
            'suite' => [
                'id' => $suite->id,
                'name' => $suite->name,
            ],
            'endpoint' => [
                'id' => $endpoint->id,
                'name' => $endpoint->name,
                'method' => $endpoint->method,
                'path' => $endpoint->path,
                'requires_auth' => (bool) $endpoint->requires_auth,
                'has_bearer_token' => !empty($endpoint->bearer_token),
                'auth_login_path' => $endpoint->auth_login_path,
                'auth_login_method' => $endpoint->auth_login_method,
                'auth_payload' => $endpoint->auth_payload,
                'auth_token_path' => $endpoint->auth_token_path,
                'auth_validate_path' => $endpoint->auth_validate_path,
                'auth_validate_method' => $endpoint->auth_validate_method,
                'auth_validate_status' => $endpoint->auth_validate_status,
            ],
        ]);
    }

    public function updateEndpoint(Request $request, ApiTestSuite $suite, Endpoint $endpoint)
    {
        if ($endpoint->suite_id !== $suite->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'method' => 'required|string|max:10',
            'path' => 'required|string|max:255',
            'requires_auth' => 'nullable|boolean',
            'bearer_token' => 'nullable|string|max:4096',
            'auth_login_path' => 'nullable|string|max:255',
            'auth_login_method' => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'auth_payload' => 'nullable|array',
            'auth_token_path' => 'nullable|string|max:255',
            'auth_validate_path' => 'nullable|string|max:255',
            'auth_validate_method' => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'auth_validate_status' => 'nullable|integer|min:100|max:599',
        ]);

        $validated['requires_auth'] = (bool) ($validated['requires_auth'] ?? false);
        $validated['auth_login_method'] = $validated['auth_login_method'] ?? 'POST';
        $validated['auth_token_path'] = $validated['auth_token_path'] ?? 'token';
        $validated['auth_validate_method'] = $validated['auth_validate_method'] ?? 'GET';
        $validated['auth_validate_status'] = $validated['auth_validate_status'] ?? 200;

        if (!$request->filled('bearer_token')) {
            unset($validated['bearer_token']);
        }

        if (
            $validated['requires_auth']
            && empty($validated['bearer_token'])
            && empty($endpoint->bearer_token)
            && empty($validated['auth_login_path'])
        ) {
            throw ValidationException::withMessages([
                'auth_login_path' => 'Informe um endpoint de login ou um bearer token inicial.',
            ]);
        }

        $endpoint->update($validated);

        return response()->json(['success' => true]);
    }

    public function createCase(ApiTestSuite $suite)
    {
        return Inertia::render('test-orchestrator/pages/TestCaseFormPage', [
            'suiteId' => $suite->id,
            'testCase' => null,
            'endpoints' => $suite->endpoints()
                ->orderBy('created_at')
                ->get(['id', 'name', 'method', 'path']),
            'initialEndpointId' => request()->query('endpoint_id'),
        ]);
    }

    public function editCase(ApiTestSuite $suite, ApiTestCase $case)
    {
        if ($case->endpoint->suite_id !== $suite->id) {
            abort(404);
        }

        return Inertia::render('test-orchestrator/pages/TestCaseFormPage', [
            'suiteId' => $suite->id,
            'testCase' => $case,
            'endpoints' => $suite->endpoints()
                ->orderBy('created_at')
                ->get(['id', 'name', 'method', 'path']),
            'initialEndpointId' => null,
        ]);
    }

    public function storeCase(Request $request, ApiTestSuite $suite)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'endpoint_id' => 'required|uuid|exists:endpoints,id',
            'expected_status' => 'nullable|integer',
            'request_json' => 'nullable|array',
            'expected_json' => 'nullable|array',
        ]);

        $endpoint = Endpoint::where('suite_id', $suite->id)
            ->where('id', $validated['endpoint_id'])
            ->firstOrFail();

        $endpoint->testCases()->create([
            'name'              => $validated['name'],
            'request_payload'   => $validated['request_json'] ?? null,
            'expected_response' => $validated['expected_json'] ?? null,
            'expected_status'   => $validated['expected_status'] ?? 200,
        ]);

        return back();
    }

    public function updateCase(Request $request, ApiTestSuite $suite, ApiTestCase $apiCase)
    {
        if ($apiCase->endpoint->suite_id !== $suite->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'endpoint_id' => 'required|uuid|exists:endpoints,id',
            'expected_status' => 'nullable|integer',
            'request_json' => 'nullable|array',
            'expected_json' => 'nullable|array',
        ]);

        Endpoint::where('suite_id', $suite->id)
            ->where('id', $validated['endpoint_id'])
            ->firstOrFail();

        $apiCase->update([
            'endpoint_id'       => $validated['endpoint_id'],
            'name'              => $validated['name'],
            'request_payload'   => $validated['request_json'] ?? null,
            'expected_response' => $validated['expected_json'] ?? null,
            'expected_status'   => $validated['expected_status'] ?? 200,
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteCase(ApiTestCase $case)
    {
        $case->delete();

        return back();
    }

    public function run(ApiTestSuite $suite, TestRunnerService $runner)
    {
        $run = $runner->runSuite($suite);

        return redirect("/test-runs/{$run->id}");
    }
}