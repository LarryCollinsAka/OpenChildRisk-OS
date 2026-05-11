<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'organization_id',
        'program_type',
        'start_date',
        'end_date',
        'budget',
        'target_beneficiaries',
        'status',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'target_beneficiaries' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Organization running this program
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Priority targets assigned to this program
     */
    public function priorityTargets()
    {
        return $this->hasMany(PriorityTarget::class, 'assigned_program_id');
    }

    /**
     * Check if program is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if program is within date range
     */
    public function isCurrentlyRunning(): bool
    {
        $now = now()->toDateString();
        
        if ($now < $this->start_date) {
            return false;
        }

        if ($this->end_date && $now > $this->end_date) {
            return false;
        }

        return true;
    }
}