<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\TestSuiteController;
use App\Http\Controllers\TestRunController;

Route::get('/', function () {
    return Inertia::render('admin/Index');
});

Route::get('/teste-api', function () {
    $response = Http::post('http://crm/api/v1/contratante/login', [
        'nomeUsuario' => 'acubaapi',
        'email' => 'joao@email.com',
    ]);

    return $response->json();
});

Route::prefix('test-suites')->group(function () {

    Route::get('/', [TestSuiteController::class, 'index']);
    Route::post('/', [TestSuiteController::class, 'store']);
    Route::get('/{suite}', [TestSuiteController::class, 'show']);

    Route::post('/{suite}/cases', [TestSuiteController::class, 'storeCase']);
    Route::post('/{suite}/run', [TestSuiteController::class, 'run']);
});

Route::prefix('test-runs')->group(function () {

    Route::get('/{run}', [TestRunController::class, 'show']);
});

Route::put('/test-cases/{case}', [TestSuiteController::class, 'updateCase']);
Route::delete('/test-cases/{case}', [TestSuiteController::class, 'deleteCase']);
