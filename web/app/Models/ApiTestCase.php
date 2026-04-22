<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApiTestCase extends Model
{
    use HasUuids;
    protected $fillable = [
        'endpoint_id',
        'name',
        'request_payload',
        'expected_response',
        'expected_status',
        'active',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'expected_response' => 'array',
    ];
 
    public function endpoint()
    {
        return $this->belongsTo(Endpoint::class, 'endpoint_id');
    }
}
