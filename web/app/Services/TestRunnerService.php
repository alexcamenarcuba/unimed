<?php 
namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ApiTestRun;
use App\Models\ApiTestResult;
use App\Models\ApiTestEnvironment;
use Carbon\Carbon;

class TestRunnerService
{
    public function runSuite($suite)
    {
        $suite->loadMissing('endpoints.testCases', 'environments');

        $run = ApiTestRun::create([
            'suite_id'   => $suite->id,
            'started_at' => now(),
        ]);

        $passed = 0;
        $failed = 0;
        $environments = $suite->environments->where('is_active', true)->values();

        if ($environments->isEmpty() && !empty($suite->base_url)) {
            $fallbackEnvironment = new ApiTestEnvironment([
                'name' => 'Base URL da Suite',
                'base_url' => $suite->base_url,
                'is_active' => true,
                'requires_auth' => false,
            ]);
            $fallbackEnvironment->id = null;
            $environments = collect([$fallbackEnvironment]);
        }

        foreach ($environments as $environment) {
            foreach ($suite->endpoints as $endpoint) {
                foreach ($endpoint->testCases as $case) {
                    $url = rtrim($environment->base_url, '/') . '/' . ltrim($endpoint->path, '/');

                    $http = Http::acceptJson();

                    if ($endpoint->requires_auth && $environment->requires_auth) {
                        $environmentToken = $this->resolveBearerToken($environment);

                        if (!empty($environmentToken)) {
                            $http = $http->withToken($environmentToken);
                        }
                    }

                    $requestVariants = $this->extractRequestVariants($case->request_payload);

                    foreach ($requestVariants as $variant) {
                        $resolvedPayload = $this->resolvePayloadVariables(
                            $variant['payload'],
                            $endpoint->variables,
                            (string) ($environment->id ?? '__default')
                        );

                        $response = $http->send(
                            $endpoint->method,
                            $url,
                            [
                                'json' => $resolvedPayload
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
                            'environment_id'  => $environment->id,
                            'variant_name'    => $variant['name'],
                            'passed'          => $isPassed,
                            'status_received' => $response->status(),
                            'response_body'   => $responseJson,
                        ]);
                    }
                }
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

    private function resolveBearerToken(ApiTestEnvironment $environment): ?string
    {
        if (empty($environment->id)) {
            return null;
        }

        $environment->refresh();

        if ($this->isStoredTokenStillValid($environment)) {
            return $environment->bearer_token;
        }

        return $this->requestAndPersistNewToken($environment);
    }

    private function isStoredTokenStillValid(ApiTestEnvironment $environment): bool
    {
        if (empty($environment->bearer_token)) {
            return false;
        }

        if ($environment->bearer_token_expires_at instanceof Carbon) {
            if ($environment->bearer_token_expires_at->isFuture()) {
                return true;
            }

            return false;
        }

        $jwtExpiration = $this->extractJwtExpiration($environment->bearer_token);

        if ($jwtExpiration) {
            $environment->update(['bearer_token_expires_at' => $jwtExpiration]);

            return $jwtExpiration->isFuture();
        }

        if (!empty($environment->auth_validate_path)) {
            try {
                $url = $this->buildUrl($environment->base_url, $environment->auth_validate_path);
                $method = strtoupper($environment->auth_validate_method ?? 'GET');
                $expectedStatus = (int) ($environment->auth_validate_status ?? 200);

                $response = Http::acceptJson()
                    ->withToken($environment->bearer_token)
                    ->send($method, $url);

                return $response->status() === $expectedStatus;
            } catch (\Throwable $e) {
                return false;
            }
        }

        return true;
    }

    private function requestAndPersistNewToken(ApiTestEnvironment $environment): ?string
    {
        if (empty($environment->auth_login_path)) {
            return $environment->bearer_token;
        }

        $url = $this->buildUrl($environment->base_url, $environment->auth_login_path);
        $method = strtoupper($environment->auth_login_method ?? 'POST');
        $payload = $environment->auth_payload ?? [];

        $options = [];

        if (in_array($method, ['GET', 'DELETE'], true)) {
            $options['query'] = $payload;
        } else {
            $options['json'] = $payload;
        }

        $response = Http::acceptJson()->send($method, $url, $options);
        $tokenPath = $environment->auth_token_path ?: 'token';
        $token = data_get($response->json(), $tokenPath);

        if (empty($token)) {
            return null;
        }

        $environment->update([
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

    private function extractRequestVariants($requestPayload): array
    {
        if (
            is_array($requestPayload)
            && array_key_exists('__variants', $requestPayload)
            && is_array($requestPayload['__variants'])
        ) {
            $variants = [];

            foreach ($requestPayload['__variants'] as $index => $variantPayload) {
                if (!is_array($variantPayload)) {
                    continue;
                }

                $variantName = $variantPayload['__name'] ?? ('variante_' . ($index + 1));
                $payload = $variantPayload;
                unset($payload['__name']);

                $variants[] = [
                    'name' => $variantName,
                    'payload' => $payload,
                ];
            }

            if (!empty($variants)) {
                return $variants;
            }
        }

        return [[
            'name' => null,
            'payload' => $requestPayload,
        ]];
    }

    private function resolvePayloadVariables($payload, $variables, string $environmentId)
    {
        $dictionary = $this->buildVariableDictionary($variables, $environmentId);

        return $this->replacePlaceholders($payload, $dictionary);
    }

    private function buildVariableDictionary($variables, string $environmentId): array
    {
        $dictionary = [];

        foreach (($variables ?? []) as $item) {
            if (!is_array($item) || empty($item['key'])) {
                continue;
            }

            $values = $item['values'] ?? [];

            if (!is_array($values)) {
                continue;
            }

            if (array_key_exists($environmentId, $values)) {
                $dictionary[$item['key']] = $values[$environmentId];
                continue;
            }

            if (array_key_exists('__default', $values)) {
                $dictionary[$item['key']] = $values['__default'];
            }
        }

        return $dictionary;
    }

    private function replacePlaceholders($value, array $dictionary)
    {
        if (is_array($value)) {
            $result = [];

            foreach ($value as $key => $item) {
                $result[$key] = $this->replacePlaceholders($item, $dictionary);
            }

            return $result;
        }

        if (!is_string($value)) {
            return $value;
        }

        if (!preg_match_all('/\{\{\s*([a-zA-Z0-9_\.-]+)\s*\}\}/', $value, $matches)) {
            return $value;
        }

        if (count($matches[0]) === 1 && trim($value) === $matches[0][0]) {
            $key = $matches[1][0];

            if (array_key_exists($key, $dictionary)) {
                return $dictionary[$key];
            }

            return $value;
        }

        $replaced = $value;

        foreach ($matches[1] as $index => $key) {
            if (!array_key_exists($key, $dictionary)) {
                continue;
            }

            $replacement = $dictionary[$key];

            if (is_array($replacement) || is_object($replacement)) {
                $replacement = json_encode($replacement);
            }

            $replaced = str_replace($matches[0][$index], (string) $replacement, $replaced);
        }

        return $replaced;
    }
}