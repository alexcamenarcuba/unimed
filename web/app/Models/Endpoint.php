<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Endpoint extends Model
{
    use HasUuids;

    protected $fillable = [
        'suite_id',
        'name',
        'method',
        'path',
        'requires_auth',
        'bearer_token',
        'bearer_token_expires_at',
        'auth_login_path',
        'auth_login_method',
        'auth_payload',
        'auth_token_path',
        'auth_validate_path',
        'auth_validate_method',
        'auth_validate_status',
    ];

    protected $casts = [
        'requires_auth' => 'boolean',
        'bearer_token' => 'encrypted',
        'bearer_token_expires_at' => 'datetime',
        'auth_payload' => 'array',
    ];

    protected $hidden = [
        'bearer_token',
    ];

    public function suite()
    {
        return $this->belongsTo(ApiTestSuite::class, 'suite_id');
    }

    public function testCases()
    {
        return $this->hasMany(ApiTestCase::class, 'endpoint_id');
    }
}