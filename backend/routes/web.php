<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Admin Authentication
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Admin Dashboard (Protected)
Route::middleware(['auth:web'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminAuthController::class, 'dashboard'])->name('dashboard');

    // Placeholder for LPK Profile
    Route::get('/profile', function () {
        return view('admin.profile'); // We will create this
    })->name('lpk.profile');
});

// Super Admin Routes (God View)
Route::middleware(['auth:web', 'super-admin'])->prefix('super-admin')->name('super.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [\App\Http\Controllers\Admin\AdminAuthController::class, 'logout'])->name('logout');

    // Future routes:
    // Route::resource('tenants', TenantController::class);
    // Route::resource('categories', CategoryController::class);
});
