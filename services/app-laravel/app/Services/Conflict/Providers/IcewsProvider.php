<?php

namespace App\Services\Conflict\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * ICEWS Provider Service
 * 
 * Integrated Crisis Early Warning System
 * Source: Harvard Dataverse (US Government data)
 * 
 * Coverage: Global, 1995-present
 * Update: Daily
 * Access: Open (public domain)
 * 
 * Use Cases:
 * - Historical pattern analysis
 * - Trend detection
 * - Escalation signals
 * - ML training data
 * 
 * Signal Type: PREDICTIVE
 */
class IcewsProvider
{
    protected string $dataverseUrl = 'https://dataverse.harvard.edu';
    protected string $apiBase = 'https://dataverse.harvard.edu/api/access/datafile';

    /**
     * ICEWS event type to CAMEO code mapping
     * CAMEO = Conflict and Mediation Event Observations
     */
    const EVENT_TYPE_MAPPING = [
        // Violent events
        'Assault' => ['18*'],
        'Fight' => ['19*'],
        'Use unconventional violence' => ['18*', '20*'],

        // Protests
        'Protest' => ['14*'],
        'Demonstrate or rally' => ['14*'],

        // Threats
        'Threaten' => ['13*'],
        'Coerce' => ['17*'],

        // Strategic
        'Reduce relations' => ['16*'],
        'Engage in material cooperation' => ['06*'],
    ];

    /**
     * Download ICEWS data file for a specific year
     * Files are hosted on Harvard Dataverse
     */
    public function downloadYearlyFile(int $year): string
    {
        $fileId = $this->getFileIdForYear($year);

        if (!$fileId) {
            throw new \Exception("No ICEWS file available for year {$year}");
        }

        $cacheKey = "icews:file:{$year}";

        if ($cached = Cache::get($cacheKey)) {
            Log::info("ICEWS file cache hit for {$year}");
            return $cached;
        }

        Log::info("Downloading ICEWS data for {$year}", ['file_id' => $fileId]);

        $url = "{$this->apiBase}/{$fileId}";

        $response = Http::timeout(300) // 5 minutes for large files
            ->get($url);

        if (!$response->successful()) {
            throw new \Exception("Failed to download ICEWS file for {$year}: HTTP " . $response->status());
        }

        // Store temporarily
        $filename = "icews_{$year}.tab";
        $path = "temp/{$filename}";

        Storage::put($path, $response->body());

        // Cache path for 24 hours
        Cache::put($cacheKey, $path, 86400);

        Log::info("ICEWS file downloaded", [
            'year' => $year,
            'size' => strlen($response->body()),
            'path' => $path,
        ]);

        return $path;
    }

    /**
     * Get Harvard Dataverse file ID for year
     * 
     * Strategy:
     * 1. Try database configuration (admin-configured via UI)
     * 2. Try Dataverse API discovery (search for files)
     * 3. Fallback to hardcoded defaults
     */
    protected function getFileIdForYear(int $year): ?string
    {
        // Try 1: Check database configuration
        $configKey = "file_id_{$year}";
        $config = DB::table('provider_configurations')
            ->join('conflict_provider_sources', 'provider_configurations.provider_source_id', '=', 'conflict_provider_sources.id')
            ->where('conflict_provider_sources.code', 'ICEWS')
            ->where('provider_configurations.config_key', $configKey)
            ->where('provider_configurations.is_active', true)
            ->first();

        if ($config) {
            Log::info("ICEWS file ID from database config", ['year' => $year, 'file_id' => $config->config_value]);
            return $config->config_value;
        }

        // Try 2: Dataverse API discovery
        try {
            $fileId = $this->discoverFileIdFromDataverse($year);
            if ($fileId) {
                Log::info("ICEWS file ID from Dataverse API", ['year' => $year, 'file_id' => $fileId]);

                // Store for future use
                $this->storeFileIdConfig($year, $fileId);

                return $fileId;
            }
        } catch (\Exception $e) {
            Log::warning("ICEWS Dataverse API discovery failed", ['year' => $year, 'error' => $e->getMessage()]);
        }

        // Try 3: Hardcoded fallback (known stable IDs)
        $fallbackIds = [
            2024 => '10488291',
            2023 => '7570127',
            2022 => '6631516',
            2021 => '5154715',
            2020 => '4310404',
        ];

        if (isset($fallbackIds[$year])) {
            Log::info("ICEWS file ID from fallback", ['year' => $year, 'file_id' => $fallbackIds[$year]]);
            return $fallbackIds[$year];
        }

        Log::error("No ICEWS file ID found", ['year' => $year]);
        return null;
    }

