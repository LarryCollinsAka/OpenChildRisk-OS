<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\ClimateObservation;
use App\Models\DataSource;
use App\Models\District;

/**
 * CHIRPS Rainfall Data Service - REAL API + Database Storage
 *
 * Fetches rainfall data from ClimateSERV API.
 * Uses CHIRPS Final product with ~21 day lag for station-blended accuracy.
 * Auto-falls back to earlier months if requested dates unavailable.
 * 
 * NOW STORES: Observations in climate_observations table for risk scoring.
 */
class ChirpsService
{
    protected string $baseUrl;
    protected bool $useRealApi;

    const FLOOD_THRESHOLD_48H = 20;
    const CRITICAL_THRESHOLD_48H = 60;

    // CHIRPS Final product lag (days after month end for station blending)
    const CHIRPS_FINAL_LAG_DAYS = 21;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.chirps.api_url', 'https://climateserv.servirglobal.net/api'), '/');
        $this->useRealApi = config('services.chirps.enabled', false);
    }

    /**
     * Get the latest safe date range for CHIRPS Final product
     */
    public static function getSafeDateRange(int $days = 7): array
    {
        $today = Carbon::now();
        $safeEndDate = $today->copy()->subDays(self::CHIRPS_FINAL_LAG_DAYS);
        $endDate = $safeEndDate->endOfMonth();
        $startDate = $endDate->copy()->subDays($days - 1);

        return [$startDate, $endDate];
    }

    /**
     * Fetch rainfall and store in database
     */
    public function getRainfallForDistrict($district, Carbon $startDate, Carbon $endDate, bool $storeData = true): array
    {
        if (!$this->useRealApi) {
            throw new \Exception("CHIRPS API is disabled. Set CHIRPS_API_ENABLED=true in .env");
        }

        // Check cache first
        $cacheKey = "chirps:{$district->id}:{$startDate->format('Ymd')}:{$endDate->format('Ymd')}";

        if ($cached = Cache::get($cacheKey)) {
            Log::info("CHIRPS cache hit for {$district->name}");
            return $cached;
        }

        Log::info("Fetching REAL CHIRPS data for {$district->name}", [
            'lat' => $district->centroid_lat,
            'lng' => $district->centroid_lng,
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
        ]);

        // Try up to 6 months back
        $maxAttempts = 6;
        $attemptStartDate = $startDate->copy();
        $attemptEndDate = $endDate->copy();
        $dailyRainfall = null;
        $usedFallback = false;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                Log::info("CHIRPS attempt {$attempt}/{$maxAttempts}", [
                    'dates' => "{$attemptStartDate->format('Y-m-d')} to {$attemptEndDate->format('Y-m-d')}"
                ]);

                $dailyRainfall = $this->fetchRealChirpsData(
                    (float) $district->centroid_lat,
                    (float) $district->centroid_lng,
                    $attemptStartDate,
                    $attemptEndDate
                );

                $startDate = $attemptStartDate;
                $endDate = $attemptEndDate;
                $usedFallback = ($attempt > 1);

                Log::info("CHIRPS data found!", [
                    'attempt' => $attempt,
                    'dates' => "{$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}"
                ]);

                break;
            } catch (\Exception $e) {
                Log::warning("CHIRPS attempt {$attempt} failed: {$e->getMessage()}");

                if ($attempt < $maxAttempts) {
                    $attemptEndDate = $attemptEndDate->copy()->subMonth()->endOfMonth();
                    $attemptStartDate = $attemptEndDate->copy()->subDays(6);
                } else {
                    throw new \Exception("No CHIRPS data available after trying {$maxAttempts} months");
                }
            }
        }

        if (!$dailyRainfall) {
            throw new \Exception("Failed to fetch CHIRPS data after {$maxAttempts} attempts");
        }

        $result = [
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
            'rainfall' => $dailyRainfall,
            'analysis' => $this->analyzeRainfall($dailyRainfall),
            'data_source' => 'ClimateSERV CHIRPS API (Final Product)',
            'used_fallback' => $usedFallback,
            'fallback_notice' => $usedFallback ?
                "Requested dates unavailable. Using validated data from {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')}." :
                null,
        ];

        // NEW: Store observations in database
        if ($storeData) {
            $this->storeObservations($district, $dailyRainfall);
        }

        Cache::put($cacheKey, $result, 3600);
        return $result;
    }

   /**
 * Store climate observations in database
 */
