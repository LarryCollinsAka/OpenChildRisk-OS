<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriorityTarget extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'district_id',
        'hazard_type_id',
        'population_group_id',
        'organization_id',
        'name',
        'description',
        'rationale',
        'priority_score',
        'priority_level',
        'valid_from',
        'valid_until',
        'status',
        'target_indicators',
        'target_beneficiaries',
        'assigned_program_id',
        'field_workers_assigned',
        'budget_allocated',
        'last_reviewed_at',
        'reviewed_by_user_id',
        'review_notes',
        'metadata',
    ];

    protected $casts = [
        'priority_score' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'target_indicators' => 'array',
        'target_beneficiaries' => 'integer',
        'field_workers_assigned' => 'integer',
        'budget_allocated' => 'decimal:2',
        'last_reviewed_at' => 'date',
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
     * Hazard type
     */
    public function hazardType()
    {
        return $this->belongsTo(HazardType::class);
    }

    /**
     * Population group
     */
    public function populationGroup()
    {
        return $this->belongsTo(PopulationGroup::class);
    }

    /**
     * Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Assigned program
     */
    public function assignedProgram()
    {
        return $this->belongsTo(Program::class, 'assigned_program_id');
    }

    /**
     * Reviewed by user
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /**
     * Check if priority is currently valid
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

        return $this->status === 'active';
    }

    /**
     * Check if priority is expired
     */
    public function isExpired(): bool
    {
        if (!$this->valid_until) {
            return false;
        }

        return now()->toDateString() > $this->valid_until;
    }

    /**
     * Check if priority is critical level
     */
    public function isCritical(): bool
    {
        return $this->priority_level === 'critical';
    }
}