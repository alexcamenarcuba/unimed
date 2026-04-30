<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ApiTestCaseGroup extends Model
{
    use HasUuids;

    protected $fillable = [
        'suite_id',
        'name',
    ];

    public function suite()
    {
        return $this->belongsTo(ApiTestSuite::class, 'suite_id');
    }
}