    /**
     * Discover file ID from Harvard Dataverse API
     */
    protected function discoverFileIdFromDataverse(int $year): ?string
    {
        // Harvard Dataverse Search API
        $searchUrl = "https://dataverse.harvard.edu/api/search";

        $response = Http::timeout(30)->get($searchUrl, [
            'q' => "ICEWS Events {$year}",
            'type' => 'file',
            'subtree' => 'icews',
            'per_page' => 10,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Dataverse API failed: HTTP " . $response->status());
        }

        $data = $response->json();

        if (empty($data['data']['items'])) {
            return null;
        }

        // Find file matching the year
        foreach ($data['data']['items'] as $item) {
            if (isset($item['name']) && stripos($item['name'], (string)$year) !== false) {
                return $item['file_id'] ?? null;
            }
        }

        return null;
    }

    /**
     * Store discovered file ID in configuration
     */
    protected function storeFileIdConfig(int $year, string $fileId): void
    {
        $provider = \App\Models\ConflictProviderSource::where('code', 'ICEWS')->first();

        if (!$provider) {
            return;
        }

        DB::table('provider_configurations')->updateOrInsert(
            [
                'provider_source_id' => $provider->id,
                'config_key' => "file_id_{$year}",
            ],
            [
                'config_value' => $fileId,
                'value_type' => 'string',
                'description' => "Harvard Dataverse file ID for ICEWS {$year} data",
                'is_active' => true,
                'is_editable_via_ui' => true,
                'last_verified_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        Log::info("Stored ICEWS file ID config", ['year' => $year, 'file_id' => $fileId]);
    }
    /**
     * Parse ICEWS tab-delimited file
     * 
     * ICEWS format:
     * Event ID, Event Date, Source Name, Source Sectors, Source Country,
     * Event Text, CAMEO Code, Intensity, Target Name, Target Sectors,
     * Target Country, Story ID, Sentence Number, Publisher, City,
     * District, Province, Country, Latitude, Longitude
     */
    public function parseFile(string $path, string $countryFilter = null): array
    {
        $fullPath = Storage::path($path);

        if (!file_exists($fullPath)) {
            throw new \Exception("ICEWS file not found: {$path}");
        }

        Log::info("Parsing ICEWS file", ['path' => $path]);

        $events = [];
        $handle = fopen($fullPath, 'r');

        // Skip header row
        fgets($handle);

        $lineNumber = 1;

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;

            $fields = str_getcsv($line, "\t");

            // ICEWS has 20 fields
            if (count($fields) < 20) {
                continue;
            }

            // Parse event
            $event = [
                'event_id' => $fields[0],
                'event_date' => $fields[1],
                'source_name' => $fields[2],
                'source_country' => $fields[4],
                'event_text' => $fields[6],
                'cameo_code' => $fields[7],
                'intensity' => $fields[8],
                'target_name' => $fields[9],
                'target_country' => $fields[11],
                'story_id' => $fields[12],
                'publisher' => $fields[14],
                'city' => $fields[15],
                'province' => $fields[17],
                'country' => $fields[18],
                'latitude' => $fields[19],
                'longitude' => $fields[20] ?? null,
            ];

            // Filter by country if specified
            if ($countryFilter && $event['country'] !== $countryFilter) {
                continue;
            }

            $events[] = $event;

            // Limit to prevent memory issues (can process in batches)
            if (count($events) >= 10000) {
                break;
            }
        }

        fclose($handle);

        Log::info("ICEWS file parsed", [
            'path' => $path,
            'lines_processed' => $lineNumber,
            'events_extracted' => count($events),
        ]);

        return $events;
    }

    /**
     * Fetch events for country and date range
     */
    public function fetchEvents(string $countryName, Carbon $startDate, Carbon $endDate): array
    {
        $events = [];

        // Determine which years to download
        $startYear = $startDate->year;
        $endYear = $endDate->year;

        for ($year = $startYear; $year <= $endYear; $year++) {
            try {
                $filePath = $this->downloadYearlyFile($year);
                $yearEvents = $this->parseFile($filePath, $countryName);

                // Filter by date range
                $filtered = array_filter($yearEvents, function ($event) use ($startDate, $endDate) {
                    $eventDate = Carbon::parse($event['event_date']);
                    return $eventDate->between($startDate, $endDate);
                });

                $events = array_merge($events, $filtered);
            } catch (\Exception $e) {
                Log::warning("Failed to fetch ICEWS data for {$year}: {$e->getMessage()}");
            }
        }

        return $events;
    }

    /**
     * Map CAMEO code to event type
     */
    public function getEventTypeFromCameo(string $cameoCode): ?string
    {
        // CAMEO codes are hierarchical: 14 = Protest, 141 = Demonstrate
        $rootCode = substr($cameoCode, 0, 2);

        $mapping = [
            '13' => 'Threaten',
            '14' => 'Protest',
            '17' => 'Coerce',
            '18' => 'Assault',
            '19' => 'Fight',
            '20' => 'Use unconventional violence',
        ];

        return $mapping[$rootCode] ?? null;
    }
}
