<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApiTestRun extends Model
{
    use HasUuids;

    protected $fillable = ['suite_id', 'started_at', 'finished_at', 'total_tests', 'passed', 'failed'];

    public function suite()
    {
        return $this->belongsTo(ApiTestSuite::class, 'suite_id');
    }

    public function results()
    {
        return $this->hasMany(ApiTestResult::class, 'run_id');
    }
}
