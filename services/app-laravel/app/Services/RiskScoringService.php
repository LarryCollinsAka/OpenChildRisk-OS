<?php

namespace App\Services;

use App\Models\District;
use App\Models\DistrictRiskAssessment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Risk Scoring Service
 * 
 * Explainable compound risk scoring engine.
 * 
 * Philosophy:
 * - Interpretable over complex
 * - Auditable over black-box
 * - Human-readable over ML hype
 * 
 * Scoring Components:
 * 1. Climate Score (rainfall anomalies, drought indicators)
 * 2. Conflict Score (ACLED events, fatalities, displacement)
 * 3. Vulnerability Score (WASH, health infrastructure, population)
 * 4. Access Score (operational constraints, security)
 * 
 * Output: Explainable risk assessment with recommendations
 */
class RiskScoringService
{
    // Scoring weights (must sum to 1.0)
    const DEFAULT_WEIGHTS = [
        'climate' => 0.30,
        'conflict' => 0.25,
        'vulnerability' => 0.30,
        'access' => 0.15,
    ];

    // Risk level thresholds
    const RISK_THRESHOLDS = [
        'critical' => 7.5,
        'high' => 5.5,
        'medium' => 3.5,
        'low' => 0,
    ];

    /**
     * Calculate comprehensive risk assessment for a district
     */
    public function assessDistrict(
        District $district,
        ?Carbon $assessmentDate = null,
        int $daysAnalyzed = 30
    ): DistrictRiskAssessment {

        $assessmentDate = $assessmentDate ?? Carbon::today();

        Log::info("Calculating risk assessment for {$district->name}", [
            'district_id' => $district->id,
            'date' => $assessmentDate->format('Y-m-d'),
        ]);

        // Calculate component scores
        $climateScore = $this->calculateClimateScore($district, $assessmentDate, $daysAnalyzed);
        $conflictScore = $this->calculateConflictScore($district, $assessmentDate, $daysAnalyzed);
        $vulnerabilityScore = $this->calculateVulnerabilityScore($district);
        $accessScore = $this->calculateAccessScore($district);

        // Calculate composite
        $composite = $this->calculateCompositeScore([
            'climate' => $climateScore['score'],
            'conflict' => $conflictScore['score'],
            'vulnerability' => $vulnerabilityScore['score'],
            'access' => $accessScore['score'],
        ]);

        // Determine risk level
        $riskLevel = $this->determineRiskLevel($composite['score']);

        // Generate explanation
        $explanation = $this->generateExplanation(
            $district,
            $climateScore,
            $conflictScore,
            $vulnerabilityScore,
            $accessScore,
            $composite
        );

        // Determine recommendation level
        $recommendationLevel = $this->determineRecommendationLevel($composite['score'], $accessScore['score']);

        // Create or update assessment
        $assessment = DistrictRiskAssessment::updateOrCreate(
            [
                'district_id' => $district->id,
                'assessment_date' => $assessmentDate,
            ],
            [
                'climate_score' => $climateScore['score'],
                'climate_factors' => $climateScore['factors'],
                'conflict_score' => $conflictScore['score'],
                'conflict_factors' => $conflictScore['factors'],
                'health_score' => null, // TODO: Implement
                'health_factors' => null,
                'vulnerability_score' => $vulnerabilityScore['score'],
                'vulnerability_factors' => $vulnerabilityScore['factors'],
                'access_score' => $accessScore['score'],
                'access_factors' => $accessScore['factors'],
                'composite_score' => $composite['score'],
                'risk_level' => $riskLevel,
                'confidence_score' => $composite['confidence'],
                'primary_drivers' => $composite['drivers'],
                'explanation' => $explanation,
                'recommendation_level' => $recommendationLevel,
                'recommended_interventions' => $this->recommendInterventions($composite, $vulnerabilityScore),
                'population_at_risk' => $district->population ?? 0,
                'calculation_method' => 'v1.0',
                'scoring_weights' => self::DEFAULT_WEIGHTS,
                'data_sources' => $composite['sources'],
                'data_completeness' => $composite['completeness'],
                'days_analyzed' => $daysAnalyzed,
                'calculated_at' => now(),
                'calculated_by' => 'system',
            ]
        );

        Log::info("Risk assessment complete", [
            'district' => $district->name,
            'risk_level' => $riskLevel,
            'composite_score' => $composite['score'],
        ]);

        return $assessment;
    }

