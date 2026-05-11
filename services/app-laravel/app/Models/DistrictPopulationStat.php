<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistrictPopulationStat extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'district_id',
        'population_group_id',
        'measured_date',
        'population_count',
        'percentage_of_total',
        'data_source_id',
        'ingestion_job_id',
        'estimate_type',
        'confidence_level',
        'methodology',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'measured_date' => 'date',
        'population_count' => 'integer',
        'percentage_of_total' => 'decimal:2',
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
     * Population group
     */
    public function populationGroup()
    {
        return $this->belongsTo(PopulationGroup::class);
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
}