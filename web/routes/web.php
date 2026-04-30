<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TestSuiteController;
use App\Http\Controllers\TestRunController;
use App\Http\Controllers\AzureController;

Route::get('/', function () {
    return Inertia::render('/Index');
});

Route::prefix('test-suites')->group(function () {
    Route::get('/', [TestSuiteController::class, 'index']);
    Route::get('/create', [TestSuiteController::class, 'create']);
    Route::post('/', [TestSuiteController::class, 'store']);
    Route::get('/{suite}/edit', [TestSuiteController::class, 'edit']);
    Route::put('/{suite}', [TestSuiteController::class, 'update']);
    Route::get('/{suite}', [TestSuiteController::class, 'show']);
    Route::get('/{suite}/environments/create', [TestSuiteController::class, 'createEnvironment']);
    Route::post('/{suite}/environments', [TestSuiteController::class, 'storeEnvironment']);
    Route::get('/{suite}/environments/{environment}/edit', [TestSuiteController::class, 'editEnvironment']);
    Route::put('/{suite}/environments/{environment}', [TestSuiteController::class, 'updateEnvironment']);
    Route::patch('/{suite}/environments/{environment}/status', [TestSuiteController::class, 'toggleEnvironmentStatus']);
    Route::get('/{suite}/endpoints/create', [TestSuiteController::class, 'createEndpoint']);
    Route::post('/{suite}/endpoints', [TestSuiteController::class, 'storeEndpoint']);
    Route::get('/{suite}/endpoints/{endpoint}/edit', [TestSuiteController::class, 'editEndpoint']);
    Route::put('/{suite}/endpoints/{endpoint}', [TestSuiteController::class, 'updateEndpoint']);
    Route::get('/{suite}/cases/create', [TestSuiteController::class, 'createCase']);
    Route::get('/{suite}/cases/{case}/edit', [TestSuiteController::class, 'editCase']);
    Route::put('/{suite}/cases/{apiCase}', [TestSuiteController::class, 'updateCase']);
    Route::post('/{suite}/cases', [TestSuiteController::class, 'storeCase']);
    Route::post('/{suite}/case-groups', [TestSuiteController::class, 'storeCaseGroup']);
    Route::delete('/{suite}/case-groups/{group}', [TestSuiteController::class, 'deleteCaseGroup']);
    Route::post('/{suite}/run', [TestSuiteController::class, 'run']);
});

Route::prefix('test-runs')->group(function () {
    Route::get('/', [TestRunController::class, 'index'])->name('test-runs.index');
    Route::get('/{run}', [TestRunController::class, 'show'])->name('test-runs.show');
});

Route::prefix('tickets')->group(function () {
    Route::get('/', [AzureController::class, 'dashboard']);
    Route::get('/work-items', [AzureController::class, 'workItems']);
    Route::get('/debug-fields', [AzureController::class, 'debugFields']);
});