    /**
     * Calculate climate risk score
     */
    protected function calculateClimateScore(District $district, Carbon $date, int $days): array
    {
        // TODO: Implement actual climate scoring from CHIRPS data
        // For now, return placeholder

        return [
            'score' => 0.0,
            'factors' => [
                'rainfall_anomaly_pct' => 0,
                'days_analyzed' => $days,
                'data_available' => false,
            ],
        ];
    }

    /**
     * Calculate conflict risk score
     */
    protected function calculateConflictScore(District $district, Carbon $date, int $days): array
    {
        $startDate = $date->copy()->subDays($days);

        $events = DB::table('conflict_events')
            ->where('district_id', $district->id)
            ->where('event_date', '>=', $startDate)
            ->where('event_date', '<=', $date)
            ->where('status', 'active')
            ->get();

        if ($events->isEmpty()) {
            return [
                'score' => 0.0,
                'factors' => [
                    'event_count' => 0,
                    'total_fatalities' => 0,
                    'days_analyzed' => $days,
                ],
            ];
        }

        $eventCount = $events->count();
        $totalFatalities = $events->sum('fatalities');

        // Scoring logic:
        // Base score from event frequency (0-5)
        $frequencyScore = min($eventCount / 2, 5.0);

        // Additional score from fatalities (0-5)
        $fatalityScore = min($totalFatalities / 5, 5.0);

        $score = min($frequencyScore + $fatalityScore, 10.0);

        return [
            'score' => $score,
            'factors' => [
                'event_count' => $eventCount,
                'total_fatalities' => $totalFatalities,
                'days_analyzed' => $days,
                'violent_events' => $events->whereIn('severity_score', '>', 5)->count(),
            ],
        ];
    }

    /**
     * Calculate vulnerability score (WASH, health, population)
     */
    protected function calculateVulnerabilityScore(District $district): array
    {
        $score = 0.0;
        $factors = [];

        // WASH coverage (inverse scoring - low coverage = high vulnerability)
        $washCoverage = $district->wash_coverage ?? 0;
        $washScore = 10 - ($washCoverage * 10); // 0% coverage = 10, 100% = 0
        $score += $washScore * 0.4; // 40% weight

        // Sanitation coverage (inverse)
        $sanitationCoverage = $district->sanitation_coverage ?? 0;
        $sanitationScore = 10 - ($sanitationCoverage * 10);
        $score += $sanitationScore * 0.3; // 30% weight

        // Population density (higher = higher vulnerability)
        $population = $district->population ?? 0;
        $area = $district->area_sq_km ?? 1;
        $density = $population / $area;
        $densityScore = min($density / 50, 10); // Cap at 500 people/km²
        $score += $densityScore * 0.3; // 30% weight

        $factors = [
            'wash_coverage' => $washCoverage,
            'sanitation_coverage' => $sanitationCoverage,
            'population' => $population,
            'population_density' => round($density, 2),
        ];

        return [
            'score' => min($score, 10.0),
            'factors' => $factors,
        ];
    }

    /**
     * Calculate access score (operational feasibility)
     */
    protected function calculateAccessScore(District $district): array
    {
        // Check for access constraints
        $constraint = DB::table('district_access_constraints')
            ->where('district_id', $district->id)
            ->where('assessed_at', '>=', now()->subDays(90))
            ->orderBy('assessed_at', 'desc')
            ->first();

        if (!$constraint) {
            return [
                'score' => 0.0, // No constraints = fully accessible
                'factors' => [
                    'access_level' => 'full',
                    'assessment_available' => false,
                ],
            ];
        }

        // Inverse scoring: More constraints = higher score (worse)
        $accessLevelScores = [
            'full' => 0,
            'partial' => 3,
            'limited' => 6,
            'no_access' => 10,
        ];

        $score = $accessLevelScores[$constraint->access_level] ?? 0;

        // Additional penalties
        if (!$constraint->road_access) $score += 2;
        if (!$constraint->humanitarian_access) $score += 3;

        return [
            'score' => min($score, 10.0),
            'factors' => [
                'access_level' => $constraint->access_level,
                'road_access' => $constraint->road_access,
                'humanitarian_access' => $constraint->humanitarian_access,
                'security_risk_level' => $constraint->security_risk_level,
            ],
        ];
    }

