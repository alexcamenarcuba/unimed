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
        'passed',
        'status_received',
        'response_body',
        'errors',
        'response_time_ms',
    ];

    protected $casts = [
        'passed'        => 'boolean',
        'response_body' => 'array',
    ];

    public function testCase()
    {
        return $this->belongsTo(ApiTestCase::class, 'test_case_id');
    }

    public function run()
    {
        return $this->belongsTo(ApiTestRun::class, 'api_test_run_id');
    }
}
