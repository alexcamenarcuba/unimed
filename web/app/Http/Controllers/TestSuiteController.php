<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ApiTestSuite;
use App\Models\ApiTestCase;
use App\Models\ApiTestEnvironment;
use App\Models\Endpoint;
use App\Services\TestRunnerService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TestSuiteController extends Controller
{
    public function create()
    {
        return Inertia::render('test-orchestrator/pages/TestSuiteFormPage', [
            'suite' => null,
        ]);
    }

    public function edit(ApiTestSuite $suite)
    {
        return Inertia::render('test-orchestrator/pages/TestSuiteFormPage', [
            'suite' => [
                'id' => $suite->id,
                'name' => $suite->name,
                'base_url' => $suite->base_url,
            ],
        ]);
    }

    public function index()
    {
        $hasSuiteId = Schema::hasColumn('api_test_runs', 'suite_id');

        $suites = ApiTestSuite::with('endpoints.testCases', 'environments')
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
                    'base_url'    => $suite->environments->first()->base_url ?? $suite->base_url,
                    'environments_count' => $suite->environments->count(),
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
        if ($request->has('environments')) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'base_path' => 'nullable|string|max:255',
                'environments' => 'required|array|min:1',
                'environments.*.name' => 'required|string|max:255',
                'environments.*.url' => 'required|string|max:500',
            ]);

            $basePath = $validated['base_path'] ?? '';
            $firstEnvironment = $validated['environments'][0];
            $suiteBaseUrl = $this->buildEnvironmentBaseUrl($firstEnvironment['url'], $basePath);

            $suite = ApiTestSuite::create([
                'name' => $validated['name'],
                'base_url' => $suiteBaseUrl,
            ]);

            foreach ($validated['environments'] as $environmentPayload) {
                $suite->environments()->create([
                    'name' => $environmentPayload['name'],
                    'base_url' => $this->buildEnvironmentBaseUrl($environmentPayload['url'], $basePath),
                    'is_active' => true,
                    'requires_auth' => false,
                    'auth_login_method' => 'POST',
                    'auth_token_path' => 'token',
                    'auth_validate_method' => 'GET',
                    'auth_validate_status' => 200,
                ]);
            }

            return redirect("/test-suites/{$suite->id}/endpoints/create");
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'base_url' => 'required|string|max:500',
        ]);

        $suite = ApiTestSuite::create($validated);

        $suite->environments()->create([
            'name' => 'Principal',
            'base_url' => $validated['base_url'],
            'is_active' => true,
            'requires_auth' => false,
            'auth_login_method' => 'POST',
            'auth_token_path' => 'token',
            'auth_validate_method' => 'GET',
            'auth_validate_status' => 200,
        ]);

        return redirect("/test-suites/{$suite->id}/environments/create");
    }

    public function update(Request $request, ApiTestSuite $suite)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_url' => 'required|string|max:500',
        ]);

        $suite->update($validated);

        return redirect("/test-suites/{$suite->id}");
    }

    public function createEnvironment(ApiTestSuite $suite)
    {
        return Inertia::render('test-orchestrator/pages/EnvironmentFormPage', [
            'suite' => [
                'id' => $suite->id,
                'name' => $suite->name,
                'base_url' => $suite->base_url,
            ],
            'environment' => null,
        ]);
    }

    public function storeEnvironment(Request $request, ApiTestSuite $suite)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_url' => 'required|string|max:500',
            'is_active' => 'nullable|boolean',
            'requires_auth' => 'nullable|boolean',
            'bearer_token' => 'nullable|string|max:4096',
            'auth_login_path' => 'nullable|string|max:255',
            'auth_login_method' => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'auth_payload' => 'nullable|array',
            'auth_token_path' => 'nullable|string|max:255',
            'auth_validate_path' => 'nullable|string|max:255',
            'auth_validate_method' => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'auth_validate_status' => 'nullable|integer|min:100|max:599',
            'bypass_header_name' => 'nullable|string|max:100',
            'bypass_header_value' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
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

        $environment = $suite->environments()->create($validated);

        return response()->json([
            'success' => true,
            'environment_id' => $environment->id,
        ]);
    }

    public function editEnvironment(ApiTestSuite $suite, ApiTestEnvironment $environment)
    {
        if ($environment->suite_id !== $suite->id) {
            abort(404);
        }

        return Inertia::render('test-orchestrator/pages/EnvironmentFormPage', [
            'suite' => [
                'id' => $suite->id,
                'name' => $suite->name,
                'base_url' => $suite->base_url,
            ],
            'environment' => [
                'id' => $environment->id,
                'name' => $environment->name,
                'base_url' => $environment->base_url,
                'is_active' => (bool) $environment->is_active,
                'requires_auth' => (bool) $environment->requires_auth,
                'has_bearer_token' => !empty($environment->bearer_token),
                'auth_login_path' => $environment->auth_login_path,
                'auth_login_method' => $environment->auth_login_method,
                'auth_payload' => $environment->auth_payload,
                'auth_token_path' => $environment->auth_token_path,
                'auth_validate_path' => $environment->auth_validate_path,
                'auth_validate_method' => $environment->auth_validate_method,
                'auth_validate_status' => $environment->auth_validate_status,
                'bypass_header_name' => $environment->bypass_header_name,
                'bypass_header_value' => $environment->bypass_header_value,
            ],
        ]);
    }

    public function updateEnvironment(Request $request, ApiTestSuite $suite, ApiTestEnvironment $environment)
    {
        if ($environment->suite_id !== $suite->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_url' => 'required|string|max:500',
            'is_active' => 'nullable|boolean',
            'requires_auth' => 'nullable|boolean',
            'bearer_token' => 'nullable|string|max:4096',
            'auth_login_path' => 'nullable|string|max:255',
            'auth_login_method' => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'auth_payload' => 'nullable|array',
            'auth_token_path' => 'nullable|string|max:255',
            'auth_validate_path' => 'nullable|string|max:255',
            'auth_validate_method' => 'nullable|string|in:GET,POST,PUT,PATCH,DELETE',
            'auth_validate_status' => 'nullable|integer|min:100|max:599',
            'bypass_header_name' => 'nullable|string|max:100',
            'bypass_header_value' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
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
            && empty($environment->bearer_token)
            && empty($validated['auth_login_path'])
        ) {
            throw ValidationException::withMessages([
                'auth_login_path' => 'Informe um endpoint de login ou um bearer token inicial.',
            ]);
        }

        $environment->update($validated);

        return response()->json(['success' => true]);
    }

    public function toggleEnvironmentStatus(Request $request, ApiTestSuite $suite, ApiTestEnvironment $environment)
    {
        if ($environment->suite_id !== $suite->id) {
            abort(404);
        }

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $newStatus = (bool) $validated['is_active'];

        if ($environment->is_active && !$newStatus) {
            $activeCount = $suite->environments()
                ->where('is_active', true)
                ->count();

            if ($activeCount <= 1) {
                throw ValidationException::withMessages([
                    'is_active' => 'A suite precisa ter ao menos um ambiente ativo.',
                ]);
            }
        }

        $environment->update([
            'is_active' => $newStatus,
        ]);

        return response()->json([
            'success' => true,
            'environment_id' => $environment->id,
            'is_active' => (bool) $environment->is_active,
        ]);
    }

    public function show(ApiTestSuite $suite)
    {
        $suite->load('endpoints.testCases', 'environments');
        $environments = $suite->environments->sortBy('created_at')->values();

        $resultCaseForeignKey = Schema::hasColumn('api_test_results', 'test_case_id')
            ? 'test_case_id'
            : 'api_test_case_id';

        $latestResultsByEnvironment = DB::table('api_test_results as r')
            ->select(
                "r.{$resultCaseForeignKey} as case_id",
                'r.environment_id',
                'r.passed',
                'r.response_body',
                'r.status_received',
                'r.created_at'
            )
            ->join(
                DB::raw("(
                    SELECT {$resultCaseForeignKey} as case_id, environment_id, MAX(created_at) as latest_created_at
                    FROM api_test_results
                    WHERE environment_id IS NOT NULL
                    GROUP BY {$resultCaseForeignKey}, environment_id
                ) as lr"),
                function ($join) use ($resultCaseForeignKey) {
                    $join->on("r.{$resultCaseForeignKey}", '=', 'lr.case_id')
                         ->on('r.environment_id', '=', 'lr.environment_id')
                         ->on('r.created_at', '=', 'lr.latest_created_at');
                }
            )
            ->get()
            ->groupBy('case_id')
            ->map(function ($items) {
                return $items->keyBy('environment_id');
            });

        $suite->endpoints->each(function ($endpoint) use ($latestResultsByEnvironment, $environments) {
            $endpoint->testCases->each(function ($case) use ($latestResultsByEnvironment, $endpoint, $environments) {
                $resultsByEnvironment = $latestResultsByEnvironment->get($case->id, collect());

                $case->environment_results = $environments->map(function ($environment) use ($resultsByEnvironment) {
                    $result = $resultsByEnvironment->get($environment->id);

                    return [
                        'environment_id' => $environment->id,
                        'environment_name' => $environment->name,
                        'last_result' => $result ? (bool) $result->passed : null,
                        'status_received' => $result->status_received ?? null,
                        'response_body' => $result->response_body ?? null,
                        'executed_at' => isset($result->created_at)
                            ? \Carbon\Carbon::parse($result->created_at)->format('d/m/Y H:i:s')
                            : null,
                    ];
                })->values();

                $executedResults = collect($case->environment_results)->filter(function ($item) {
                    return $item['last_result'] !== null;
                });

                $case->passed_environments = $executedResults->where('last_result', true)->count();
                $case->failed_environments = $executedResults->where('last_result', false)->count();
                $case->last_result = $executedResults->isEmpty()
                    ? null
                    : $executedResults->every(function ($item) {
                        return $item['last_result'] === true;
                    });
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
            'suite' => $suite,
            'environments' => $environments
                ->map(function ($environment) {
                    return [
                        'id' => $environment->id,
                        'name' => $environment->name,
                        'base_url' => $environment->base_url,
                        'is_active' => $environment->is_active,
                        'requires_auth' => $environment->requires_auth,
                    ];
                })
                ->values(),
        ]);
    }

    public function createEndpoint(ApiTestSuite $suite)
    {
        return Inertia::render('test-orchestrator/pages/EndpointFormPage', [
            'suite' => [
                'id' => $suite->id,
                'name' => $suite->name,
            ],
            'environments' => $suite->environments()
                ->orderBy('created_at')
                ->get(['id', 'name']),
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
            'variables' => 'nullable|array',
            'variables.*.key' => 'required|string|max:255',
            'variables.*.type' => 'required|string|in:simple,array,file',
            'variables.*.values' => 'nullable|array',
        ]);

        $validated['requires_auth'] = (bool) ($validated['requires_auth'] ?? false);
        $validated['variables'] = array_values($validated['variables'] ?? []);

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
            'environments' => $suite->environments()
                ->orderBy('created_at')
                ->get(['id', 'name']),
            'endpoint' => [
                'id' => $endpoint->id,
                'name' => $endpoint->name,
                'method' => $endpoint->method,
                'path' => $endpoint->path,
                'requires_auth' => (bool) $endpoint->requires_auth,
                'variables' => $endpoint->variables ?? [],
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
            'variables' => 'nullable|array',
            'variables.*.key' => 'required|string|max:255',
            'variables.*.type' => 'required|string|in:simple,array,file',
            'variables.*.values' => 'nullable|array',
        ]);

        $validated['requires_auth'] = (bool) ($validated['requires_auth'] ?? false);
        $validated['variables'] = array_values($validated['variables'] ?? []);

        $endpoint->update($validated);

        return response()->json(['success' => true]);
    }

    public function createCase(ApiTestSuite $suite)
    {
        return Inertia::render('test-orchestrator/pages/TestCaseFormPage', [
            'suiteId' => $suite->id,
            'testCase' => null,
            'environments' => $suite->environments()
                ->orderBy('created_at')
                ->get(['id', 'name']),
            'endpoints' => $suite->endpoints()
                ->orderBy('created_at')
                ->get(['id', 'name', 'method', 'path', 'variables']),
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
            'environments' => $suite->environments()
                ->orderBy('created_at')
                ->get(['id', 'name']),
            'endpoints' => $suite->endpoints()
                ->orderBy('created_at')
                ->get(['id', 'name', 'method', 'path', 'variables']),
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
            'variable_overrides' => 'nullable|array',
            'grupo' => 'nullable|string|max:255',
        ]);

        $endpoint = Endpoint::where('suite_id', $suite->id)
            ->where('id', $validated['endpoint_id'])
            ->firstOrFail();

        $endpoint->testCases()->create([
            'name'              => $validated['name'],
            'grupo'             => $validated['grupo'] ?? null,
            'request_payload'   => $validated['request_json'] ?? null,
            'variable_overrides' => $this->sanitizeVariableOverrides($validated['variable_overrides'] ?? []),
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
            'variable_overrides' => 'nullable|array',
            'grupo' => 'nullable|string|max:255',
        ]);

        Endpoint::where('suite_id', $suite->id)
            ->where('id', $validated['endpoint_id'])
            ->firstOrFail();

        $apiCase->update([
            'endpoint_id'       => $validated['endpoint_id'],
            'name'              => $validated['name'],
            'grupo'             => $validated['grupo'] ?? null,
            'request_payload'   => $validated['request_json'] ?? null,
            'variable_overrides' => $this->sanitizeVariableOverrides($validated['variable_overrides'] ?? []),
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
        $validated = request()->validate([
            'test_case_ids' => 'nullable|array|min:1',
            'test_case_ids.*' => 'uuid',
        ]);

        $selectedCaseIds = collect($validated['test_case_ids'] ?? [])
            ->filter()
            ->values();

        if ($selectedCaseIds->isNotEmpty()) {
            $validCaseIds = $suite->endpoints()
                ->with('testCases:id,endpoint_id')
                ->get()
                ->flatMap(function ($endpoint) {
                    return $endpoint->testCases->pluck('id');
                })
                ->intersect($selectedCaseIds)
                ->values()
                ->all();

            $selectedCaseIds = collect($validCaseIds);
        }

        $run = $runner->runSuite($suite, $selectedCaseIds->all());

        return redirect("/test-runs/{$run->id}");
    }

    private function buildEnvironmentBaseUrl(string $url, string $basePath): string
    {
        $normalizedUrl = rtrim($url, '/');
        $normalizedBasePath = trim($basePath);

        if ($normalizedBasePath === '') {
            return $normalizedUrl;
        }

        return $normalizedUrl . '/' . ltrim($normalizedBasePath, '/');
    }

    private function sanitizeVariableOverrides(array $overrides): array
    {
        if ($this->isFlatOverrideMap($overrides)) {
            return $this->sanitizeFlatOverrideMap($overrides);
        }

        $sanitized = [];

        foreach ($overrides as $environmentId => $environmentOverrides) {
            if (!is_array($environmentOverrides)) {
                continue;
            }

            $filtered = $this->sanitizeFlatOverrideMap($environmentOverrides);

            if (empty($filtered)) {
                continue;
            }

            $sanitized[$environmentId] = $filtered;
        }

        return $sanitized;
    }

    private function sanitizeFlatOverrideMap(array $overrides): array
    {
        $sanitized = [];

        foreach ($overrides as $key => $value) {
            if ($value === null) {
                continue;
            }

            // Permite string vazia intencional para validar obrigatoriedade.
            if ($value === '') {
                $sanitized[$key] = '';
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    private function isFlatOverrideMap(array $overrides): bool
    {
        if (empty($overrides)) {
            return true;
        }

        foreach ($overrides as $key => $value) {
            if (!$this->isEnvironmentOverrideKey((string) $key)) {
                return true;
            }
        }

        return false;
    }

    private function isEnvironmentOverrideKey(string $key): bool
    {
        if ($key === '__default') {
            return true;
        }

        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $key
        );
    }
}