    /**
     * Calculate weighted composite score
     */
    protected function calculateCompositeScore(array $componentScores): array
    {
        $weights = self::DEFAULT_WEIGHTS;
        $score = 0.0;
        $dataSources = [];
        $dataPresent = [];

        foreach ($componentScores as $component => $value) {
            if ($value !== null) {
                $score += $value * $weights[$component];
                $dataPresent[$component] = true;
                $dataSources[] = $component;
            } else {
                $dataPresent[$component] = false;
            }
        }

        // Calculate data completeness
        $completeness = count(array_filter($dataPresent)) / count($dataPresent);

        // Identify primary drivers (top contributors)
        $weightedScores = [];
        foreach ($componentScores as $component => $value) {
            if ($value !== null) {
                $weightedScores[$component] = $value * $weights[$component];
            }
        }
        arsort($weightedScores);
        $drivers = array_keys(array_slice($weightedScores, 0, 3));

        return [
            'score' => round($score, 2),
            'confidence' => $completeness,
            'drivers' => $drivers,
            'sources' => $dataSources,
            'completeness' => $completeness,
        ];
    }

    /**
     * Determine categorical risk level
     */
    protected function determineRiskLevel(float $score): string
    {
        foreach (self::RISK_THRESHOLDS as $level => $threshold) {
            if ($score >= $threshold) {
                return $level;
            }
        }
        return 'low';
    }

    /**
     * Determine operational recommendation level
     */
    protected function determineRecommendationLevel(float $compositeScore, float $accessScore): string
    {
        // If access is severely constrained, recommendations may be limited
        if ($accessScore >= 8 && $compositeScore >= 7) {
            // High risk but no access = monitor remotely
            return 'monitor';
        }

        if ($compositeScore >= 7.5) return 'emergency';
        if ($compositeScore >= 5.5) return 'respond';
        if ($compositeScore >= 3.5) return 'prepare';
        return 'monitor';
    }

    /**
     * Generate human-readable explanation
     */
    protected function generateExplanation(
        District $district,
        array $climate,
        array $conflict,
        array $vulnerability,
        array $access,
        array $composite
    ): string {

        $riskLevel = $this->determineRiskLevel($composite['score']);
        $riskLevelText = strtoupper($riskLevel);

        $lines = ["Why {$district->name} is {$riskLevelText} (Score: {$composite['score']}/10):"];

        // Climate factors
        if ($climate['score'] > 5) {
            $lines[] = "- Climate risk elevated";
        }

        // Conflict factors
        if ($conflict['score'] > 5) {
            $lines[] = "- Conflict escalation: {$conflict['factors']['event_count']} events in last {$conflict['factors']['days_analyzed']} days";
            if ($conflict['factors']['total_fatalities'] > 0) {
                $lines[] = "  → {$conflict['factors']['total_fatalities']} fatalities reported";
            }
        } elseif ($conflict['factors']['event_count'] > 0) {
            $lines[] = "- Conflict activity: {$conflict['factors']['event_count']} events (monitored)";
        }

        // Vulnerability factors
        if ($vulnerability['score'] > 5) {
            $wash = round($vulnerability['factors']['wash_coverage'] * 100);
            $sanitation = round($vulnerability['factors']['sanitation_coverage'] * 100);
            $lines[] = "- Infrastructure vulnerability:";
            $lines[] = "  → WASH coverage: {$wash}%";
            $lines[] = "  → Sanitation coverage: {$sanitation}%";
        }

        // Access constraints
        if ($access['score'] > 5) {
            $accessLevel = $access['factors']['access_level'];
            $lines[] = "- Operational constraints: {$accessLevel} access";
            if (!$access['factors']['road_access']) {
                $lines[] = "  → Road access limited";
            }
            if (!$access['factors']['humanitarian_access']) {
                $lines[] = "  → Humanitarian access restricted";
            }
        }

        // Population exposure
        $population = number_format($district->population ?? 0);
        $lines[] = "- Population at risk: {$population}";

        return implode("\n", $lines);
    }

    /**
     * Recommend priority interventions
     */
    protected function recommendInterventions(array $composite, array $vulnerability): array
    {
        $interventions = [];

        if ($vulnerability['factors']['wash_coverage'] < 0.5) {
            $interventions[] = 'WASH';
        }

        if ($vulnerability['factors']['sanitation_coverage'] < 0.5) {
            $interventions[] = 'Sanitation';
        }

        if ($composite['score'] > 7) {
            $interventions[] = 'Health screening';
            $interventions[] = 'Nutrition assessment';
        }

        return $interventions;
    }
}
