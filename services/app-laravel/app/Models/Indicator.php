<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indicator extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'subcategory',
        'data_type',
        'unit',
        'min_value',
        'max_value',
        'polarity',
        'critical_threshold',
        'warning_threshold',
        'primary_population_group_id',
        'active',
        'display_order',
        'calculation_method',
        'data_collection_guidance',
        'metadata',
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'critical_threshold' => 'decimal:2',
        'warning_threshold' => 'decimal:2',
        'active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Primary population group
     */
    public function populationGroup()
    {
        return $this->belongsTo(PopulationGroup::class, 'primary_population_group_id');
    }

    /**
     * District indicator values
     */
    public function values()
    {
        return $this->hasMany(DistrictIndicatorValue::class);
    }

    /**
     * Check if higher values are better
     */
    public function isPositivePolarity(): bool
    {
        return $this->polarity === 'positive';
    }

    /**
     * Check if value is critical
     */
    public function isCritical($value): bool
    {
        if ($this->critical_threshold === null) {
            return false;
        }

        if ($this->isPositivePolarity()) {
            return $value < $this->critical_threshold;
        }

        return $value > $this->critical_threshold;
    }

    /**
     * Check if value is warning level
     */
    public function isWarning($value): bool
    {
        if ($this->warning_threshold === null) {
            return false;
        }

        if ($this->isPositivePolarity()) {
            return $value < $this->warning_threshold && $value >= $this->critical_threshold;
        }

        return $value > $this->warning_threshold && $value <= $this->critical_threshold;
    }

    /**
     * Get status for a given value
     */
    public function getStatus($value): string
    {
        if ($this->isCritical($value)) {
            return 'critical';
        }

        if ($this->isWarning($value)) {
            return 'warning';
        }

        return 'normal';
    }
}