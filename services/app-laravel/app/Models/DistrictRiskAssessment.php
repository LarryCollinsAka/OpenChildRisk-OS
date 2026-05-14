<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * DistrictRiskAssessment Model
 * 
 * Unified compound risk scoring - the operational decision engine.
 * 
 * Orchestrates:
 * - Climate risk (rainfall anomalies)
 * - Conflict risk (ACLED events)
 * - Vulnerability (WASH, health, population)
 * - Access constraints (operational feasibility)
 * 
 * Purpose: Transform fragmented indicators into actionable intelligence.
 */
class DistrictRiskAssessment extends Model
{
    use HasUuids;

    protected $fillable = [
        'district_id',
        'assessment_date',
        'climate_score',
        'climate_factors',
        'conflict_score',
        'conflict_factors',
        'health_score',
        'health_factors',
        'vulnerability_score',
        'vulnerability_factors',
        'access_score',
        'access_factors',
        'composite_score',
        'risk_level',
        'confidence_score',
        'primary_drivers',
        'explanation',
        'recommendation_level',
        'recommended_interventions',
        'population_at_risk',
        'calculation_method',
        'scoring_weights',
        'data_sources',
        'data_completeness',
        'days_analyzed',
        'calculated_at',
        'calculated_by',
        'triggered_by_event_id',
        'calculation_notes',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'climate_score' => 'float',
        'climate_factors' => 'array',
        'conflict_score' => 'float',
        'conflict_factors' => 'array',
        'health_score' => 'float',
        'health_factors' => 'array',
        'vulnerability_score' => 'float',
        'vulnerability_factors' => 'array',
        'access_score' => 'float',
        'access_factors' => 'array',
        'composite_score' => 'float',
        'confidence_score' => 'float',
        'primary_drivers' => 'array',
        'recommended_interventions' => 'array',
        'population_at_risk' => 'integer',
        'scoring_weights' => 'array',
        'data_sources' => 'array',
        'data_completeness' => 'float',
        'days_analyzed' => 'integer',
        'calculated_at' => 'datetime',
    ];

    /**
     * Belongs to district
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Scope: Critical risk only
     */
    public function scopeCritical($query)
    {
        return $query->where('risk_level', 'critical');
    }

    /**
     * Scope: High or critical risk
     */
    public function scopeHighRisk($query)
    {
        return $query->whereIn('risk_level', ['high', 'critical']);
    }

    /**
     * Scope: Recent assessments
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('assessment_date', '>=', now()->subDays($days));
    }

    /**
     * Scope: By recommendation level
     */
    public function scopeRequiresAction($query, array $levels = ['respond', 'emergency'])
    {
        return $query->whereIn('recommendation_level', $levels);
    }

    /**
     * Get human-readable risk summary
     */
    public function getRiskSummaryAttribute(): string
    {
        $level = strtoupper($this->risk_level);
        $score = number_format($this->composite_score, 1);
        
        return "{$level} ({$score}/10)";
    }

    /**
     * Check if assessment is recent (within 7 days)
     */
    public function isRecent(): bool
    {
        return $this->assessment_date->diffInDays(now()) <= 7;
    }

    /**
     * Check if assessment requires immediate action
     */
    public function requiresImmediateAction(): bool
    {
        return in_array($this->recommendation_level, ['respond', 'emergency']);
    }
}