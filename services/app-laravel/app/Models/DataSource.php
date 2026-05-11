<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataSource extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'organization_id',
        'source_type',
        'provider',
        'api_url',
        'api_auth_type',
        'api_credentials_encrypted',
        'data_format',
        'update_frequency',
        'coverage_countries',
        'coverage_regions',
        'active',
        'last_sync_at',
        'last_sync_status',
        'last_sync_error',
        'consecutive_failures',
        'reliability_score',
        'data_quality_score',
        'total_records_synced',
        'sync_config',
        'auto_sync',
        'documentation_url',
        'contact_info',
        'metadata',
    ];

    protected $casts = [
        'coverage_countries' => 'array',
        'coverage_regions' => 'array',
        'active' => 'boolean',
        'last_sync_at' => 'datetime',
        'consecutive_failures' => 'integer',
        'reliability_score' => 'decimal:2',
        'data_quality_score' => 'decimal:2',
        'total_records_synced' => 'integer',
        'sync_config' => 'array',
        'auto_sync' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Ingestion jobs
     */
    public function ingestionJobs()
    {
        return $this->hasMany(DataIngestionJob::class);
    }

    /**
     * Indicator values from this source
     */
    public function indicatorValues()
    {
        return $this->hasMany(DistrictIndicatorValue::class);
    }

    /**
     * Population stats from this source
     */
    public function populationStats()
    {
        return $this->hasMany(DistrictPopulationStat::class);
    }

    /**
     * Location aliases from this source
     */
    public function locationAliases()
    {
        return $this->hasMany(LocationAlias::class);
    }

    /**
     * Check if source is API-based
     */
    public function isApi(): bool
    {
        return $this->source_type === 'api';
    }

    /**
     * Check if source is reliable
     */
    public function isReliable(): bool
    {
        return $this->reliability_score >= 0.80;
    }
}