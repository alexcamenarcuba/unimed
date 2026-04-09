<?php 
namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ApiTestRun;
use App\Models\ApiTestResult;

class TestRunnerService
{
    public function runSuite($suite)
    {
        $run = ApiTestRun::create([
            'suite_id'   => $suite->id,
            'started_at' => now(),
        ]);

        $passed = 0;
        $failed = 0;

        foreach ($suite->cases as $case) {

            $url = rtrim($suite->base_url, '/') . '/' . ltrim($case->endpoint, '/');

            $response = Http::send(
                $case->method,
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
}