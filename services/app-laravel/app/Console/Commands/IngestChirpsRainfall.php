<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\HazardEvent;
use App\Models\HazardType;
use App\Models\DataSource;
use App\Services\ChirpsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Ingest CHIRPS Rainfall Data Command
 * 
 * Fetches rainfall data from CHIRPS for all active districts,
 * analyzes for flood risks, and creates hazard events when
 * thresholds are exceeded.
 * 
 * Usage:
 *   php artisan chirps:ingest
 *   php artisan chirps:ingest --days=7
 *   php artisan chirps:ingest --district="Mora"
 */
class IngestChirpsRainfall extends Command
{
    /**
     * Command signature
     */
    protected $signature = 'chirps:ingest 
                            {--days=2 : Number of days to fetch (default: 2 for 48h analysis)}
                            {--district= : Specific district name (optional)}';

    /**
     * Command description
     */
    protected $description = 'Ingest CHIRPS rainfall data and create flood hazard events';

    /**
     * Execute the command
     */
    public function handle(ChirpsService $chirps)
    {
        $this->info('🌧️  Starting CHIRPS Rainfall Ingestion...');
        $this->newLine();

        // ================================================================
        // CONFIGURATION
        // ================================================================
        $days = (int) $this->option('days');
        $districtFilter = $this->option('district');
        
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($days);

        $this->info("📅 Period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')} ({$days} days)");
        $this->newLine();

        // ================================================================
        // GET DATA SOURCE RECORD
        // ================================================================
        $dataSource = DataSource::firstOrCreate(
            ['code' => 'CHIRPS'],
            [
                'name' => 'CHIRPS Rainfall Data',
                'description' => 'Climate Hazards Group InfraRed Precipitation with Station data',
                'source_type' => 'api',
                'provider' => 'UC Santa Barbara',
                'api_url' => 'https://data.chc.ucsb.edu/products/CHIRPS-2.0/',
                'update_frequency' => 'daily',
                'active' => true,
            ]
        );

        // ================================================================
        // GET FLOOD HAZARD TYPE
        // ================================================================
        $floodHazardType = HazardType::where('code', 'FLOOD')->first();
        
        if (!$floodHazardType) {
            $this->error('❌ Flood hazard type not found! Run hazard taxonomy seeder first.');
            return 1;
        }

        // ================================================================
        // FETCH DISTRICTS
        // ================================================================
        $query = District::where('active', true);
        
        if ($districtFilter) {
            $query->where('name', 'like', "%{$districtFilter}%");
        }
        
        $districts = $query->get();

        if ($districts->isEmpty()) {
            $this->error('❌ No districts found!');
            return 1;
        }

        $this->info("📍 Processing {$districts->count()} districts...");
        $this->newLine();

        // ================================================================
        // PROCESS EACH DISTRICT
        // ================================================================
        $eventsCreated = 0;
        $districtsProcessed = 0;

        foreach ($districts as $district) {
            $districtsProcessed++;
            
            $this->info("📊 [{$districtsProcessed}/{$districts->count()}] {$district->name}...");

            // Fetch rainfall data
            $rainfallData = $chirps->getRainfallForDistrict($district, $startDate, $endDate);

            if (isset($rainfallData['error'])) {
                $this->error("   ❌ Error: {$rainfallData['message']}");
                continue;
            }

            // Display analysis
            $analysis = $rainfallData['analysis'];
            $this->line("   Total: {$analysis['total_rainfall_mm']}mm");
            $this->line("   Max 48h: {$analysis['max_48h_mm']}mm");
            $this->line("   Flood Risk: {$analysis['flood_risk_level']}");

            // ============================================================
            // CREATE HAZARD EVENT IF THRESHOLD EXCEEDED
            // ============================================================
            if ($chirps->shouldGenerateFloodAlert($rainfallData)) {
                $this->warn("   ⚠️  FLOOD THRESHOLD EXCEEDED! Creating hazard event...");

                $event = HazardEvent::create([
                    'event_code' => 'CHIRPS-FLOOD-' . $district->code . '-' . $endDate->format('Ymd'),
                    'title' => "Heavy Rainfall - {$district->name}",
                    'description' => "Rainfall of {$analysis['max_48h_mm']}mm recorded in 48 hours, exceeding flood threshold of " . ChirpsService::FLOOD_THRESHOLD_48H . "mm.",
                    'hazard_type_id' => $floodHazardType->id,
                    'hazard_category_id' => $floodHazardType->category_id,
                    'district_id' => $district->id,
                    'state_id' => $district->state_id,
                    'country_id' => $district->country_id,
                    'latitude' => $district->centroid_lat,
                    'longitude' => $district->centroid_lng,
                    'detected_at' => $endDate,
                    'started_at' => Carbon::parse($analysis['max_48h_date']),
                    'severity' => $analysis['flood_risk_level'] === 'critical' ? 'critical' : 'high',
                    'severity_score' => $analysis['flood_risk_level'] === 'critical' ? 9.0 : 7.5,
                    'event_data' => [
                        'rainfall_48h_mm' => $analysis['max_48h_mm'],
                        'total_rainfall_mm' => $analysis['total_rainfall_mm'],
                        'days_analyzed' => $analysis['days_analyzed'],
                        'threshold_exceeded_by' => $analysis['max_48h_mm'] - ChirpsService::FLOOD_THRESHOLD_48H,
                    ],
                    'data_source_id' => $dataSource->id,
                    'verified' => false,
                    'status' => 'active',
                ]);

                $this->info("   ✅ Hazard event created: {$event->event_code}");
                $eventsCreated++;
            } else {
                $this->line("   ✓ No alert needed (below threshold)");
            }

            $this->newLine();
        }

        // ================================================================
        // SUMMARY
        // ================================================================
        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('✅ CHIRPS Ingestion Complete!');
        $this->info('═══════════════════════════════════════');
        $this->line("Districts Processed: {$districtsProcessed}");
        $this->line("Hazard Events Created: {$eventsCreated}");
        $this->newLine();

        return 0;
    }
}