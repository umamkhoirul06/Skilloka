<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('v1')->group(function () {
    // Auth - Public
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Auth - Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Other routes to be added here
    });

    // Public Discovery
    Route::get('/courses', [\App\Http\Controllers\Api\V1\CourseController::class, 'index']);
    Route::get('/categories', [\App\Http\Controllers\Api\V1\CategoryController::class, 'index']);
    Route::get('/locations', [\App\Http\Controllers\Api\V1\LocationController::class, 'index']);
    // Route::get('/lpks', [LpkController::class, 'index']);
});
