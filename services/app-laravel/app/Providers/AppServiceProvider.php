<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * OpenChildRisk OS service registrations.
     * Add bindings and singletons here.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Configures core services for OpenChildRisk OS:
     * - Sanctum token model (UUID-based)
     * - API token expiration
     * - Force HTTPS in production
     */
    public function boot(): void
    {
        // Use default PersonalAccessToken model
        // This model is UUID-aware via our migration
        // Override here if custom token model needed later
        Sanctum::usePersonalAccessTokenModel(
            PersonalAccessToken::class
        );

        // Force HTTPS in production
        // Critical for secure token transmission
        // in field deployments
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
