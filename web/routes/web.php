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
    Route::get('/{suite}/cases/create', [TestSuiteController::class, 'createCase']);
    Route::get('/{suite}/cases/{case}/edit', [TestSuiteController::class, 'editCase']);
    Route::post('/{suite}/cases', [TestSuiteController::class, 'storeCase']);
    Route::post('/{suite}/run', [TestSuiteController::class, 'run']);
});

Route::prefix('test-runs')->group(function () {
    Route::get('/{run}', [TestRunController::class, 'show']);
});

Route::put('/test-cases/{case}', [TestSuiteController::class, 'updateCase']);
Route::delete('/test-cases/{case}', [TestSuiteController::class, 'deleteCase']);

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
