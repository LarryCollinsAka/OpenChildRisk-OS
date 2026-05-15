<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * ConflictEvent Model - Provider-Agnostic
 * 
 * Normalized conflict events from multiple providers.
 * Philosophy: Single normalized table, not per-provider tables.
 */
class ConflictEvent extends Model
{
    use HasUuids;

    protected $fillable = [
        'conflict_category_id',
        'district_id',
        'country_id',
        'data_source_id',
        'source_provider',
        'source_confidence',
        'canonical_event_hash',
        'cross_source_matches',
        'temporal_window_hours',
        'spatial_radius_km',
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
        'provider_raw_data',
    ];

    protected $casts = [
        'event_date' => 'date',
        'actors' => 'array',
        'fatalities' => 'integer',
        'estimated_displaced' => 'integer',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'severity_score' => 'decimal:2',
        'source_confidence' => 'decimal:2',
        'temporal_window_hours' => 'integer',
        'spatial_radius_km' => 'decimal:2',
        'metadata' => 'array',
        'provider_raw_data' => 'array',
        'cross_source_matches' => 'array',
    ];

    /**
     * Belongs to conflict category
     */
    public function conflictCategory()
    {
        return $this->belongsTo(ConflictCategory::class);
    }

    /**
     * Belongs to district
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Belongs to country
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Belongs to data source
     */
    public function dataSource()
    {
        return $this->belongsTo(DataSource::class);
    }

    /**
     * Belongs to provider source
     */
    public function providerSource()
    {
        return $this->belongsTo(ConflictProviderSource::class, 'source_provider', 'code');
    }

    /**
     * Scope: By provider
     */
    public function scopeFromProvider($query, string $provider)
    {
        return $query->where('source_provider', $provider);
    }

    /**
     * Scope: By signal type (from metadata)
     */
    public function scopeSignalType($query, string $type)
    {
        return $query->whereJsonContains('metadata->signal_type', $type);
    }

    /**
     * Scope: High confidence events
     */
    public function scopeHighConfidence($query, float $threshold = 0.75)
    {
        return $query->where('source_confidence', '>=', $threshold);
    }

    /**
     * Scope: Recent events
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('event_date', '>=', now()->subDays($days));
    }

    /**
     * Scope: By date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Active events only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: High severity
     */
    public function scopeHighSeverity($query, float $threshold = 7.0)
    {
        return $query->where('severity_score', '>=', $threshold);
    }

    /**
     * Get signal type from metadata
     */
    public function getSignalType(): ?string
    {
        return $this->metadata['signal_type'] ?? null;
    }

    /**
     * Check if event is operational (high confidence)
     */
    public function isOperational(): bool
    {
        return $this->getSignalType() === 'operational';
    }

    /**
     * Check if event is predictive
     */
    public function isPredictive(): bool
    {
        return $this->getSignalType() === 'predictive';
    }

    /**
     * Check if event is weak signal
     */
    public function isWeakSignal(): bool
    {
        return $this->getSignalType() === 'weak_signal';
    }

    /**
     * Get confidence components from provider
     */
    public function getConfidenceComponents(): array
    {
        if ($this->providerSource) {
            return $this->providerSource->getConfidenceComponents();
        }

        return [
            'source_reliability' => $this->source_confidence,
            'spatial_confidence' => 0.70,
            'temporal_confidence' => 0.80,
            'classification_confidence' => 0.70,
        ];
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

    /**
     * Generate canonical event hash for deduplication
     */
    public static function generateCanonicalHash(
        string $date,
        float $lat,
        float $lng,
        string $category,
        int $fatalities
    ): string {
        $normalized = implode('|', [
            $date,
            round($lat, 2),
            round($lng, 2),
            $category,
            round($fatalities, -1), // Round to nearest 10
        ]);

        return hash('sha256', $normalized);
    }

    /**
     * Find potential duplicate events
     */
    public function findPotentialDuplicates()
    {
        return self::where('id', '!=', $this->id)
            ->where('conflict_category_id', $this->conflict_category_id)
            ->whereBetween('event_date', [
                $this->event_date->copy()->subHours($this->temporal_window_hours),
                $this->event_date->copy()->addHours($this->temporal_window_hours),
            ])
            ->whereRaw('
                (
                    6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(latitude))
                    )
                ) <= ?
            ', [$this->latitude, $this->longitude, $this->latitude, $this->spatial_radius_km])
            ->get();
    }
}