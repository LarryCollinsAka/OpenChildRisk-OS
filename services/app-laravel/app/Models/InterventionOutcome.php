<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterventionOutcome extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'intervention_id',
        'outcome_code',
        'title',
        'description',
        'measured_date',
        'measurement_type',
        'days_since_intervention_start',
        'outcome_indicators',
        'effectiveness_score',
        'impact_level',
        'children_vaccinated',
        'cases_prevented',
        'lives_saved_estimate',
        'disease_incidence_reduction',
        'malnutrition_reduction',
        'risk_score_before',
        'risk_score_after',
        'risk_reduction',
        'cost_per_beneficiary',
        'cost_per_outcome',
        'beneficiaries_surveyed',
        'satisfaction_score',
        'feedback_summary',
        'challenges_encountered',
        'lessons_learned',
        'recommendations',
        'data_source_id',
        'data_quality_score',
        'verification_method',
        'verified',
        'assessed_by_user_id',
        'organization_id',
        'assessed_at',
        'published',
        'published_at',
        'report_url',
        'metadata',
    ];

    protected $casts = [
        'measured_date' => 'date',
        'days_since_intervention_start' => 'integer',
        'outcome_indicators' => 'array',
        'effectiveness_score' => 'decimal:2',
        'children_vaccinated' => 'integer',
        'cases_prevented' => 'integer',
        'lives_saved_estimate' => 'integer',
        'disease_incidence_reduction' => 'decimal:2',
        'malnutrition_reduction' => 'decimal:2',
        'risk_score_before' => 'decimal:2',
        'risk_score_after' => 'decimal:2',
        'risk_reduction' => 'decimal:2',
        'cost_per_beneficiary' => 'decimal:2',
        'cost_per_outcome' => 'decimal:2',
        'beneficiaries_surveyed' => 'integer',
        'satisfaction_score' => 'decimal:2',
        'feedback_summary' => 'array',
        'challenges_encountered' => 'array',
        'lessons_learned' => 'array',
        'recommendations' => 'array',
        'data_quality_score' => 'decimal:2',
        'verified' => 'boolean',
        'assessed_at' => 'datetime',
        'published' => 'boolean',
        'published_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Intervention
     */
    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }

    /**
     * Data source
     */
    public function dataSource()
    {
        return $this->belongsTo(DataSource::class);
    }

    /**
     * Assessed by user
     */
    public function assessedBy()
    {
        return $this->belongsTo(User::class, 'assessed_by_user_id');
    }

    /**
     * Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Check if outcome is verified
     */
    public function isVerified(): bool
    {
        return $this->verified;
    }

    /**
     * Check if outcome is published
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * Check if outcome shows significant impact
     */
    public function isSignificant(): bool
    {
        return in_array($this->impact_level, ['significant', 'transformative']);
    }
}