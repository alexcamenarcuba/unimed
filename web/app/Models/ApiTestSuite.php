<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApiTestSuite extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'base_url'];

    public function endpoints()
    {
        return $this->hasMany(Endpoint::class, 'suite_id');
    }

    public function runs()
    {
        return $this->hasMany(ApiTestRun::class, 'suite_id');
    }
}