protected function storeObservations(District $district, array $dailyRainfall): int
{
    // Get or create CHIRPS data source
    $dataSource = DataSource::firstOrCreate(
        ['code' => 'CHIRPS'],
        [
            'name' => 'CHIRPS',
            'description' => 'Climate Hazards Group InfraRed Precipitation with Station data',
            'source_type' => 'satellite',
            'url' => 'https://www.chc.ucsb.edu/data/chirps',
            'update_frequency' => 'daily',
            'is_active' => true,
        ]
    );

    $stored = 0;

    foreach ($dailyRainfall as $day) {
        try {
            ClimateObservation::updateOrCreate(
                [
                    'district_id' => $district->id,
                    'observation_date' => Carbon::parse($day['date']),
                    'data_source_id' => $dataSource->id,
                ],
                [
                    'rainfall_mm' => $day['rainfall_mm'],
                    'quality' => 'good',
                    'confidence' => 1.0,
                    'spatial_resolution_km' => 5.0,
                    'metadata' => [
                        'source' => 'ClimateSERV API',
                        'product' => 'CHIRPS Final',
                    ],
                ]
            );
            $stored++;
        } catch (\Exception $e) {
            Log::warning("Failed to store observation for {$day['date']}: {$e->getMessage()}");
        }
    }

    Log::info("Stored {$stored} climate observations for {$district->name}");

    return $stored;
}

    protected function fetchRealChirpsData(float $lat, float $lng, Carbon $startDate, Carbon $endDate): array
    {
        $geometry = [
            'type' => 'Point',
            'coordinates' => [$lng, $lat]
        ];

        $geometryString = json_encode($geometry);

        $requestPayload = [
            'datatype' => 0,
            'begintime' => $startDate->format('m/d/Y'),
            'endtime' => $endDate->format('m/d/Y'),
            'intervaltype' => 0,
            'operationtype' => 5,
            'geometry' => $geometryString,
        ];

        Log::info("ClimateSERV: Submitting request", [
            'url' => $this->baseUrl . '/submitDataRequest/',
            'dates' => "{$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}",
        ]);

        $submitResponse = Http::timeout(30)
            ->asForm()
            ->post($this->baseUrl . '/submitDataRequest/', $requestPayload);

        if (!$submitResponse->successful()) {
            throw new \Exception("ClimateSERV submit failed: HTTP " . $submitResponse->status());
        }

        $submitData = $submitResponse->json();

        if (!is_array($submitData) || empty($submitData[0])) {
            throw new \Exception("Invalid submit response: " . json_encode($submitData));
        }

        $requestId = $submitData[0];
        Log::info("ClimateSERV: Job ID received", ['job_id' => $requestId]);

        // Poll for results
        $maxAttempts = 10;
        $data = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            sleep(2);

            $dataResponse = Http::timeout(30)
                ->get($this->baseUrl . '/getDataFromRequest/', [
                    'id' => $requestId,
                ]);

            if (!$dataResponse->successful()) {
                Log::warning("Poll failed: HTTP " . $dataResponse->status());
                continue;
            }

            $responseData = $dataResponse->json();

            if (isset($responseData['data']) && !empty($responseData['data'])) {
                $data = $responseData['data'];
                Log::info("ClimateSERV: Data ready!", ['records' => count($data)]);
                break;
            }
        }

        if (!$data) {
            throw new \Exception("No data received after {$maxAttempts} polling attempts");
        }

        // Parse data
        $dailyRainfall = [];

        foreach ($data as $record) {
            try {
                $date = Carbon::createFromFormat('n/j/Y', $record['date'])->format('Y-m-d');
                $rainfall = $record['value']['avg'] ?? 0;

                $dailyRainfall[] = [
                    'date' => $date,
                    'rainfall_mm' => round((float) $rainfall, 2),
                ];
            } catch (\Exception $e) {
                Log::warning("Failed to parse record", [
                    'record' => $record,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        usort($dailyRainfall, fn($a, $b) => strcmp($a['date'], $b['date']));

        Log::info("ClimateSERV: Parsing complete", [
            'days' => count($dailyRainfall),
            'total_mm' => array_sum(array_column($dailyRainfall, 'rainfall_mm')),
        ]);

        return $dailyRainfall;
    }

    protected function analyzeRainfall(array $dailyRainfall): array
    {
        $total = array_sum(array_column($dailyRainfall, 'rainfall_mm'));

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

        $floodRisk = 'low';
        if ($max48h >= self::CRITICAL_THRESHOLD_48H) {
            $floodRisk = 'critical';
        } elseif ($max48h >= self::FLOOD_THRESHOLD_48H) {
            $floodRisk = 'high';
        } elseif ($max48h >= 50) {
            $floodRisk = 'medium';
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

    public function shouldGenerateFloodAlert(array $rainfallData): bool
    {
        return $rainfallData['analysis']['exceeds_threshold'] ?? false;
    }
}
