<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Indicator;
use App\Models\PopulationGroup;
use App\Models\DataSource;
use Inertia\Inertia;

/**
 * Dashboard Controller
 * 
 * Handles the main operational intelligence dashboard display.
 * Aggregates data from multiple sources to provide real-time
 * humanitarian risk intelligence for the Far North Region.
 */
class DashboardController extends Controller
{
    /**
     * Display the operational intelligence dashboard
     * 
     * This method:
     * 1. Fetches active districts with geospatial coordinates
     * 2. Calculates (or retrieves) risk scores for each district
     * 3. Determines risk status levels (Critical/High/Medium/Low)
     * 4. Aggregates system-wide statistics
     * 5. Returns data to the React dashboard via Inertia
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        // ============================================================
        // FETCH REAL DISTRICTS FROM DATABASE
        // ============================================================
        // Get all active districts in the Far North Region with their
        // geographic coordinates and population data
        $districts = District::where('active', true)
            ->select('id', 'name', 'code', 'centroid_lat', 'centroid_lng', 'population')
            ->get()
            ->map(function ($district) {
                // ====================================================
                // RISK SCORE CALCULATION
                // ====================================================
                // TODO: Replace mock data with real compound risk assessments
                // Future implementation will query compound_risk_assessments table
                // and calculate real-time risk based on:
                // - Climate hazards (rainfall, temperature)
                // - Disease surveillance (cholera, malaria, measles)
                // - Conflict events (ACLED data)
                // - Infrastructure indicators (WASH, health facilities)
                // - Vulnerability factors (under-5 population, malnutrition)

                // TEMPORARY: Mock risk scores for initial deployment
                // These will be replaced with live risk engine calculations
                $mockRisks = [
                    'Mora' => 9.2,        // Critical: Poor WASH + Flooding
                    'Makary' => 7.8,      // High: Heavy rainfall
                    'Kousseri' => 6.4,    // Medium: Low vaccination
                    'Yagoua' => 4.2,      // Low: Stable conditions
                    'Maroua' => 3.8,      // Low: Good infrastructure
                    'Kolofata' => 8.1,    // Critical: Conflict + Displacement
                    'Logone-Birni' => 5.5, // Medium: Seasonal flooding
                ];

                $risk = $mockRisks[$district->name] ?? 5.0;

                // ====================================================
                // RISK STATUS CLASSIFICATION
                // ====================================================
                // Classify districts into 4 risk levels based on score:
                // - Critical (≥8.0): Immediate intervention required
                // - High (6.0-7.9): Priority attention needed
                // - Medium (4.0-5.9): Monitoring required
                // - Low (<4.0): Stable conditions
                $status = $risk >= 8 ? 'Critical'
                    : ($risk >= 6 ? 'High'
                        : ($risk >= 4 ? 'Medium'
                            : 'Low'));

                // ====================================================
                // FORMAT DATA FOR MAP VISUALIZATION
                // ====================================================
                // Return structured data for Leaflet map rendering
                return [
                    'name' => $district->name,
                    'lat' => (float) $district->centroid_lat,  // Ensure float for JavaScript
                    'lng' => (float) $district->centroid_lng,
                    'risk' => $risk,
                    'status' => $status,
                    'population' => number_format($district->population / 1000, 1) . 'K', // Format as "125.0K"
                    'factors' => $this->getMockFactors($district->name), // Risk drivers
                ];
            });

        // ============================================================
        // AGGREGATE SYSTEM STATISTICS
        // ============================================================
        // Count active records across key system tables to display
        // operational readiness metrics on the dashboard
        $stats = [
            'total_districts' => District::where('active', true)->count(),
            'total_indicators' => Indicator::where('active', true)->count(),
            'total_population_groups' => PopulationGroup::where('active', true)->count(),
            'total_data_sources' => DataSource::where('active', true)->count(),
        ];

        // ============================================================
        // RENDER INERTIA DASHBOARD
        // ============================================================
        // Pass data to React frontend via Inertia.js
        // The Dashboard.jsx component will receive:
        // - mapDistricts: Array of district objects with coordinates & risk
        // - stats: System-wide counts for header cards
        return Inertia::render('Dashboard', [
            'mapDistricts' => $districts,
            'stats' => $stats,
        ]);
    }

    /**
     * Get mock risk factors for a district
     * 
     * TODO: Replace with real factor analysis from:
     * - district_indicator_values (sanitation, vaccination, etc.)
     * - hazard_events (floods, droughts, conflicts)
     * - compound_risk_assessments (cascade pathways)
     * 
     * This method should eventually query the database for:
     * 1. Top 3 contributing risk indicators
     * 2. Active hazard events in the district
     * 3. Primary vulnerability drivers
     * 
     * @param string $districtName
     * @return string Risk factors description
     */
    private function getMockFactors($districtName)
    {
        // TEMPORARY: Hardcoded risk factors
        // These will be replaced with dynamic queries to:
        // - district_indicator_values (e.g., "Sanitation coverage: 38%")
        // - hazard_events (e.g., "Active flood event")
        // - district_population_stats (e.g., "High under-5 population")
        $factors = [
            'Mora' => 'Poor WASH + Flood',           // Low sanitation + Active flood event
            'Makary' => 'Heavy rainfall',            // CHIRPS rainfall > 120mm
            'Kousseri' => 'Low vaccination',         // Vaccination coverage < 60%
            'Yagoua' => 'Stable conditions',         // No active hazards
            'Maroua' => 'Good infrastructure',       // Urban center with health facilities
            'Kolofata' => 'Conflict + Displacement', // ACLED conflict events + IDP population
            'Logone-Birni' => 'Seasonal flooding',   // Riverine district, flood-prone
        ];

        return $factors[$districtName] ?? 'Under assessment';
    }
}
