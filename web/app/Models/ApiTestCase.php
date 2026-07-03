<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApiTestCase extends Model
{
    use HasUuids;

    protected $fillable = [
        'endpoint_id',
        'case_group_id',
        'name',
        'request_payload',
        'variable_overrides',
        'expected_response',
        'expected_status',
        'active',
    ];

    protected $casts = [
        'variable_overrides' => 'array',
        'expected_response' => 'array',
    ];

    public function getRequestPayloadAttribute($value)
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $value;
    }

    public function setRequestPayloadAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['request_payload'] = null;
            return;
        }

        if (is_array($value) || is_object($value)) {
            $this->attributes['request_payload'] = json_encode($value);
            return;
        }

        $this->attributes['request_payload'] = $value;
    }
 
    public function endpoint()
    {
        return $this->belongsTo(Endpoint::class, 'endpoint_id');
    }

    public function caseGroup()
    {
        return $this->belongsTo(ApiTestCaseGroup::class, 'case_group_id');
    }
}
