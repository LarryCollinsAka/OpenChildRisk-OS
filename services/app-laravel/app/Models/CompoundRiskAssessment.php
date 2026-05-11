<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompoundRiskAssessment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'assessment_code',
        'title',
        'description',
        'district_id',
        'assessment_date',
        'valid_from',
        'valid_until',
        'overall_risk_score',
        'risk_level',
        'climate_risk_score',
        'disease_risk_score',
        'conflict_risk_score',
        'infrastructure_risk_score',
        'nutrition_risk_score',
        'vulnerable_population',
        'vulnerable_children_under5',
        'displaced_population',
        'malnourished_children',
        'hazard_events',
        'contributing_indicators',
        'vulnerability_factors',
        'cascade_pathways',
        'primary_drivers',
        'confidence_level',
        'methodology',
        'model_version',
        'recommended_interventions',
        'priority_actions',
        'estimated_response_cost',
        'assessed_by_user_id',
        'organization_id',
        'published',
        'published_at',
        'status',
        'superseded_by_id',
        'metadata',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'overall_risk_score' => 'decimal:2',
        'climate_risk_score' => 'decimal:2',
        'disease_risk_score' => 'decimal:2',
        'conflict_risk_score' => 'decimal:2',
        'infrastructure_risk_score' => 'decimal:2',
        'nutrition_risk_score' => 'decimal:2',
        'vulnerable_population' => 'integer',
        'vulnerable_children_under5' => 'integer',
        'displaced_population' => 'integer',
        'malnourished_children' => 'integer',
        'hazard_events' => 'array',
        'contributing_indicators' => 'array',
        'vulnerability_factors' => 'array',
        'cascade_pathways' => 'array',
        'confidence_level' => 'decimal:2',
        'recommended_interventions' => 'array',
        'estimated_response_cost' => 'integer',
        'published' => 'boolean',
        'published_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * District
     */
    public function district()
    {
        return $this->belongsTo(District::class);
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
     * Superseded by assessment
     */
    public function supersededBy()
    {
        return $this->belongsTo(CompoundRiskAssessment::class, 'superseded_by_id');
    }

    /**
     * Assessments superseded by this one
     */
    public function supersedes()
    {
        return $this->hasMany(CompoundRiskAssessment::class, 'superseded_by_id');
    }

    /**
     * Interventions based on this assessment
     */
    public function interventions()
    {
        return $this->hasMany(Intervention::class, 'risk_assessment_id');
    }

    /**
     * Check if assessment is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if assessment is critical
     */
    public function isCritical(): bool
    {
        return $this->risk_level === 'critical' || $this->overall_risk_score >= 8;
    }

    /**
     * Check if assessment is published
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * Check if assessment is currently valid
     */
    public function isValid(): bool
    {
        $now = now()->toDateString();
        
        if ($now < $this->valid_from) {
            return false;
        }

        if ($this->valid_until && $now > $this->valid_until) {
            return false;
        }

        return true;
    }
}