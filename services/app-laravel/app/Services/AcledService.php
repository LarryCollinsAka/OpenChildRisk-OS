<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\ConflictEvent;
use App\Models\ConflictCategory;
use App\Models\DataSource;
use App\Models\Country;

/**
 * ACLED API Service
 * 
 * Fetches conflict event data from Armed Conflict Location & Event Data Project.
 * Uses OAuth 2.0 password grant authentication.
 * 
 * API Documentation: https://acleddata.com/resources/general-guides/
 * 
 * Free tier limits:
 * - Access token valid: 24 hours
 * - Refresh token valid: 14 days
 * - Default limit: 5000 events per request
 * 
 * Coverage: Real-time conflict data across 200+ countries
 */
class AcledService
{
    protected string $apiUrl;
    protected string $tokenUrl;
    protected string $email;
    protected string $password;

    const RATE_LIMIT_DELAY = 6; // seconds between requests

    public function __construct()
    {
        $this->apiUrl = config('services.acled.api_url', 'https://acleddata.com/api');
        $this->tokenUrl = config('services.acled.token_url', 'https://acleddata.com/oauth/token');
        $this->email = config('services.acled.email');
        $this->password = config('services.acled.password');
    }

    /**
     * Get OAuth access token
     * Token cached for 23 hours (expires in 24)
     */
    protected function getAccessToken(): string
    {
        $cacheKey = 'acled:access_token';

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        if (!$this->email || !$this->password) {
            throw new \Exception('ACLED credentials not configured. Set ACLED_EMAIL and ACLED_PASSWORD in .env');
        }

        Log::info('Requesting ACLED OAuth token');

        try {
            $response = Http::asForm()->post($this->tokenUrl, [
                'username' => $this->email,
                'password' => $this->password,
                'grant_type' => 'password',
                'client_id' => 'acled',
                'scope' => 'authenticated',
            ]);

            if (!$response->successful()) {
                throw new \Exception("ACLED OAuth failed: HTTP " . $response->status() . " " . $response->body());
            }

            $data = $response->json();

            if (!isset($data['access_token'])) {
                throw new \Exception("No access token in ACLED response");
            }

            $token = $data['access_token'];

            // Cache for 23 hours (token valid 24 hours)
            Cache::put($cacheKey, $token, 82800);

            Log::info('ACLED OAuth token obtained');

            return $token;
        } catch (\Exception $e) {
            Log::error("ACLED OAuth error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch conflict events for a country
     * 
     * @param string $countryIso ISO 3166-1 alpha-3 code (e.g., 'CMR' for Cameroon)
     * @param Carbon $startDate Start date for events
     * @param Carbon $endDate End date for events
     * @return array Events fetched
     */
    public function fetchEventsForCountry(string $countryIso, Carbon $startDate, Carbon $endDate): array
    {
        // Check cache
        $cacheKey = "acled:events:{$countryIso}:{$startDate->format('Ymd')}:{$endDate->format('Ymd')}";

        if ($cached = Cache::get($cacheKey)) {
            Log::info("ACLED cache hit for {$countryIso}");
            return $cached;
        }

        $token = $this->getAccessToken();

        Log::info("Fetching ACLED data for {$countryIso}", [
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
        ]);

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->get($this->apiUrl . '/acled/read', [
                    '_format' => 'json',
                    'iso' => $this->getNumericIso($countryIso),
                    'event_date' => $startDate->format('Y-m-d') . '|' . $endDate->format('Y-m-d'),
                    'event_date_where' => 'BETWEEN',
                    'limit' => 500,
                ]);

            if (!$response->successful()) {
                throw new \Exception("ACLED API failed: HTTP " . $response->status() . " " . $response->body());
            }

            $json = $response->json();

            if (!isset($json['data']) || !is_array($json['data'])) {
                throw new \Exception("Invalid ACLED response format");
            }

            $events = $json['data'];

            Log::info("ACLED data fetched", [
                'country' => $countryIso,
                'events' => count($events),
            ]);

            // Cache for 6 hours
            Cache::put($cacheKey, $events, 21600);

            return $events;
        } catch (\Exception $e) {
            Log::error("ACLED API error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convert ISO3 to numeric ISO code from database
     */
    protected function getNumericIso(string $iso3): int
    {
        $country = Country::where('iso3', $iso3)->first();

        if (!$country || !$country->numeric_code) {
            throw new \Exception("Country not found or missing ISO numeric code: {$iso3}");
        }

        return (int) $country->numeric_code;
    }

    /**
     * Ingest ACLED events into database
     * 
     * @param string $countryIso Country ISO code
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return array Statistics (created, updated, skipped)
     */
    public function ingestEvents(string $countryIso, Carbon $startDate, Carbon $endDate): array
    {
        $events = $this->fetchEventsForCountry($countryIso, $startDate, $endDate);

        $stats = [
            'fetched' => count($events),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        // Get or create ACLED data source
        $dataSource = DataSource::firstOrCreate(
            ['code' => 'ACLED'],
            [
                'name' => 'ACLED',
                'source_type' => 'api',
                'description' => 'Armed Conflict Location & Event Data Project',
                'url' => 'https://acleddata.com',
                'update_frequency' => 'weekly',
                'is_active' => true,
            ]
        );

        // Get country
        $country = Country::where('iso3', $countryIso)->first();

        if (!$country) {
            throw new \Exception("Country not found: {$countryIso}");
        }

        foreach ($events as $acledEvent) {
            try {
                $result = $this->storeEvent($acledEvent, $country, $dataSource);

                if ($result['created']) {
                    $stats['created']++;
                } elseif ($result['updated']) {
                    $stats['updated']++;
                } else {
                    $stats['skipped']++;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to store ACLED event", [
                    'event_id' => $acledEvent['event_id_cnty'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
                $stats['skipped']++;
            }
        }

        return $stats;
    }

    /**
     * Store individual ACLED event using normalizer
     */
    protected function storeEvent(array $acledEvent, Country $country, DataSource $dataSource): array
    {
        // Use normalizer for provider-agnostic transformation
        $normalizer = new \App\Services\Conflict\ConflictEventNormalizer('ACLED');
        $normalized = $normalizer->normalize($acledEvent, $country);

        // Add data source
        $normalized['data_source_id'] = $dataSource->id;

        // Store normalized event
        $event = ConflictEvent::updateOrCreate(
            [
                'external_id' => $normalized['external_id'],
                'source_provider' => 'ACLED',
            ],
            $normalized
        );

        return [
            'created' => $event->wasRecentlyCreated,
            'updated' => !$event->wasRecentlyCreated && $event->wasChanged(),
        ];
    }

    /**
     * Map ACLED event type to conflict category
     */
    protected function mapEventTypeToCategory(string $eventType): ?ConflictCategory
    {
        $mapping = [
            'Battles' => 'BATTLES',
            'Explosions/Remote violence' => 'EXPLOSIONS',
            'Violence against civilians' => 'VIOLENCE_CIVILIANS',
            'Protests' => 'PROTESTS',
            'Riots' => 'RIOTS',
            'Strategic developments' => 'STRATEGIC_DEVELOPMENTS',
        ];

        $code = $mapping[$eventType] ?? null;

        if (!$code) {
            return null;
        }

        return ConflictCategory::where('code', $code)->first();
    }

    /**
     * Find district by coordinates (Haversine distance)
     */
    protected function findDistrictByCoordinates(float $lat, float $lng): ?\App\Models\District
    {
        return \App\Models\District::selectRaw('
                *,
                (
                    6371 * acos(
                        cos(radians(?)) * cos(radians(centroid_lat)) *
                        cos(radians(centroid_lng) - radians(?)) +
                        sin(radians(?)) * sin(radians(centroid_lat))
                    )
                ) AS distance
            ', [$lat, $lng, $lat])
            ->having('distance', '<', 50) // Within 50km
            ->orderBy('distance')
            ->first();
    }

    /**
     * Calculate severity score
     */
    protected function calculateSeverityScore(array $acledEvent, ConflictCategory $category): float
    {
        $score = $category->base_severity_weight;

        $fatalities = (int) ($acledEvent['fatalities'] ?? 0);
        if ($fatalities > 0) {
            $score += min($fatalities / 2, 5);
        }

        return min($score, 10.0);
    }
}
