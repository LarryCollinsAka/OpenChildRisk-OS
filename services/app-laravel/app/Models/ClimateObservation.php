<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * ClimateObservation Model
 * 
 * Time-series climate measurements from satellite sources.
 * Primary use: Rainfall anomaly detection for risk scoring.
 */
class ClimateObservation extends Model
{
    use HasUuids;

    protected $fillable = [
        'district_id',
        'data_source_id',
        'ingestion_job_id',
        'observation_date',
        'rainfall_mm',
        'temperature_min_c',
        'temperature_max_c',
        'temperature_avg_c',
        'humidity_pct',
        'rainfall_anomaly_pct',
        'rainfall_historical_avg',
        'rainfall_zscore',
        'quality',
        'confidence',
        'spatial_resolution_km',
        'metadata',
    ];

    protected $casts = [
        'observation_date' => 'date',
        'rainfall_mm' => 'decimal:2',
        'temperature_min_c' => 'decimal:2',
        'temperature_max_c' => 'decimal:2',
        'temperature_avg_c' => 'decimal:2',
        'humidity_pct' => 'decimal:2',
        'rainfall_anomaly_pct' => 'decimal:2',
        'rainfall_historical_avg' => 'decimal:2',
        'rainfall_zscore' => 'decimal:3',
        'confidence' => 'decimal:2',
        'spatial_resolution_km' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Belongs to district
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Belongs to data source
     */
    public function dataSource()
    {
        return $this->belongsTo(DataSource::class);
    }

    /**
     * Belongs to ingestion job
     */
    public function ingestionJob()
    {
        return $this->belongsTo(DataIngestionJob::class);
    }

    /**
     * Scope: Recent observations
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('observation_date', '>=', now()->subDays($days));
    }

    /**
     * Scope: By date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('observation_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Rainfall anomalies (positive or negative)
     */
    public function scopeAnomalous($query, float $threshold = 20)
    {
        return $query->where(function ($q) use ($threshold) {
            $q->where('rainfall_anomaly_pct', '>', $threshold)
              ->orWhere('rainfall_anomaly_pct', '<', -$threshold);
        });
    }

    /**
     * Scope: Drought conditions (low rainfall)
     */
    public function scopeDrought($query, float $threshold = -30)
    {
        return $query->where('rainfall_anomaly_pct', '<', $threshold);
    }

    /**
     * Scope: Heavy rainfall
     */
    public function scopeHeavyRainfall($query, float $threshold = 30)
    {
        return $query->where('rainfall_anomaly_pct', '>', $threshold);
    }

    /**
     * Check if observation indicates drought
     */
    public function isDrought(): bool
    {
        return $this->rainfall_anomaly_pct < -30;
    }

    /**
     * Check if observation indicates heavy rainfall
     */
    public function isHeavyRainfall(): bool
    {
        return $this->rainfall_anomaly_pct > 30;
    }
}