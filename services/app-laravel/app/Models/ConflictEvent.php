<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * ConflictEvent Model
 * 
 * Individual conflict incidents from ACLED or other sources.
 * Linked to districts for compound risk analysis.
 */
class ConflictEvent extends Model
{
    use HasUuids;

    protected $fillable = [
        'conflict_category_id',
        'district_id',
        'country_id',
        'data_source_id',
        'external_id',
        'event_date',
        'sub_event_type',
        'notes',
        'actors',
        'fatalities',
        'estimated_displaced',
        'latitude',
        'longitude',
        'location_name',
        'severity_score',
        'status',
        'metadata',
    ];

    protected $casts = [
        'event_date' => 'date',
        'fatalities' => 'integer',
        'estimated_displaced' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'severity_score' => 'float',
        'actors' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Belongs to conflict category
     */
    public function category()
    {
        return $this->belongsTo(ConflictCategory::class, 'conflict_category_id');
    }

    /**
     * Belongs to district
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    /**
     * Belongs to country
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Belongs to data source
     */
    public function dataSource()
    {
        return $this->belongsTo(DataSource::class, 'data_source_id');
    }

    /**
     * Scope: Recent events (last N days)
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('event_date', '>=', now()->subDays($days));
    }

    /**
     * Scope: High impact (fatalities or displacement)
     */
    public function scopeHighImpact($query)
    {
        return $query->where(function ($q) {
            $q->where('fatalities', '>', 0)
              ->orWhereNotNull('estimated_displaced');
        });
    }

    /**
     * Scope: By category code
     */
    public function scopeByCategory($query, string $code)
    {
        return $query->whereHas('category', function ($q) use ($code) {
            $q->where('code', $code);
        });
    }
}