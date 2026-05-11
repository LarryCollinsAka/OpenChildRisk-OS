<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistrictIndicatorValue extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'district_id',
        'indicator_id',
        'measured_at',
        'value',
        'value_text',
        'data_source_id',
        'ingestion_job_id',
        'confidence_level',
        'quality_flag',
        'methodology',
        'raw_value',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'measured_at' => 'date',
        'value' => 'decimal:4',
        'confidence_level' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * District
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Indicator
     */
    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }

    /**
     * Data source
     */
    public function dataSource()
    {
        return $this->belongsTo(DataSource::class);
    }

    /**
     * Ingestion job
     */
    public function ingestionJob()
    {
        return $this->belongsTo(DataIngestionJob::class);
    }

    /**
     * Check if value is critical
     */
    public function isCritical(): bool
    {
        return $this->indicator->isCritical($this->value);
    }

    /**
     * Check if value is warning level
     */
    public function isWarning(): bool
    {
        return $this->indicator->isWarning($this->value);
    }

    /**
     * Get status
     */
    public function getStatusAttribute(): string
    {
        return $this->indicator->getStatus($this->value);
    }
}