<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HazardEvent extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'event_code',
        'title',
        'description',
        'hazard_type_id',
        'hazard_category_id',
        'district_id',
        'state_id',
        'country_id',
        'latitude',
        'longitude',
        'affected_areas',
        'detected_at',
        'started_at',
        'ended_at',
        'peak_at',
        'duration_hours',
        'severity',
        'severity_score',
        'affected_population',
        'affected_children_under5',
        'displaced_population',
        'casualties',
        'event_data',
        'data_source_id',
        'ingestion_job_id',
        'verified',
        'verified_by_user_id',
        'verified_at',
        'status',
        'status_notes',
        'alert_generated',
        'alert_id',
        'metadata',
    ];

    protected $casts = [
        'affected_areas' => 'array',
        'detected_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'peak_at' => 'datetime',
        'duration_hours' => 'integer',
        'severity_score' => 'decimal:2',
        'affected_population' => 'integer',
        'affected_children_under5' => 'integer',
        'displaced_population' => 'integer',
        'casualties' => 'integer',
        'event_data' => 'array',
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'alert_generated' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Hazard type
     */
    public function hazardType()
    {
        return $this->belongsTo(HazardType::class);
    }

    /**
     * Hazard category
     */
    public function hazardCategory()
    {
        return $this->belongsTo(HazardCategory::class);
    }

    /**
     * District
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * State
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Country
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
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
     * Verified by user
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    /**
     * Alert
     */
    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }

    /**
     * Interventions responding to this event
     */
    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }

    /**
     * Check if event is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if event is verified
     */
    public function isVerified(): bool
    {
        return $this->verified;
    }

    /**
     * Check if event is critical
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical' || $this->severity_score >= 8;
    }
}