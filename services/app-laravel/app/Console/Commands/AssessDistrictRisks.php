<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RiskScoringService;
use App\Models\District;
use Carbon\Carbon;

class AssessDistrictRisks extends Command
{
    protected $signature = 'risk:assess 
                            {district? : District name or ID (optional - runs all if omitted)}
                            {--date= : Assessment date (default: today)}
                            {--days=30 : Days to analyze for temporal data}
                            {--force : Force recalculation even if assessment exists}';

    protected $description = 'Calculate compound risk assessments for districts';

    public function handle(RiskScoringService $scoringService)
    {
        $this->info('🧠 District Risk Assessment Engine');
        $this->line('═══════════════════════════════════════════════════════════');

        $assessmentDate = $this->option('date') 
            ? Carbon::parse($this->option('date'))
            : Carbon::today();
        
        $daysAnalyzed = (int) $this->option('days');
        $force = $this->option('force');

        $this->info("📅 Assessment Date: {$assessmentDate->format('Y-m-d')}");
        $this->info("📊 Analysis Window: {$daysAnalyzed} days");
        $this->newLine();

        // Get districts to assess
        $districtInput = $this->argument('district');
        
        if ($districtInput) {
            $district = is_numeric($districtInput)
                ? District::find($districtInput)
                : District::where('name', 'like', "%{$districtInput}%")->first();

            if (!$district) {
                $this->error("❌ District not found: {$districtInput}");
                return 1;
            }

            $districts = collect([$district]);
        } else {
            $districts = District::all();
            $this->info("📍 Assessing all districts ({$districts->count()} total)");
            $this->newLine();
        }

        // Progress bar
        $bar = $this->output->createProgressBar($districts->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        $results = [
            'assessed' => 0,
            'skipped' => 0,
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'errors' => 0,
        ];

        foreach ($districts as $district) {
            $bar->setMessage("Assessing {$district->name}...");

            try {
                // Check if assessment already exists
                if (!$force) {
                    $existing = \App\Models\DistrictRiskAssessment::where('district_id', $district->id)
                        ->where('assessment_date', $assessmentDate)
                        ->first();

                    if ($existing) {
                        $results['skipped']++;
                        $bar->advance();
                        continue;
                    }
                }

                // Calculate assessment
                $assessment = $scoringService->assessDistrict($district, $assessmentDate, $daysAnalyzed);

                $results['assessed']++;
                $results[$assessment->risk_level]++;

                $bar->advance();

            } catch (\Exception $e) {
                $results['errors']++;
                $this->newLine();
                $this->error("  ❌ Error assessing {$district->name}: {$e->getMessage()}");
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->line('═══════════════════════════════════════════════════════════');
        $this->info('📊 ASSESSMENT SUMMARY');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->line("Districts Assessed:  {$results['assessed']}");
        $this->line("Skipped (existing):  {$results['skipped']}");
        
        if ($results['errors'] > 0) {
            $this->line("Errors:              {$results['errors']}");
        }

        $this->newLine();
        $this->line('Risk Level Distribution:');
        $this->line("  🔴 Critical:  {$results['critical']}");
        $this->line("  🟠 High:      {$results['high']}");
        $this->line("  🟡 Medium:    {$results['medium']}");
        $this->line("  🟢 Low:       {$results['low']}");

        // Show critical districts if any
        if ($results['critical'] > 0) {
            $this->newLine();
            $this->warn('⚠️  CRITICAL DISTRICTS REQUIRING IMMEDIATE ATTENTION:');
            
            $critical = \App\Models\DistrictRiskAssessment::with('district')
                ->where('assessment_date', $assessmentDate)
                ->where('risk_level', 'critical')
                ->get();

            foreach ($critical as $assessment) {
                $this->line("  • {$assessment->district->name} (Score: {$assessment->composite_score}/10)");
            }
        }

        $this->newLine();
        $this->info('✅ Risk assessment complete!');

        return 0;
    }
}