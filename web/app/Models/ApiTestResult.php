<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApiTestResult extends Model
{
    use HasUuids;

    protected $fillable = [
        'run_id',
        'test_case_id',
        'environment_id',
        'variant_name',
        'passed',
        'status_received',
        'request_payload',
        'response_body',
        'errors',
        'response_time_ms',
    ];

    protected $casts = [
        'passed'        => 'boolean',
        'request_payload' => 'object',
        'response_body' => 'array',
    ];

    public function testCase()
    {
        return $this->belongsTo(ApiTestCase::class, 'test_case_id');
    }

    public function run()
    {
        return $this->belongsTo(ApiTestRun::class, 'run_id');
    }

    public function environment()
    {
        return $this->belongsTo(ApiTestEnvironment::class, 'environment_id');
    }
}
