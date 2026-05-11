<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PopulationGroup extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'group_type',
        'min_age_months',
        'max_age_months',
        'vulnerability_weight',
        'active',
        'display_order',
        'metadata',
    ];

    protected $casts = [
        'min_age_months' => 'integer',
        'max_age_months' => 'integer',
        'vulnerability_weight' => 'decimal:2',
        'active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * District population stats for this group
     */
    public function districtStats()
    {
        return $this->hasMany(DistrictPopulationStat::class);
    }

    /**
     * Indicators primarily related to this group
     */
    public function indicators()
    {
        return $this->hasMany(Indicator::class, 'primary_population_group_id');
    }

    /**
     * Priority targets for this group
     */
    public function priorityTargets()
    {
        return $this->hasMany(PriorityTarget::class);
    }

    /**
     * Check if this is an age-based group
     */
    public function isAgeBased(): bool
    {
        return $this->group_type === 'age';
    }

    /**
     * Get age range in human-readable format
     */
    public function getAgeRangeAttribute(): ?string
    {
        if (!$this->isAgeBased()) {
            return null;
        }

        $minYears = $this->min_age_months ? floor($this->min_age_months / 12) : 0;
        $maxYears = $this->max_age_months ? floor($this->max_age_months / 12) : null;

        if ($maxYears === null) {
            return "{$minYears}+ years";
        }

        return "{$minYears}-{$maxYears} years";
    }

    /**
     * Check if group is high vulnerability
     */
    public function isHighVulnerability(): bool
    {
        return $this->vulnerability_weight >= 1.50;
    }
}