<?php

/**
 * Web Routes
 * 
 * Application routing for OpenChildRisk OS.
 * Uses Inertia.js to bridge Laravel backend with React frontend.
 */

use App\Http\Controllers\Admin\ConflictProviderController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ============================================================================
// DASHBOARD ROUTE
// ============================================================================
// Main operational intelligence dashboard
// Displays real-time risk data, geospatial mapping, and alerts
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ============================================================================
// ADMIN ROUTES
// ============================================================================
// Admin panel for managing providers, configurations, and system settings
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Conflict Providers
    Route::get('/conflict-providers', [ConflictProviderController::class, 'index'])
         ->name('conflict-providers.index');
    
    Route::get('/conflict-providers/{provider}/configurations', [ConflictProviderController::class, 'configurations'])
         ->name('conflict-providers.configurations');
    
    Route::post('/conflict-providers/{provider}/configurations', [ConflictProviderController::class, 'storeConfiguration'])
         ->name('conflict-providers.configurations.store');
    
    Route::put('/conflict-providers/{provider}/configurations/{configuration}', [ConflictProviderController::class, 'updateConfiguration'])
         ->name('conflict-providers.configurations.update');
    
    Route::delete('/conflict-providers/{provider}/configurations/{configuration}', [ConflictProviderController::class, 'deleteConfiguration'])
         ->name('conflict-providers.configurations.delete');
    
    Route::post('/conflict-providers/{provider}/toggle', [ConflictProviderController::class, 'toggle'])
         ->name('conflict-providers.toggle');
});