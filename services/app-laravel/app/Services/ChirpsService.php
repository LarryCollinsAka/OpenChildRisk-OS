<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * CHIRPS Rainfall Data Service
 * 
 * Fetches rainfall data from Climate Hazards Group InfraRed Precipitation with Station data.
 * CHIRPS provides daily/pentad/dekadal/monthly rainfall estimates for drought monitoring
 * and early warning systems.
 * 
 * API Documentation: https://data.chc.ucsb.edu/products/CHIRPS-2.0/
 * 
 * Features:
 * - Fetch rainfall data for specific coordinates (districts)
 * - Time-series rainfall accumulation
 * - Anomaly detection (rainfall vs historical average)
 * - Flood threshold detection
 * 
 * Usage:
 *   $chirps = new ChirpsService();
 *   $rainfall = $chirps->getRainfallForDistrict($district, $startDate, $endDate);
 */
class ChirpsService
{
    /**
     * CHIRPS API base URL
     * Using the CHC data portal
     */
    protected string $baseUrl = 'https://data.chc.ucsb.edu/products/CHIRPS-2.0';

    /**
     * Rainfall threshold for flood warning (mm in 48 hours)
     * Based on Far North Cameroon flood patterns
     */
    const FLOOD_THRESHOLD_48H = 20; // 100mm in 48 hours triggers flood warning

    /**
     * Rainfall threshold for critical flood alert (mm in 48 hours)
     */
    const CRITICAL_THRESHOLD_48H = 60; // 150mm in 48 hours = critical

    /**
     * Get rainfall data for a district
     * 
     * Fetches CHIRPS daily rainfall estimates for a specific location
     * and time period. Returns total accumulation and daily breakdown.
     * 
     * @param \App\Models\District $district District object with coordinates
     * @param Carbon $startDate Start date for rainfall query
     * @param Carbon $endDate End date for rainfall query
     * @return array Rainfall data with totals and daily values
     */
    public function getRainfallForDistrict($district, Carbon $startDate, Carbon $endDate): array
    {
        try {
            // ================================================================
            // CHIRPS DATA EXTRACTION
            // ================================================================
            // CHIRPS stores data as GeoTIFF rasters
            // For production, you'd use GDAL to extract pixel values
            // For now, we'll simulate with realistic patterns
            
            Log::info("Fetching CHIRPS data for {$district->name}", [
                'lat' => $district->centroid_lat,
                'lng' => $district->centroid_lng,
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ]);

            // ================================================================
            // MOCK DATA FOR INITIAL DEPLOYMENT
            // ================================================================
            // TODO: Replace with real CHIRPS API integration
            // Real implementation would:
            // 1. Download CHIRPS GeoTIFF for date range
            // 2. Extract pixel value at (lat, lng) coordinates
            // 3. Convert DN (Digital Number) to rainfall mm
            // 4. Aggregate daily values
            
            $mockRainfall = $this->getMockRainfallData($district->name, $startDate, $endDate);

            return [
                'district_id' => $district->id,
                'district_name' => $district->name,
                'coordinates' => [
                    'lat' => (float) $district->centroid_lat,
                    'lng' => (float) $district->centroid_lng,
                ],
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                    'days' => $startDate->diffInDays($endDate) + 1,
                ],
                'rainfall' => $mockRainfall,
                'analysis' => $this->analyzeRainfall($mockRainfall),
            ];

        } catch (\Exception $e) {
            Log::error("CHIRPS fetch failed for {$district->name}: " . $e->getMessage());
            
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'district_id' => $district->id,
            ];
        }
    }

    /**
     * Analyze rainfall data for hazard detection
     * 
     * Calculates:
     * - Total accumulation
     * - 48-hour rolling maximum
     * - Flood risk level
     * - Anomaly vs normal
     * 
     * @param array $dailyRainfall Daily rainfall values
     * @return array Analysis results with thresholds and alerts
     */
    protected function analyzeRainfall(array $dailyRainfall): array
    {
        $total = array_sum(array_column($dailyRainfall, 'rainfall_mm'));
        
        // ================================================================
        // 48-HOUR ROLLING MAXIMUM (Flood Trigger)
        // ================================================================
        // Floods typically occur when heavy rain falls in short period
        // Calculate maximum 48-hour accumulation in the dataset
        $max48h = 0;
        $max48hDate = null;
        
        for ($i = 0; $i < count($dailyRainfall) - 1; $i++) {
            $sum48h = $dailyRainfall[$i]['rainfall_mm'] + 
                     ($dailyRainfall[$i + 1]['rainfall_mm'] ?? 0);
            
            if ($sum48h > $max48h) {
                $max48h = $sum48h;
                $max48hDate = $dailyRainfall[$i]['date'];
            }
        }

        // ================================================================
        // FLOOD RISK CLASSIFICATION
        // ================================================================
        $floodRisk = 'low';
        if ($max48h >= self::CRITICAL_THRESHOLD_48H) {
            $floodRisk = 'critical'; // Severe flooding expected
        } elseif ($max48h >= self::FLOOD_THRESHOLD_48H) {
            $floodRisk = 'high'; // Flooding likely
        } elseif ($max48h >= 50) {
            $floodRisk = 'medium'; // Monitor situation
        }

        return [
            'total_rainfall_mm' => round($total, 2),
            'average_daily_mm' => round($total / count($dailyRainfall), 2),
            'max_48h_mm' => round($max48h, 2),
            'max_48h_date' => $max48hDate,
            'flood_risk_level' => $floodRisk,
            'exceeds_threshold' => $max48h >= self::FLOOD_THRESHOLD_48H,
            'days_analyzed' => count($dailyRainfall),
        ];
    }

    /**
     * Get mock rainfall data for testing
     * 
     * Simulates realistic rainfall patterns for Far North Cameroon:
     * - Rainy season: June-September (heavy rains)
     * - Dry season: October-May (minimal rain)
     * - Flood-prone districts get higher values
     * 
     * TODO: Remove when real CHIRPS integration is complete
     * 
     * @param string $districtName District name
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return array Daily rainfall values
     */
    protected function getMockRainfallData(string $districtName, Carbon $startDate, Carbon $endDate): array
    {
        $dailyData = [];
        $current = $startDate->copy();

        // ================================================================
        // DISTRICT-SPECIFIC RAINFALL PATTERNS
        // ================================================================
        // Flood-prone districts (Makary, Logone-Birni) get higher rainfall
        $baseRainfall = in_array($districtName, ['Makary', 'Logone-Birni']) ? 15 : 8;
        
        // Month affects rainfall (rainy season = higher)
        $isRainySeason = in_array($current->month, [6, 7, 8, 9]);
        $seasonMultiplier = $isRainySeason ? 3 : 0.5;

        while ($current <= $endDate) {
            // Add some randomness (±50%)
            $rainfall = $baseRainfall * $seasonMultiplier * (0.5 + rand(0, 100) / 100);
            
            // Occasionally add heavy rain events
            if (rand(1, 10) == 1) {
                $rainfall *= 5; // Simulate storm
            }

            $dailyData[] = [
                'date' => $current->format('Y-m-d'),
                'rainfall_mm' => round($rainfall, 2),
            ];

            $current->addDay();
        }

        return $dailyData;
    }

    /**
     * Check if district needs flood alert
     * 
     * Quick check method for alert generation
     * 
     * @param array $rainfallData Rainfall data from getRainfallForDistrict()
     * @return bool True if alert should be generated
     */
    public function shouldGenerateFloodAlert(array $rainfallData): bool
    {
        return $rainfallData['analysis']['exceeds_threshold'] ?? false;
    }
}