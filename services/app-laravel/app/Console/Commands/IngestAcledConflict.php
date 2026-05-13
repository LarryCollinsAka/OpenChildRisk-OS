<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AcledService;
use Carbon\Carbon;

class IngestAcledConflict extends Command
{
    protected $signature = 'acled:ingest 
                            {country? : Country ISO3 code (default: CMR)}
                            {--days=30 : Number of days to fetch}
                            {--from= : Start date (YYYY-MM-DD)}
                            {--to= : End date (YYYY-MM-DD)}';

    protected $description = 'Ingest conflict events from ACLED API';

    public function handle(AcledService $acledService)
    {
        $this->info('⚔️  Starting ACLED Conflict Ingestion...');
        
        $countryIso = strtoupper($this->argument('country') ?? 'CMR');
        
        // Determine date range
        if ($this->option('from') && $this->option('to')) {
            $startDate = Carbon::parse($this->option('from'));
            $endDate = Carbon::parse($this->option('to'));
        } else {
            $days = (int) $this->option('days');
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subDays($days);
        }

        $this->info("📅 Period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        $this->info("🌍 Country: {$countryIso}");
        $this->newLine();

        try {
            $stats = $acledService->ingestEvents($countryIso, $startDate, $endDate);

            $this->line('═══════════════════════════════════════');
            $this->info('✅ ACLED Ingestion Complete!');
            $this->line('═══════════════════════════════════════');
            $this->line("Events Fetched: {$stats['fetched']}");
            $this->line("Events Created: {$stats['created']}");
            $this->line("Events Updated: {$stats['updated']}");
            $this->line("Events Skipped: {$stats['skipped']}");

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}