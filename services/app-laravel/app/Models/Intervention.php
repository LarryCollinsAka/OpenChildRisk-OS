<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Intervention extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'intervention_code',
        'title',
        'description',
        'intervention_type',
        'intervention_category',
        'hazard_event_id',
        'risk_assessment_id',
        'priority_target_id',
        'triggered_by_alerts',
        'district_id',
        'target_areas',
        'latitude',
        'longitude',
        'organization_id',
        'program_id',
        'facility_id',
        'population_group_id',
        'target_beneficiaries',
        'target_children_under5',
        'target_women',
        'eligibility_criteria',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'planned_duration_days',
        'budget_allocated',
        'budget_spent',
        'resources_deployed',
        'field_workers_assigned',
        'implementation_approach',
        'activities',
        'implementing_partners',
        'status',
        'completion_percentage',
        'status_notes',
        'actual_beneficiaries',
        'actual_children_reached',
        'actual_women_reached',
        'requested_by_user_id',
        'approved_by_user_id',
        'approved_at',
        'lead_field_worker_id',
        'team_members',
        'metadata',
    ];

    protected $casts = [
        'triggered_by_alerts' => 'array',
        'target_areas' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'target_beneficiaries' => 'integer',
        'target_children_under5' => 'integer',
        'target_women' => 'integer',
        'eligibility_criteria' => 'array',
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'planned_duration_days' => 'integer',
        'budget_allocated' => 'decimal:2',
        'budget_spent' => 'decimal:2',
        'resources_deployed' => 'array',
        'field_workers_assigned' => 'integer',
        'activities' => 'array',
        'implementing_partners' => 'array',
        'completion_percentage' => 'integer',
        'actual_beneficiaries' => 'integer',
        'actual_children_reached' => 'integer',
        'actual_women_reached' => 'integer',
        'approved_at' => 'datetime',
        'team_members' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Hazard event
     */
    public function hazardEvent()
    {
        return $this->belongsTo(HazardEvent::class);
    }

    /**
     * Risk assessment
     */
    public function riskAssessment()
    {
        return $this->belongsTo(CompoundRiskAssessment::class);
    }

    /**
     * Priority target
     */
    public function priorityTarget()
    {
        return $this->belongsTo(PriorityTarget::class);
    }

    /**
     * District
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Program
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Facility
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Population group
     */
    public function populationGroup()
    {
        return $this->belongsTo(PopulationGroup::class);
    }

    /**
     * Requested by user
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /**
     * Approved by user
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /**
     * Lead field worker
     */
    public function leadFieldWorker()
    {
        return $this->belongsTo(FieldWorker::class, 'lead_field_worker_id');
    }

    /**
     * Intervention outcomes
     */
    public function outcomes()
    {
        return $this->hasMany(InterventionOutcome::class);
    }

    /**
     * Check if intervention is active
     */
    public function isActive(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if intervention is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if intervention is approved
     */
    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    /**
     * Get budget utilization percentage
     */
    public function getBudgetUtilizationAttribute(): ?float
    {
        if (!$this->budget_allocated || $this->budget_allocated == 0) {
            return null;
        }

        return ($this->budget_spent / $this->budget_allocated) * 100;
    }

    /**
     * Get reach percentage
     */
    public function getReachPercentageAttribute(): ?float
    {
        if (!$this->target_beneficiaries || $this->target_beneficiaries == 0) {
            return null;
        }

        return ($this->actual_beneficiaries / $this->target_beneficiaries) * 100;
    }
}