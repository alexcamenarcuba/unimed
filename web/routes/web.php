<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TestSuiteController;
use App\Http\Controllers\TestRunController;

/*
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Auth;
*/

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
    Route::get('/{suite}/endpoints/create', [TestSuiteController::class, 'createEndpoint']);
    Route::post('/{suite}/endpoints', [TestSuiteController::class, 'storeEndpoint']);
    Route::get('/{suite}/endpoints/{endpoint}/edit', [TestSuiteController::class, 'editEndpoint']);
    Route::put('/{suite}/endpoints/{endpoint}', [TestSuiteController::class, 'updateEndpoint']);
    Route::get('/{suite}/cases/create', [TestSuiteController::class, 'createCase']);
    Route::get('/{suite}/cases/{case}/edit', [TestSuiteController::class, 'editCase']);
    Route::put('/{suite}/cases/{apiCase}', [TestSuiteController::class, 'updateCase']);
    Route::post('/{suite}/cases', [TestSuiteController::class, 'storeCase']);
    Route::post('/{suite}/run', [TestSuiteController::class, 'run']);
});

Route::prefix('test-runs')->group(function () {
    Route::get('/', [TestRunController::class, 'index'])->name('test-runs.index');
    Route::get('/{run}', [TestRunController::class, 'show'])->name('test-runs.show');
});

//Route::delete('/test-cases/{case}', [TestSuiteController::class, 'deleteCase']);

/*
Route::prefix('auth')->group(function () {

    Route::get('/login', fn() => Inertia::render('auth/pages/Login'))
        ->name('auth.login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('auth.login.store');

    Route::get('/token', fn() => Inertia::render('auth/pages/ConfirmToken'))
        ->name('auth.token');

    Route::get('/esqueci-senha', fn() => Inertia::render('auth/pages/ForgotPassword'))
        ->name('auth.forgot');

    Route::post('/esqueci-senha', [ForgotPasswordController::class, 'sendToken'])
        ->name('auth.send-token');

    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])
        ->name('auth.reset-password');

    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])
        ->name('auth.reset-password.store');
});

Route::get('/admin', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('auth.login');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('auth.login');
})->name('auth.logout');

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('admin/pages/Dashboard'))
        ->name('admin.dashboard');
});

Route::get('/login', function () {
    return redirect()->route('auth.login');
})->name('login');
*/