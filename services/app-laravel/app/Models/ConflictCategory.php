<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * ConflictCategory Model
 * 
 * Taxonomy of conflict types based on ACLED classification.
 * Mirrors HazardType architecture for consistency.
 */
class ConflictCategory extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'name',
        'description',
        'color',
        'icon',
        'base_severity_weight',
        'acled_event_types',
        'is_active',
    ];

    protected $casts = [
        'base_severity_weight' => 'float',
        'acled_event_types' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Category has many conflict events
     */
    public function events()
    {
        return $this->hasMany(ConflictEvent::class, 'conflict_category_id');
    }

    /**
     * Get active categories only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}