<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\District;
use App\Models\HazardEvent;
use App\Models\HazardType;
use App\Models\DataSource;
use App\Services\ChirpsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IngestChirpsRainfall extends Command
{
    protected $signature = 'chirps:ingest 
                            {--days=7 : Number of days to analyze}
                            {--district= : Optional district name filter}';

    protected $description = 'Ingest CHIRPS rainfall data and generate flood hazard events';

    public function handle(ChirpsService $chirpsService)
    {
        $this->info('🌧️  Starting CHIRPS Rainfall Ingestion...');
        
        $days = (int) $this->option('days');
        $districtFilter = $this->option('district');

        // Get safe date range (accounts for CHIRPS Final ~21 day lag)
        [$startDate, $endDate] = ChirpsService::getSafeDateRange($days);

        $this->info("📅 Requested Period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')} ({$days} days)");
        $this->line("ℹ️  Using CHIRPS Final product (validated data with ~21 day lag for station blending)");
        $this->line("ℹ️  Will auto-fallback to earlier months if requested dates unavailable");
        $this->newLine();

        // Get districts to process
        $districtsQuery = District::query();
        
        if ($districtFilter) {
            $districtsQuery->where('name', 'like', "%{$districtFilter}%");
        }
        
        $districts = $districtsQuery->get();

        if ($districts->isEmpty()) {
            $this->error('❌ No districts found!');
            return 1;
        }

        $this->info("📍 Processing {$districts->count()} districts...");
        $this->newLine();

        $createdCount = 0;
        $updatedCount = 0;
        $errorCount = 0;

        foreach ($districts as $index => $district) {
            $districtNum = $index + 1;
            $this->info("📊 [{$districtNum}/{$districts->count()}] {$district->name}...");

            try {
                // RATE LIMITING: Sleep 3 seconds between districts
                if ($index > 0) {
                    $this->line("   ⏱️  Waiting 3 seconds (API rate limiting)...");
                    sleep(3);
                }

                // Fetch rainfall data (auto-fallback if needed)
                $rainfallData = $chirpsService->getRainfallForDistrict(
                    $district,
                    $startDate,
                    $endDate
                );

                // Show fallback notice if dates changed
                if (!empty($rainfallData['fallback_notice'])) {
                    $this->warn("   ⚠️  {$rainfallData['fallback_notice']}");
                }

                // Display summary
                $analysis = $rainfallData['analysis'];
                $this->line("   Period: {$rainfallData['period']['start']} to {$rainfallData['period']['end']}");
                $this->line("   Total: {$analysis['total_rainfall_mm']}mm");
                $this->line("   Max 48h: {$analysis['max_48h_mm']}mm");
                $this->line("   Flood Risk: {$analysis['flood_risk_level']}");

                // Check if flood alert needed
                if ($chirpsService->shouldGenerateFloodAlert($rainfallData)) {
                    $this->warn("   ⚠️  FLOOD THRESHOLD EXCEEDED! Creating/updating hazard event...");

                    $result = $this->createOrUpdateHazardEvent($district, $rainfallData);
                    
                    if ($result['created']) {
                        $createdCount++;
                        $this->line("   ✅ Hazard event created: {$result['event_code']}");
                    } else {
                        $updatedCount++;
                        $this->line("   🔄 Hazard event updated: {$result['event_code']}");
                    }
                } else {
                    $this->line("   ✅ No flood risk detected");
                }

                $this->newLine();

            } catch (\Exception $e) {
                $errorCount++;
                $this->error("   ❌ Error: " . $e->getMessage());
                $this->newLine();
                
                // Log but continue to next district
                Log::error("CHIRPS ingestion failed for {$district->name}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Continue to next district instead of stopping
                continue;
            }
        }

        // Summary
        $this->line('═══════════════════════════════════════');
        $this->info('✅ CHIRPS Ingestion Complete!');
        $this->line('═══════════════════════════════════════');
        $this->line("Districts Processed: {$districts->count()}");
        $this->line("Hazard Events Created: {$createdCount}");
        $this->line("Hazard Events Updated: {$updatedCount}");
        
        if ($errorCount > 0) {
            $this->warn("Errors Encountered: {$errorCount}");
        }

        return 0;
    }

    protected function createOrUpdateHazardEvent($district, $rainfallData)
    {
        $analysis = $rainfallData['analysis'];
        
        // Get or create flood hazard type
        $floodType = HazardType::where('name', 'Flooding')->firstOrFail();
        
        // Get or create CHIRPS data source
        $dataSource = DataSource::firstOrCreate(
            ['name' => 'CHIRPS Rainfall Data'],
            [
                'description' => 'Climate Hazards Group InfraRed Precipitation with Station data',
                'url' => 'https://www.chc.ucsb.edu/data/chirps',
                'update_frequency' => 'daily',
                'is_active' => true,
            ]
        );

        // Use the actual dates from rainfallData (may be fallback dates)
        $actualStartDate = Carbon::parse($rainfallData['period']['start']);
        $actualEndDate = Carbon::parse($rainfallData['period']['end']);

        // Create event code with actual dates
        $eventCode = sprintf(
            'CHIRPS-FLOOD-%s-%s',
            $district->code,
            $actualEndDate->format('Ymd')
        );

        // Map risk level to severity
        $severityMap = [
            'low' => 3.0,
            'medium' => 5.0,
            'high' => 7.5,
            'critical' => 9.0,
        ];
        $severity = $severityMap[$analysis['flood_risk_level']] ?? 5.0;

        // Determine status
        $status = $analysis['flood_risk_level'] === 'critical' ? 'critical' : 'active';

        // Build description with fallback notice if applicable
        $description = sprintf(
            'Significant rainfall detected: %.2fmm in 48 hours, exceeding flood threshold of %.2fmm. Total rainfall over %d days: %.2fmm.',
            $analysis['max_48h_mm'],
            ChirpsService::FLOOD_THRESHOLD_48H,
            $analysis['days_analyzed'],
            $analysis['total_rainfall_mm']
        );

        if (!empty($rainfallData['fallback_notice'])) {
            $description .= ' ' . $rainfallData['fallback_notice'];
        }

        // Create or update hazard event
        $hazardEvent = HazardEvent::updateOrCreate(
            [
                'event_code' => $eventCode,
            ],
            [
                'title' => "Heavy Rainfall - {$district->name}",
                'description' => $description,
                'hazard_type_id' => $floodType->id,
                'district_id' => $district->id,
                'state_id' => $district->state_id,
                'country_id' => $district->country_id,
                'severity_score' => $severity,
                'status' => $status,
                'start_date' => $actualStartDate,
                'end_date' => $actualEndDate,
                'detected_at' => now(),
                'data_source_id' => $dataSource->id,
                'event_data' => [
                    'rainfall_48h_mm' => $analysis['max_48h_mm'],
                    'rainfall_48h_date' => $analysis['max_48h_date'],
                    'total_rainfall_mm' => $analysis['total_rainfall_mm'],
                    'average_daily_mm' => $analysis['average_daily_mm'],
                    'days_analyzed' => $analysis['days_analyzed'],
                    'threshold_mm' => ChirpsService::FLOOD_THRESHOLD_48H,
                    'threshold_exceeded_by' => $analysis['max_48h_mm'] - ChirpsService::FLOOD_THRESHOLD_48H,
                    'flood_risk_level' => $analysis['flood_risk_level'],
                    'daily_rainfall' => $rainfallData['rainfall'],
                    'coordinates' => $rainfallData['coordinates'],
                    'used_fallback_dates' => $rainfallData['used_fallback'] ?? false,
                ],
            ]
        );

        return [
            'created' => $hazardEvent->wasRecentlyCreated,
            'event_code' => $eventCode,
        ];
    }
}