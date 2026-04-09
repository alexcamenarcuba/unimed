<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\ApiTestRun;

class TestRunController extends Controller
{
    public function show(ApiTestRun $run)
    {
        $run->load([
            'suite',
            'results.testCase'
        ]);

        return Inertia::render('admin/test-orchestrator/pages/TestRunShow', [
            'run' => $run
        ]);
    }
}