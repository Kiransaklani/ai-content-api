<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes — login required
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // Analyze — max 10 requests per minute (AI calls are expensive)
    Route::post('/analyze', [MainController::class, 'analyze'])->middleware('throttle:10,1');

    // Analyses / Reports
    Route::get('/analyses/{id}/status', [MainController::class, 'analysisStatus']);
    Route::get('/analyses', [MainController::class, 'index']);
    Route::get('/reports', [MainController::class, 'reports']);
    Route::delete('/reports/{id}', [MainController::class, 'deleteReport']);

    // Dashboard & Usage
    Route::get('/dashboard-summary', [MainController::class, 'dashboardSummary']);
    Route::get('/api-usage', [MainController::class, 'apiUsage']);

});

Route::get('/analysis-scores', [MainController::class, 'analysisScores']);
