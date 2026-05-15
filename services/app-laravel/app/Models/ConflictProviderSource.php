<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * ConflictProviderSource Model
 * 
 * Metadata about conflict data providers.
 * Tracks provider characteristics, reliability, and ingestion status.
 */
class ConflictProviderSource extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'name',
        'description',
        'provider_type',
        'reliability_score',
        'update_frequency',
        'license_type',
        'historical_depth_years',
        'api_enabled',
        'api_base_url',
        'api_auth_type',
        'requires_institutional_access',
        'geographic_coverage',
        'event_types_covered',
        'last_successful_ingestion',
        'last_attempted_ingestion',
        'total_events_ingested',
        'events_last_30_days',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'reliability_score' => 'decimal:2',
        'historical_depth_years' => 'integer',
        'api_enabled' => 'boolean',
        'requires_institutional_access' => 'boolean',
        'geographic_coverage' => 'array',
        'event_types_covered' => 'array',
        'last_successful_ingestion' => 'datetime',
        'last_attempted_ingestion' => 'datetime',
        'total_events_ingested' => 'integer',
        'events_last_30_days' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Conflict events from this provider
     */
    public function conflictEvents()
    {
        return $this->hasMany(ConflictEvent::class, 'source_provider', 'code');
    }

    /**
     * Scope: Active providers only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: API-enabled providers
     */
    public function scopeApiEnabled($query)
    {
        return $query->where('api_enabled', true);
    }

    /**
     * Scope: By provider type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('provider_type', $type);
    }

    /**
     * Check if provider requires institutional access
     */
    public function requiresInstitutionalAccess(): bool
    {
        return $this->requires_institutional_access;
    }

    /**
     * Get confidence score components
     */
    public function getConfidenceComponents(): array
    {
        return [
            'source_reliability' => $this->reliability_score,
            'spatial_confidence' => $this->getDefaultSpatialConfidence(),
            'temporal_confidence' => $this->getDefaultTemporalConfidence(),
            'classification_confidence' => $this->getDefaultClassificationConfidence(),
        ];
    }

    /**
     * Get default spatial confidence by provider
     */
    protected function getDefaultSpatialConfidence(): float
    {
        $defaults = [
            'ACLED' => 0.90,
            'ICEWS' => 0.70,
            'GDELT' => 0.50,
        ];

        return $defaults[$this->code] ?? 0.70;
    }

    /**
     * Get default temporal confidence by provider
     */
    protected function getDefaultTemporalConfidence(): float
    {
        $defaults = [
            'ACLED' => 0.95,
            'ICEWS' => 0.80,
            'GDELT' => 0.90,
        ];

        return $defaults[$this->code] ?? 0.80;
    }

    /**
     * Get default classification confidence by provider
     */
    protected function getDefaultClassificationConfidence(): float
    {
        $defaults = [
            'ACLED' => 0.90,
            'ICEWS' => 0.70,
            'GDELT' => 0.60,
        ];

        return $defaults[$this->code] ?? 0.70;
    }

    /**
     * Calculate composite confidence
     */
    public function getCompositeConfidence(): float
    {
        $components = $this->getConfidenceComponents();

        return round(
            $components['source_reliability'] * 0.40 +
            $components['spatial_confidence'] * 0.25 +
            $components['temporal_confidence'] * 0.20 +
            $components['classification_confidence'] * 0.15,
            2
        );
    }
}