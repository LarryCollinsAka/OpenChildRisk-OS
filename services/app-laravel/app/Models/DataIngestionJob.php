<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataIngestionJob extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'data_source_id',
        'job_type',
        'status',
        'started_at',
        'completed_at',
        'duration_seconds',
        'records_processed',
        'records_created',
        'records_updated',
        'records_skipped',
        'records_failed',
        'data_start_date',
        'data_end_date',
        'error_message',
        'error_details',
        'failed_records',
        'validation_warnings',
        'duplicate_count',
        'invalid_count',
        'job_config',
        'metadata',
        'triggered_by_user_id',
        'trigger_source',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_seconds' => 'integer',
        'records_processed' => 'integer',
        'records_created' => 'integer',
        'records_updated' => 'integer',
        'records_skipped' => 'integer',
        'records_failed' => 'integer',
        'data_start_date' => 'date',
        'data_end_date' => 'date',
        'error_details' => 'array',
        'failed_records' => 'array',
        'validation_warnings' => 'array',
        'duplicate_count' => 'integer',
        'invalid_count' => 'integer',
        'job_config' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Data source
     */
    public function dataSource()
    {
        return $this->belongsTo(DataSource::class);
    }

    /**
     * Triggered by user
     */
    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    /**
     * Indicator values created by this job
     */
    public function indicatorValues()
    {
        return $this->hasMany(DistrictIndicatorValue::class, 'ingestion_job_id');
    }

    /**
     * Population stats created by this job
     */
    public function populationStats()
    {
        return $this->hasMany(DistrictPopulationStat::class, 'ingestion_job_id');
    }

    /**
     * Check if job completed successfully
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if job failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get success rate
     */
    public function getSuccessRateAttribute(): ?float
    {
        if ($this->records_processed === 0) {
            return null;
        }

        return ($this->records_created + $this->records_updated) / $this->records_processed * 100;
    }
}