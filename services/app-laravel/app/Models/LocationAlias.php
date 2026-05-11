<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationAlias extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'alias',
        'normalized_alias',
        'alias_language',
        'alias_type',
        'canonical_district_id',
        'canonical_city_id',
        'canonical_state_id',
        'canonical_country_id',
        'confidence_score',
        'verified',
        'usage_count',
        'last_used_at',
        'data_source_id',
        'created_by_user_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'verified' => 'boolean',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Canonical district
     */
    public function canonicalDistrict()
    {
        return $this->belongsTo(District::class, 'canonical_district_id');
    }

    /**
     * Canonical city
     */
    public function canonicalCity()
    {
        return $this->belongsTo(City::class, 'canonical_city_id');
    }

    /**
     * Canonical state
     */
    public function canonicalState()
    {
        return $this->belongsTo(State::class, 'canonical_state_id');
    }

    /**
     * Canonical country
     */
    public function canonicalCountry()
    {
        return $this->belongsTo(Country::class, 'canonical_country_id');
    }

    /**
     * Data source
     */
    public function dataSource()
    {
        return $this->belongsTo(DataSource::class);
    }

    /**
     * Created by user
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }
}