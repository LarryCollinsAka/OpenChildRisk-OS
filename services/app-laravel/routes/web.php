<?php

/**
 * Web Routes
 * 
 * Application routing for OpenChildRisk OS.
 * Uses Inertia.js to bridge Laravel backend with React frontend.
 */

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ============================================================================
// DASHBOARD ROUTE
// ============================================================================
// Main operational intelligence dashboard
// Displays real-time risk data, geospatial mapping, and alerts
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');