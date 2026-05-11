<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HazardType extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'category_id',
        'code',
        'name',
        'description',
        'parent_id',
        'category',
        'risk_engine',
        'default_severity',
        'typical_time_window_days',
        'active',
        'metadata',
    ];

    protected $casts = [
        'default_severity' => 'integer',
        'typical_time_window_days' => 'integer',
        'active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Hazard category
     */
    public function hazardCategory()
    {
        return $this->belongsTo(HazardCategory::class, 'category_id');
    }

    /**
     * Parent hazard type
     */
    public function parent()
    {
        return $this->belongsTo(HazardType::class, 'parent_id');
    }

    /**
     * Child hazard types
     */
    public function children()
    {
        return $this->hasMany(HazardType::class, 'parent_id');
    }

    /**
     * Priority targets for this hazard
     */
    public function priorityTargets()
    {
        return $this->hasMany(PriorityTarget::class);
    }
}