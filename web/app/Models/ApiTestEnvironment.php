<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ApiTestEnvironment extends Model
{
    use HasUuids;

    protected $fillable = [
        'suite_id',
        'name',
        'base_url',
        'is_active',
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
        'is_active' => 'boolean',
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

    public function results()
    {
        return $this->hasMany(ApiTestResult::class, 'environment_id');
    }
}
