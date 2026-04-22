<?php 
namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ApiTestRun;
use App\Models\ApiTestResult;
use App\Models\Endpoint;
use Carbon\Carbon;

class TestRunnerService
{
    public function runSuite($suite)
    {
        $suite->loadMissing('endpoints.testCases');

        $run = ApiTestRun::create([
            'suite_id'   => $suite->id,
            'started_at' => now(),
        ]);

        $passed = 0;
        $failed = 0;

        foreach ($suite->endpoints as $endpoint) {
            foreach ($endpoint->testCases as $case) {
                $url = rtrim($suite->base_url, '/') . '/' . ltrim($endpoint->path, '/');

                $http = Http::acceptJson();

                if ($endpoint->requires_auth) {
                    $endpointToken = $this->resolveBearerToken($suite, $endpoint);

                    if (!empty($endpointToken)) {
                        $http = $http->withToken($endpointToken);
                    }
                }

                $response = $http->send(
                    $endpoint->method,
                    $url,
                    [
                        'json' => $case->request_payload
                    ]
                );

                $responseJson = $response->json();

                $isPassed = $this->compare(
                    $response->status(),
                    $responseJson,
                    $case->expected_status,
                    $case->expected_response
                );

                $isPassed ? $passed++ : $failed++;

                ApiTestResult::create([
                    'run_id'          => $run->id,
                    'test_case_id'    => $case->id,
                    'passed'          => $isPassed,
                    'status_received' => $response->status(),
                    'response_body'   => $responseJson,
                ]);
            }
        }

        $run->update([
            'finished_at'  => now(),
            'total_tests'  => $passed + $failed,
            'passed'       => $passed,
            'failed'       => $failed,
        ]);

        return $run;
    }

    /**
     * Comparação simples (evolui depois)
     */
    private function compare($actualStatus, $actualBody, $expectedStatus, $expectedBody)
    {
        if ((int) $actualStatus !== (int) $expectedStatus) {
            return false;
        }

        if (empty($expectedBody)) {
            return true;
        }

        foreach ($expectedBody as $key => $value) {
            if (data_get($actualBody, $key) != $value) {
                return false;
            }
        }

        return true;
    }

    private function resolveBearerToken($suite, Endpoint $endpoint): ?string
    {
        $endpoint->refresh();

        if ($this->isStoredTokenStillValid($suite, $endpoint)) {
            return $endpoint->bearer_token;
        }

        return $this->requestAndPersistNewToken($suite, $endpoint);
    }

    private function isStoredTokenStillValid($suite, Endpoint $endpoint): bool
    {
        if (empty($endpoint->bearer_token)) {
            return false;
        }

        if ($endpoint->bearer_token_expires_at instanceof Carbon) {
            if ($endpoint->bearer_token_expires_at->isFuture()) {
                return true;
            }

            return false;
        }

        $jwtExpiration = $this->extractJwtExpiration($endpoint->bearer_token);

        if ($jwtExpiration) {
            $endpoint->update(['bearer_token_expires_at' => $jwtExpiration]);

            return $jwtExpiration->isFuture();
        }

        if (!empty($endpoint->auth_validate_path)) {
            try {
                $url = $this->buildUrl($suite->base_url, $endpoint->auth_validate_path);
                $method = strtoupper($endpoint->auth_validate_method ?? 'GET');
                $expectedStatus = (int) ($endpoint->auth_validate_status ?? 200);

                $response = Http::acceptJson()
                    ->withToken($endpoint->bearer_token)
                    ->send($method, $url);

                return $response->status() === $expectedStatus;
            } catch (\Throwable $e) {
                return false;
            }
        }

        return true;
    }

    private function requestAndPersistNewToken($suite, Endpoint $endpoint): ?string
    {
        if (empty($endpoint->auth_login_path)) {
            return $endpoint->bearer_token;
        }

        $url = $this->buildUrl($suite->base_url, $endpoint->auth_login_path);
        $method = strtoupper($endpoint->auth_login_method ?? 'POST');
        $payload = $endpoint->auth_payload ?? [];

        $options = [];

        if (in_array($method, ['GET', 'DELETE'], true)) {
            $options['query'] = $payload;
        } else {
            $options['json'] = $payload;
        }

        $response = Http::acceptJson()->send($method, $url, $options);
        $tokenPath = $endpoint->auth_token_path ?: 'token';
        $token = data_get($response->json(), $tokenPath);

        if (empty($token)) {
            return null;
        }

        $endpoint->update([
            'bearer_token' => $token,
            'bearer_token_expires_at' => $this->extractJwtExpiration($token),
        ]);

        return $token;
    }

    private function buildUrl(string $baseUrl, string $path): string
    {
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    private function extractJwtExpiration(?string $token): ?Carbon
    {
        if (empty($token) || substr_count($token, '.') < 2) {
            return null;
        }

        $parts = explode('.', $token);
        $payload = $parts[1] ?? null;

        if (empty($payload)) {
            return null;
        }

        $normalized = strtr($payload, '-_', '+/');
        $padding = strlen($normalized) % 4;

        if ($padding > 0) {
            $normalized .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($normalized, true);

        if ($decoded === false) {
            return null;
        }

        $data = json_decode($decoded, true);

        if (!is_array($data) || empty($data['exp'])) {
            return null;
        }

        return Carbon::createFromTimestamp((int) $data['exp']);
    }
}