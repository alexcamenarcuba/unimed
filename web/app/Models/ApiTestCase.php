<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApiTestCase extends Model
{
    use HasUuids;
    protected $fillable = [
        'suite_id',
        'name',
        'method',
        'endpoint',
        'request_payload',
        'expected_response',
        'expected_status',
        'active',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'expected_response' => 'array',
    ];
    public function suite()
    {
        return $this->belongsTo(ApiTestSuite::class, 'suite_id');
    }
}
